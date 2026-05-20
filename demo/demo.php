<?php
/**
 * admin_fees_interface.php
 * Bootstrap + PHP single-file admin interface for Fees Management
 * - Integrates with existing `student_info` table (uses roll_no)
 * - Create fee_structure, assign fees to students, record installments
 * - Generate PDF receipt using FPDF (place fpdf.php beside this file)
 *
 * Deployment:
 * 1. Place this file in your admin folder (e.g., /admin/)
 * 2. Update DB credentials below
 * 3. Ensure FPDF library (fpdf.php) is present in same folder
 * 4. Run once with ?action=init to create tables
 * 5. Use the UI to add structures, assign fees, record payments and generate receipts
 *
 * Notes:
 * - This is a starter implementation. Add authentication, input validation and sanitization
 *   to integrate it safely into your project.
 */

// ---------- DB CONFIG ----------
$DB_HOST = 'localhost';
$DB_NAME = 'imperial_college';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO("mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    die('DB Connection failed: ' . $e->getMessage());
}

// ---------- Helpers ----------
function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES); }
function flash($msg) { $_SESSION['flash'] = $msg; }
function get_flash() { $m = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $m; }
function gen_receipt_no() { return 'RCPT-' . strtoupper(substr(uniqid(), -8)); }

session_start();

// ---------- Init: create required tables (run once) ----------
if (isset($_GET['action']) && $_GET['action'] === 'init') {
    $sqls = [
        "CREATE TABLE IF NOT EXISTS fee_structure (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_code VARCHAR(50),
            year VARCHAR(20),
            registration_fee INT DEFAULT 0,
            caution_fee INT DEFAULT 0,
            cocurricular_fee INT DEFAULT 0,
            course_fee INT DEFAULT 0,
            total_fee INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",

        "CREATE TABLE IF NOT EXISTS student_fees (
            id INT AUTO_INCREMENT PRIMARY KEY,
            roll_no VARCHAR(50),
            fee_structure_id INT,
            total_fee INT,
            discount INT,
            final_fee INT,
            paid_amount INT DEFAULT 0,
            remaining_amount INT,
            status ENUM('Pending','Partially Paid','Paid') DEFAULT 'Pending',
            next_due_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (fee_structure_id) REFERENCES fee_structure(id) ON DELETE SET NULL
        ) ENGINE=InnoDB",

        "CREATE TABLE IF NOT EXISTS fee_installments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_fee_id INT,
            installment_no INT,
            amount INT,
            payment_date DATE,
            receipt_no VARCHAR(100),
            payment_mode VARCHAR(50),
            remarks TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_fee_id) REFERENCES student_fees(id) ON DELETE CASCADE
        ) ENGINE=InnoDB"
    ];

    foreach ($sqls as $sql) $pdo->exec($sql);
    flash('Initialized DB tables (fee_structure, student_fees, fee_installments).');
    header('Location: admin_fees_interface.php');
    exit;
}

