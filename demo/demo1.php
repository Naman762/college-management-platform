<?php
/**
 * admin_fees_interface_upgraded.php
 * Upgraded Admin interface supporting:
 *  - fee_installment_plan table (preloaded via installment_plan.sql)
 *  - When assigning yearly fees, admin can enter custom due dates per installment
 *  - Auto-creation of installment rows for assigned student with those due dates
 *  - Other features from previous admin_fees_interface.php
 *
 * Instructions:
 * 1. Import installment_plan.sql into your imperial_college DB (phpMyAdmin or CLI)
 * 2. Place this file in admin/ and ensure fpdf.php is present
 * 3. Run ?action=init once (creates tables student_fees, fee_structure, fee_installments if missing)
 */

$DB_HOST = 'localhost';
$DB_NAME = 'imperial_college';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (Exception $e) {
    die('DB Connection failed: ' . $e->getMessage());
}

session_start();

function e($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES);
}

function flash($m) {
    $_SESSION['flash'] = $m;
}

function get_flash() {
    $m = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $m;
}

/* -------------------------------------------------------------------------- */
/*                             Initialize Database                            */
/* -------------------------------------------------------------------------- */

if (isset($_GET['action']) && $_GET['action'] === 'init') {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS fee_structure (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_code VARCHAR(50),
            year VARCHAR(20),
            registration_fee INT DEFAULT 0,
            caution_fee INT DEFAULT 0,
            cocurricular_fee INT DEFAULT 0,
            course_fee INT DEFAULT 0,
            total_fee INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS student_fees (
            id INT AUTO_INCREMENT PRIMARY KEY,
            roll_no VARCHAR(50),
            fee_structure_id INT,
            year_label VARCHAR(50),
            total_fee INT,
            discount INT,
            final_fee INT,
            paid_amount INT DEFAULT 0,
            remaining_amount INT,
            status ENUM('Pending','Partially Paid','Paid') DEFAULT 'Pending',
            next_due_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (fee_structure_id) REFERENCES fee_structure(id) ON DELETE SET NULL
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS fee_installments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_fee_id INT,
            installment_no INT,
            amount INT,
            due_date DATE DEFAULT NULL,
            paid_amount INT DEFAULT 0,
            paid_date DATE DEFAULT NULL,
            receipt_no VARCHAR(100),
            payment_mode VARCHAR(50),
            remarks TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_fee_id) REFERENCES student_fees(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");

    flash('Initialized/verified tables. Import installment_plan.sql to load default installment amounts.');
    header('Location: admin_fees_interface_upgraded.php');
    exit;
}

/* -------------------------------------------------------------------------- */
/*                       Fetch Installment Plan (AJAX)                        */
/* -------------------------------------------------------------------------- */

if (isset($_GET['action']) && $_GET['action'] === 'get_plan' && isset($_GET['course_code']) && isset($_GET['year_label'])) {
    $stmt = $pdo->prepare("
        SELECT * FROM fee_installment_plan
        WHERE course_code = ? AND year_label = ?
        ORDER BY installment_no
    ");
    $stmt->execute([$_GET['course_code'], $_GET['year_label']]);
    $rows = $stmt->fetchAll();

    header('Content-Type: application/json');
    echo json_encode($rows);
    exit;
}

/* -------------------------------------------------------------------------- */
/*                          Handle Form Submissions                           */
/* -------------------------------------------------------------------------- */

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        /* ----------------------------- Add Structure ----------------------------- */
        if ($_POST['action'] === 'add_structure') {
            $stmt = $pdo->prepare("
                INSERT INTO fee_structure (course_code, year, registration_fee, caution_fee, cocurricular_fee, course_fee, total_fee)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $total = (int)$_POST['total_fee'];
            if ($total === 0) {
                $total =
                    (int)$_POST['registration_fee'] +
                    (int)$_POST['caution_fee'] +
                    (int)$_POST['cocurricular_fee'] +
                    (int)$_POST['course_fee'];
            }

            $stmt->execute([
                $_POST['course_code'],
                $_POST['year'],
                (int)$_POST['registration_fee'],
                (int)$_POST['caution_fee'],
                (int)$_POST['cocurricular_fee'],
                (int)$_POST['course_fee'],
                $total
            ]);

            flash('Structure added successfully.');
            header('Location: admin_fees_interface_upgraded.php');
            exit;
        }

        /* ----------------------------- Assign Fee ----------------------------- */
        if ($_POST['action'] === 'assign_fee') {
            $roll = $_POST['roll_no'];
            $fsid = (int)$_POST['fee_structure_id'];
            $year_label = $_POST['year_label'];
            $discount = max(0, (int)$_POST['discount']);

            // Load structure
            $st = $pdo->prepare('SELECT * FROM fee_structure WHERE id = ?');
            $st->execute([$fsid]);
            $fs = $st->fetch();
            if (!$fs) throw new Exception('Fee structure not found.');

            $total = (int)$fs['total_fee'];
            if ($total === 0) {
                $total =
                    (int)$fs['registration_fee'] +
                    (int)$fs['caution_fee'] +
                    (int)$fs['cocurricular_fee'] +
                    (int)$fs['course_fee'];
            }

            $final = max(0, $total - $discount);

            // Insert into student_fees
            $ins = $pdo->prepare("
                INSERT INTO student_fees
                (roll_no, fee_structure_id, year_label, total_fee, discount, final_fee, paid_amount, remaining_amount, status, next_due_date)
                VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?, ?)
            ");

            $remaining = $final;
            $ins->execute([$roll, $fsid, $year_label, $total, $discount, $final, $remaining, 'Pending', null]);
            $student_fee_id = $pdo->lastInsertId();

            // Load installment plan
            $plan = $pdo->prepare("
                SELECT * FROM fee_installment_plan
                WHERE course_code = ? AND year_label = ?
                ORDER BY installment_no
            ");
            $plan->execute([$fs['course_code'], $year_label]);
            $planRows = $plan->fetchAll();
            $due_dates = $_POST['due_dates'] ?? [];

            // Create installments
            if (count($planRows) > 0) {
                foreach ($planRows as $idx => $p) {
                    $amount = (int)$p['amount'];
                    $due = $due_dates[$idx] ?? null;
                    $stmt = $pdo->prepare("
                        INSERT INTO fee_installments (student_fee_id, installment_no, amount, due_date, paid_amount, paid_date)
                        VALUES (?, ?, ?, ?, 0, NULL)
                    ");
                    $stmt->execute([$student_fee_id, $p['installment_no'], $amount, $due]);
                }
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO fee_installments (student_fee_id, installment_no, amount, due_date, paid_amount, paid_date)
                    VALUES (?, ?, ?, ?, 0, NULL)
                ");
                $stmt->execute([$student_fee_id, 1, $final, null]);
            }

            flash('Fees assigned and installment schedule created.');
            header('Location: admin_fees_interface_upgraded.php');
            exit;
        }

        /* ----------------------------- Record Payment ----------------------------- */
        if ($_POST['action'] === 'record_payment') {
            $inst_id = (int)$_POST['installment_id'];
            $amount = (int)$_POST['amount'];
            if ($amount <= 0) throw new Exception('Invalid amount.');

            $r = $pdo->prepare('SELECT * FROM fee_installments WHERE id = ?');
            $r->execute([$inst_id]);
            $inst = $r->fetch();
            if (!$inst) throw new Exception('Installment not found.');

            $receipt = 'RCPT-' . strtoupper(substr(uniqid(), -8));

            $u = $pdo->prepare("
                UPDATE fee_installments
                SET paid_amount = ?, paid_date = ?, receipt_no = ?, payment_mode = ?, remarks = ?
                WHERE id = ?
            ");
            $u->execute([
                $amount,
                $_POST['payment_date'] ?: date('Y-m-d'),
                $receipt,
                $_POST['payment_mode'],
                $_POST['remarks'] ?: null,
                $inst_id
            ]);

            // Update student_fees totals
            $sf = $pdo->prepare('SELECT * FROM student_fees WHERE id = ?');
            $sf->execute([$inst['student_fee_id']]);
            $sfr = $sf->fetch();

            $new_paid = $sfr['paid_amount'] + $amount;
            $new_remaining = max(0, $sfr['final_fee'] - $new_paid);
            $status = $new_remaining === 0 ? 'Paid' : ($new_paid > 0 ? 'Partially Paid' : 'Pending');

            $upd = $pdo->prepare('UPDATE student_fees SET paid_amount = ?, remaining_amount = ?, status = ? WHERE id = ?');
            $upd->execute([$new_paid, $new_remaining, $status, $inst['student_fee_id']]);

            flash('Payment recorded. Receipt: ' . $receipt);
            header('Location: admin_fees_interface_upgraded.php');
            exit;
        }
    }
} catch (Exception $e) {
    flash('Error: ' . $e->getMessage());
    header('Location: admin_fees_interface_upgraded.php');
    exit;
}

/* -------------------------------------------------------------------------- */
/*                              Fetch UI Data Sets                            */
/* -------------------------------------------------------------------------- */

$structures = $pdo->query('SELECT * FROM fee_structure ORDER BY id DESC')->fetchAll();
$students = $pdo->query('SELECT roll_no, first_name, middle_name, last_name, father_name, course_code, semester FROM student_info ORDER BY roll_no')->fetchAll();
$assigned = $pdo->query('SELECT sf.*, si.first_name, si.middle_name, si.last_name FROM student_fees sf LEFT JOIN student_info si ON sf.roll_no = si.roll_no ORDER BY sf.created_at DESC')->fetchAll();
$installmentsList = $pdo->query('SELECT fi.*, sf.roll_no, si.first_name, si.last_name FROM fee_installments fi JOIN student_fees sf ON fi.student_fee_id = sf.id JOIN student_info si ON sf.roll_no = si.roll_no ORDER BY fi.due_date ASC, fi.installment_no ASC LIMIT 200')->fetchAll();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Fees - Upgraded</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Fees Management (Upgraded)</h2>
        <div>
            <a href="?action=init" class="btn btn-danger btn-sm">Init</a>
            <a href="admin_fees_interface_upgraded.php" class="btn btn-secondary btn-sm">Refresh</a>
        </div>
    </div>

    <!-- Flash Message -->
    <?php if ($m = get_flash()): ?>
        <div class="alert alert-info"><?= e($m) ?></div>
    <?php endif; ?>

    <div class="row">

        <!-- Left Column -->
        <div class="col-md-6">

            <!-- Add Fee Structure -->
            <div class="card mb-3">
                <div class="card-body">
                    <h5>Add Fee Structure</h5>
                    <form method="post">
                        <input type="hidden" name="action" value="add_structure">

                        <div class="mb-2">
                            <input name="course_code" class="form-control" placeholder="Course Code e.g. BCA" required>
                        </div>
                        <div class="mb-2">
                            <input name="year" class="form-control" placeholder="Year label e.g. I YEAR" required>
                        </div>

                        <div class="row">
                            <div class="col">
                                <input name="registration_fee" class="form-control" type="number" value="0" placeholder="Registration">
                            </div>
                            <div class="col">
                                <input name="caution_fee" class="form-control" type="number" value="0" placeholder="Caution">
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col">
                                <input name="cocurricular_fee" class="form-control" type="number" value="0" placeholder="Co-curricular">
                            </div>
                            <div class="col">
                                <input name="course_fee" class="form-control" type="number" value="0" placeholder="Course Fee">
                            </div>
                        </div>

                        <div class="mt-2">
                            <input name="total_fee" class="form-control" type="number" value="0" placeholder="Total Fee (0 to auto-calc)">
                        </div>

                        <button class="btn btn-primary mt-2">Add</button>
                    </form>
                </div>
            </div>

            <!-- Assign Yearly Fee -->
            <div class="card mb-3">
                <div class="card-body">
                    <h5>Assign Yearly Fee (with custom due dates)</h5>
                    <form id="assignForm" method="post">
                        <input type="hidden" name="action" value="assign_fee">

                        <div class="mb-2">
                            <select id="assign_roll" name="roll_no" class="form-select" required>
                                <option value="">Select Student</option>
                                <?php foreach ($students as $s):
                                    $fn = trim($s['first_name'] . ' ' . ($s['middle_name'] ?? '') . ' ' . ($s['last_name'] ?? '')); ?>
                                    <option value="<?= e($s['roll_no']) ?>">
                                        <?= e($s['roll_no'] . ' - ' . $fn . ' (' . $s['course_code'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col">
                                <select id="assign_structure" name="fee_structure_id" class="form-select" required>
                                    <option value="">Select Structure</option>
                                    <?php foreach ($structures as $st): ?>
                                        <option value="<?= e($st['id']) ?>" data-course="<?= e($st['course_code']) ?>">
                                            <?= e($st['course_code'] . ' | ' . $st['year'] . ' | ' . ($st['total_fee'] ?: ($st['course_fee'] + $st['registration_fee'] + $st['cocurricular_fee'] + $st['caution_fee']))) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col">
                                <input id="year_label" name="year_label" class="form-control" placeholder="Year label (e.g. I YEAR)" required>
                            </div>
                        </div>

                        <div class="mt-2">
                            <input name="discount" class="form-control" type="number" value="0" placeholder="Discount (₹)">
                        </div>

                        <div id="installmentDates" class="mt-3"></div>

                        <small class="text-muted">
                            Choose the structure + enter Year label, then click
                            <strong>Load Plan</strong> to populate installment amounts and supply due dates.
                        </small>

                        <div class="mt-2 d-flex gap-2">
                            <button type="button" id="loadPlanBtn" class="btn btn-outline-primary btn-sm">Load Plan</button>
                            <button class="btn btn-success btn-sm">Assign Fee</button>
                        </div>

                    </form>
                </div>
            </div>

        </div> <!-- End Left Column -->

        <!-- Right Column -->
        <div class="col-md-6">

            <!-- Record Payment -->
            <div class="card mb-3">
                <div class="card-body">
                    <h5>Record Payment (by installment)</h5>
                    <form method="post">
                        <input type="hidden" name="action" value="record_payment">

                        <div class="mb-2">
                            <select name="installment_id" class="form-select" required>
                                <option value="">Select Installment (Unpaid)</option>
                                <?php foreach ($installmentsList as $ins):
                                    if ($ins['paid_amount'] > 0) continue; ?>
                                    <option value="<?= e($ins['id']) ?>">
                                        <?= e($ins['roll_no'] . ' | Inst#' . $ins['installment_no'] . ' | Amt: ' . $ins['amount'] . ' | Due: ' . $ins['due_date']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col">
                                <input name="amount" class="form-control" type="number" placeholder="Amount" required>
                            </div>
                            <div class="col">
                                <input name="payment_date" class="form-control" type="date" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col">
                                <select name="payment_mode" class="form-select">
                                    <option>Cash</option>
                                    <option>Card</option>
                                    <option>UPI</option>
                                </select>
                            </div>
                            <div class="col">
                                <input name="next_due_date" class="form-control" type="date" placeholder="Next Due (optional)">
                            </div>
                        </div>

                        <div class="mt-2">
                            <input name="remarks" class="form-control" placeholder="Remarks">
                        </div>

                        <button class="btn btn-primary mt-2">Record Payment</button>
                    </form>
                </div>
            </div>

            <!-- Upcoming / Recent Installments -->
            <div class="card mb-3">
                <div class="card-body">
                    <h5>Upcoming / Recent Installments</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Roll</th>
                                    <th>Inst#</th>
                                    <th>Amt</th>
                                    <th>Due</th>
                                    <th>Paid</th>
                                    <th>Receipt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($installmentsList as $i): ?>
                                    <tr>
                                        <td><?= e($i['roll_no']) ?></td>
                                        <td><?= e($i['installment_no']) ?></td>
                                        <td><?= e($i['amount']) ?></td>
                                        <td><?= e($i['due_date']) ?></td>
                                        <td><?= e($i['paid_amount']) ?></td>
                                        <td>
                                            <?php if ($i['receipt_no']): ?>
                                                <a class="btn btn-sm btn-outline-primary" href="generate_receipt.php?r=<?= urlencode($i['receipt_no']) ?>" target="_blank">Open</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div> <!-- End Right Column -->

    </div> <!-- End Row -->

    <!-- Assigned Student Fees -->
    <div class="card mt-3">
        <div class="card-body">
            <h5>Assigned Student Fees</h5>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Roll</th>
                            <th>Name</th>
                            <th>Year</th>
                            <th>Final Fee</th>
                            <th>Paid</th>
                            <th>Remaining</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assigned as $a):
                            $name = trim($a['first_name'] . ' ' . ($a['middle_name'] ?? '') . ' ' . ($a['last_name'] ?? '')); ?>
                            <tr>
                                <td><?= e($a['roll_no']) ?></td>
                                <td><?= e($name) ?></td>
                                <td><?= e($a['year_label'] ?? '') ?></td>
                                <td><?= e($a['final_fee']) ?></td>
                                <td><?= e($a['paid_amount']) ?></td>
                                <td><?= e($a['remaining_amount']) ?></td>
                                <td><?= e($a['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div> <!-- End Container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- JS: Load Plan & Insert Due Dates -->
<script>
document.getElementById('loadPlanBtn').addEventListener('click', function () {
    const structSel = document.getElementById('assign_structure');
    const yearInput = document.getElementById('year_label');

    if (!structSel.value) {
        alert('Select a structure first');
        return;
    }
    if (!yearInput.value) {
        alert('Enter Year label (e.g. I YEAR)');
        return;
    }

    const selectedOption = structSel.options[structSel.selectedIndex];
    const course = selectedOption.getAttribute('data-course');

    fetch('?action=get_plan&course_code=' + encodeURIComponent(course) + '&year_label=' + encodeURIComponent(yearInput.value))
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById('installmentDates');
            container.innerHTML = '';

            if (!data || data.length === 0) {
                container.innerHTML = '<div class="alert alert-warning">No plan found for this course/year. A single installment equal to final fee will be created.</div>';
                return;
            }

            data.forEach((p, idx) => {
                const row = document.createElement('div');
                row.className = 'row g-2 mb-2';
                row.innerHTML =
                    '<div class="col-6"><input class="form-control" readonly value="Inst ' + p.installment_no + ' - Amount: ' + p.amount + '"></div>' +
                    '<div class="col-6"><input name="due_dates[]" type="date" class="form-control" required></div>';
                container.appendChild(row);
            });
        })
        .catch(err => {
            alert('Failed to load plan');
            console.error(err);
        });
});
</script>

</body>
</html>