// ---------- Actions: add structure, assign fee, record payment, generate receipt ----------
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action']) && $_POST['action'] === 'add_structure') {
            $stmt = $pdo->prepare("INSERT INTO fee_structure (course_code, year, registration_fee, caution_fee, cocurricular_fee, course_fee, total_fee) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $total = (int)$_POST['total_fee'];
            if ($total === 0) {
                $total = (int)$_POST['registration_fee'] + (int)$_POST['caution_fee'] + (int)$_POST['cocurricular_fee'] + (int)$_POST['course_fee'];
            }
            $stmt->execute([$_POST['course_code'], $_POST['year'], (int)$_POST['registration_fee'], (int)$_POST['caution_fee'], (int)$_POST['cocurricular_fee'], (int)$_POST['course_fee'], $total]);
            flash('Fee structure added.');
            header('Location: admin_fees_interface.php'); exit;
        }

        if (isset($_POST['action']) && $_POST['action'] === 'assign_fee') {
            $roll = $_POST['roll_no'];
            $fsid = (int)$_POST['fee_structure_id'];
            $discount = max(0, (int)$_POST['discount']);
            $stmt = $pdo->prepare("SELECT * FROM fee_structure WHERE id = ?"); $stmt->execute([$fsid]); $fs = $stmt->fetch();
            if (!$fs) throw new Exception('Fee structure not found');
            $total = (int)$fs['total_fee']; if ($total === 0) $total = (int)$fs['registration_fee'] + (int)$fs['caution_fee'] + (int)$fs['cocurricular_fee'] + (int)$fs['course_fee'];
            $final = max(0, $total - $discount);
            $stmt = $pdo->prepare("INSERT INTO student_fees (roll_no, fee_structure_id, total_fee, discount, final_fee, paid_amount, remaining_amount, status, next_due_date) VALUES (?, ?, ?, ?, ?, 0, ?, 'Pending', ?)");
            $stmt->execute([$roll, $fsid, $total, $discount, $final, $final, $_POST['next_due_date'] ?: null]);
            flash('Fees assigned to student.'); header('Location: admin_fees_interface.php'); exit;
        }

        if (isset($_POST['action']) && $_POST['action'] === 'record_payment') {
            $sfid = (int)$_POST['student_fee_id'];
            $amount = (int)$_POST['amount']; if ($amount <= 0) throw new Exception('Invalid amount');
            $pdate = $_POST['payment_date'] ?: date('Y-m-d');
            // load student_fees
            $stmt = $pdo->prepare("SELECT * FROM student_fees WHERE id = ?"); $stmt->execute([$sfid]); $sf = $stmt->fetch();
            if (!$sf) throw new Exception('Student fee record not found');
            $cnt = $pdo->prepare("SELECT COUNT(*) FROM fee_installments WHERE student_fee_id = ?"); $cnt->execute([$sfid]); $install_no = (int)$cnt->fetchColumn() + 1;
            $receipt = gen_receipt_no();
            $stmt = $pdo->prepare("INSERT INTO fee_installments (student_fee_id, installment_no, amount, payment_date, receipt_no, payment_mode, remarks) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$sfid, $install_no, $amount, $pdate, $receipt, $_POST['payment_mode'], $_POST['remarks']]);
            $new_paid = $sf['paid_amount'] + $amount; $new_remaining = max(0, $sf['final_fee'] - $new_paid); $status = $new_remaining === 0 ? 'Paid' : 'Partially Paid';
            $u = $pdo->prepare("UPDATE student_fees SET paid_amount = ?, remaining_amount = ?, status = ?, next_due_date = ? WHERE id = ?");
            $u->execute([$new_paid, $new_remaining, $status, $_POST['next_due_date'] ?: null, $sfid]);
            flash('Payment recorded. Receipt: ' . $receipt);
            header('Location: admin_fees_interface.php'); exit;
        }

    }
} catch (Exception $e) {
    flash('Error: ' . $e->getMessage()); header('Location: admin_fees_interface.php'); exit;
}

// ---------- Helper queries for UI ----------
$structures = $pdo->query('SELECT * FROM fee_structure ORDER BY id DESC')->fetchAll();
// students from student_info table
$students = $pdo->query('SELECT roll_no, first_name, middle_name, last_name, father_name, course_code, semester FROM student_info ORDER BY roll_no')->fetchAll();
// assigned fees
$assigned = $pdo->query('SELECT sf.*, si.first_name, si.middle_name, si.last_name, si.father_name FROM student_fees sf LEFT JOIN student_info si ON sf.roll_no = si.roll_no ORDER BY sf.created_at DESC')->fetchAll();
// recent payments
$payments = $pdo->query('SELECT fi.*, sf.roll_no, si.first_name, si.last_name FROM fee_installments fi JOIN student_fees sf ON fi.student_fee_id = sf.id JOIN student_info si ON sf.roll_no = si.roll_no ORDER BY fi.created_at DESC LIMIT 100')->fetchAll();

// ---------- PDF generation endpoint ----------
if (isset($_GET['action']) && $_GET['action'] === 'receipt' && isset($_GET['r'])) {
    require_once __DIR__ . '/fpdf.php';
    $r = $_GET['r'];
    $stmt = $pdo->prepare("SELECT fi.*, sf.final_fee, sf.paid_amount as total_paid, sf.remaining_amount, sf.roll_no, si.first_name, si.middle_name, si.last_name, si.father_name, si.course_code, si.semester
        FROM fee_installments fi
        JOIN student_fees sf ON fi.student_fee_id = sf.id
        JOIN student_info si ON sf.roll_no = si.roll_no
        WHERE fi.receipt_no = ?");
    $stmt->execute([$r]); $row = $stmt->fetch();
    if (!$row) { flash('Receipt not found'); header('Location: admin_fees_interface.php'); exit; }

    $student_name = trim($row['first_name'] . ' ' . ($row['middle_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 8, 'Adarsh College - Fee Receipt', 0, 1, 'C');
    $pdf->Ln(4);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(100, 6, 'Receipt No: ' . $row['receipt_no']);
    $pdf->Cell(0, 6, 'Date: ' . $row['payment_date'], 0, 1);
    $pdf->Cell(0, 6, 'Student: ' . $student_name, 0, 1);
    $pdf->Cell(0, 6, 'Father: ' . $row['father_name'], 0, 1);
    $pdf->Cell(0, 6, 'Roll No: ' . $row['roll_no'], 0, 1);
    $pdf->Cell(0, 6, 'Course/Sem: ' . $row['course_code'] . ' / ' . $row['semester'], 0, 1);
    $pdf->Ln(4);
    $pdf->Cell(90, 7, 'Particulars', 1);
    $pdf->Cell(30, 7, 'Amount', 1);
    $pdf->Cell(30, 7, 'Paid', 1);
    $pdf->Cell(40, 7, 'Remaining', 1);
    $pdf->Ln();
    $pdf->Cell(90, 7, 'Total Fees', 1);
    $pdf->Cell(30, 7, number_format($row['final_fee'], 2), 1);
    $pdf->Cell(30, 7, number_format($row['amount'], 2), 1);
    $pdf->Cell(40, 7, number_format($row['remaining_amount'], 2), 1);
    $pdf->Ln(12);
    $pdf->Cell(0, 6, 'Payment Mode: ' . $row['payment_mode'], 0, 1);
    $pdf->Cell(0, 6, 'Remarks: ' . substr($row['remarks'] ?? '', 0, 200), 0, 1);
    $pdf->Ln(20);
    $pdf->Cell(0, 6, 'Authority Signature', 0, 1, 'R');
    $pdf->Output('I', 'Receipt_' . $row['receipt_no'] . '.pdf');
    exit;
}

// ---------- Simple Bootstrap UI ----------
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Fees Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Fees Management - Admin</h2>
    <div>
      <a href="?action=init" class="btn btn-outline-danger btn-sm">Init DB (run once)</a>
      <a href="admin_fees_interface.php" class="btn btn-secondary btn-sm">Refresh</a>
    </div>
  </div>

  <?php if ($msg = get_flash()): ?>
    <div class="alert alert-info"><?php echo e($msg); ?></div>
  <?php endif; ?>

  <div class="row">
    <div class="col-md-6">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">Add Fee Structure</h5>
          <form method="post">
            <input type="hidden" name="action" value="add_structure">
            <div class="mb-2"><label class="form-label">Course Code</label><input name="course_code" class="form-control" required></div>
            <div class="mb-2"><label class="form-label">Year</label><input name="year" class="form-control" required></div>
            <div class="row">
              <div class="col"><label class="form-label">Registration</label><input name="registration_fee" class="form-control" type="number" value="0"></div>
              <div class="col"><label class="form-label">Caution</label><input name="caution_fee" class="form-control" type="number" value="0"></div>
            </div>
            <div class="row mt-2">
              <div class="col"><label class="form-label">Co-curricular</label><input name="cocurricular_fee" class="form-control" type="number" value="0"></div>
              <div class="col"><label class="form-label">Course Fee</label><input name="course_fee" class="form-control" type="number" value="0"></div>
            </div>
            <div class="mt-2"><label class="form-label">Total Fee (leave 0 to auto-calc)</label><input name="total_fee" class="form-control" type="number" value="0"></div>
            <button class="btn btn-primary mt-3">Add Structure</button>
          </form>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">Assign Fee To Student</h5>
          <form method="post">
            <input type="hidden" name="action" value="assign_fee">
            <div class="mb-2"><label class="form-label">Student (Roll No)</label>
              <select name="roll_no" class="form-select" required>
                <option value="">Select Student</option>
                <?php foreach ($students as $s): $fullname = trim($s['first_name'] . ' ' . ($s['middle_name'] ?? '') . ' ' . ($s['last_name'] ?? '')); ?>
                  <option value="<?php echo e($s['roll_no']); ?>"><?php echo e($s['roll_no'] . ' - ' . $fullname . ' (' . $s['course_code'] . ')'); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-2"><label class="form-label">Fee Structure</label>
              <select name="fee_structure_id" class="form-select" required>
                <option value="">Select Structure</option>
                <?php foreach ($structures as $st): ?>
                  <option value="<?php echo e($st['id']); ?>"><?php echo e($st['course_code'] . ' | ' . $st['year'] . ' | ' . ($st['total_fee']?:($st['course_fee']+$st['registration_fee']+$st['cocurricular_fee']+$st['caution_fee']))); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="row">
              <div class="col"><label class="form-label">Discount</label><input name="discount" class="form-control" type="number" value="0"></div>
              <div class="col"><label class="form-label">Next Due Date</label><input name="next_due_date" class="form-control" type="date"></div>
            </div>
            <button class="btn btn-success mt-3">Assign Fee</button>
          </form>
        </div>
      </div>

    </div>

    <div class="col-md-6">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">Record Payment</h5>
          <form method="post">
            <input type="hidden" name="action" value="record_payment">
            <div class="mb-2"><label class="form-label">Assigned Fee (Student)</label>
              <select name="student_fee_id" class="form-select" required>
                <option value="">Select Assigned Fee</option>
                <?php foreach ($assigned as $a): $sname = trim($a['first_name'] . ' ' . ($a['middle_name'] ?? '') . ' ' . ($a['last_name'] ?? '')); ?>
                  <option value="<?php echo e($a['id']); ?>"><?php echo e($a['roll_no'] . ' - ' . ($sname ?: 'Unknown') . ' | Final: ' . $a['final_fee'] . ' | Rem: ' . $a['remaining_amount']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="row">
              <div class="col"><label class="form-label">Amount</label><input name="amount" type="number" class="form-control" required></div>
              <div class="col"><label class="form-label">Payment Date</label><input name="payment_date" type="date" class="form-control"></div>
            </div>
            <div class="row mt-2">
              <div class="col"><label class="form-label">Payment Mode</label>
                <select name="payment_mode" class="form-select"><option>Cash</option><option>Card</option><option>UPI</option><option>Netbanking</option></select>
              </div>
              <div class="col"><label class="form-label">Next Due Date (optional)</label><input name="next_due_date" type="date" class="form-control"></div>
            </div>
            <div class="mt-2"><label class="form-label">Remarks</label><input name="remarks" class="form-control"></div>
            <button class="btn btn-primary mt-3">Record Payment</button>
          </form>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">Recent Payments</h5>
          <div class="table-responsive">
            <table class="table table-sm">
              <thead><tr><th>Receipt</th><th>Roll</th><th>Name</th><th>Amt</th><th>Date</th><th></th></tr></thead>
              <tbody>
                <?php foreach ($payments as $p): ?>
                  <tr>
                    <td><?php echo e($p['receipt_no']); ?></td>
                    <td><?php echo e($p['roll_no']); ?></td>
                    <td><?php echo e($p['first_name'] . ' ' . $p['last_name']); ?></td>
                    <td><?php echo e($p['amount']); ?></td>
                    <td><?php echo e($p['payment_date']); ?></td>
                    <td><a class="btn btn-sm btn-outline-primary" href="?action=receipt&r=<?php echo e($p['receipt_no']); ?>" target="_blank">Open</a></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>

  <div class="card mt-3">
    <div class="card-body">
      <h5>Assigned Fees</h5>
      <div class="table-responsive">
        <table class="table table-sm">
          <thead><tr><th>Roll</th><th>Name</th><th>Final Fee</th><th>Paid</th><th>Remaining</th><th>Status</th><th>Assigned At</th></tr></thead>
          <tbody>
            <?php foreach ($assigned as $a): $name = trim($a['first_name'] . ' ' . ($a['middle_name'] ?? '') . ' ' . ($a['last_name'] ?? '')); ?>
              <tr>
                <td><?php echo e($a['roll_no']); ?></td>
                <td><?php echo e($name); ?></td>
                <td><?php echo e($a['final_fee']); ?></td>
                <td><?php echo e($a['paid_amount']); ?></td>
                <td><?php echo e($a['remaining_amount']); ?></td>
                <td><?php echo e($a['status']); ?></td>
                <td><?php echo e($a['created_at']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
