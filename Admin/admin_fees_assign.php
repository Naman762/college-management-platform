<?php
// admin_fees_assign.php
// DB config - adjust as needed
$DB_HOST='localhost'; $DB_NAME='imperial_college'; $DB_USER='root'; $DB_PASS='';
try{ $pdo=new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]); }catch(Exception $e){ die('DB Err: '.$e->getMessage()); }

function e($v){ return htmlspecialchars($v??'',ENT_QUOTES); }
session_start();

$students = $pdo->query('SELECT roll_no, first_name, middle_name, last_name, father_name, course_code, semester FROM student_info ORDER BY roll_no')->fetchAll();

$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'assign') {
    $roll = $_POST['roll_no'];
    $course = $_POST['course_code'];
    $semester = $_POST['semester'];
    $year_label = $_POST['year_label'] ?: 'I YEAR';
    $uniform = (int)($_POST['uniform'] ?? 0);
    $registration = (int)($_POST['registration'] ?? 0);
    $caution = (int)($_POST['caution'] ?? 0);
    $activity = (int)($_POST['activity'] ?? 0);
    $tution = (int)($_POST['tution'] ?? 0);

    $payable = $uniform + $registration + $caution + $activity + $tution;

    // Prevent duplicate per student per semester
    $chk = $pdo->prepare('SELECT COUNT(*) FROM student_fees WHERE roll_no = ? AND semester = ?');
    $chk->execute([$roll, $semester]);
    if ($chk->fetchColumn() > 0) {
        $err = 'Fees already assigned for this student and semester.';
    } else {
        $pdo->beginTransaction();
        $ins = $pdo->prepare('INSERT INTO student_fees (roll_no, course_code, semester, academic_year, uniform_fees, registration_fees, caution_money, activity_fees, payable_fees, paid_amount, due_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)');
        $ins->execute([$roll, $course, $semester, $year_label, $uniform, $registration, $caution, $activity, $payable, $payable]);
        $sfid = $pdo->lastInsertId();

        // Insert installments from POST arrays
        $inst_amounts = $_POST['inst_amount'] ?? [];
        $inst_dates = $_POST['inst_due'] ?? [];

        // Allow admin to submit any number of installments
        foreach ($inst_amounts as $k => $amt) {
            $no = $k + 1;
            $a = (int)$amt;
            if ($a <= 0) continue; // skip zero installments
            $d = $inst_dates[$k] ?: null;
            $pdo->prepare('INSERT INTO fee_installments (student_fee_id, installment_no, due_date, amount) VALUES (?, ?, ?, ?)')
                ->execute([$sfid, $no, $d, $a]);
        }

        $pdo->commit();
        header('Location: admin_fees_assign.php?ok=1');
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Assign Fees</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
        <?php include('../common/common-header.php') ?>
		<?php include('../common/admin-sidebar.php') ?>
		<main role="main" class="col-xl-10 col-lg-9 col-md-8 ml-sm-auto px-md-4 w-100">
			<div class="sub-main">
				<div class="text-center d-flex flex-wrap flex-md-nowrap pt-3 pb-2 mb-3 text-white admin-dashboard pl-3">
					<div class="d-flex">
						<h4 class="mr-5">Fees Management System </h4>
					</div>
				</div>
            </div>
            <div class="container py-4">
            <h3 style="color:black;">Assign Fees to Student</h3>
            <?php if ($err): ?><div class="alert alert-danger"><?=e($err)?></div><?php endif; ?>
            <?php if (isset($_GET['ok'])): ?><div class="alert alert-success">Fees assigned successfully.</div><?php endif; ?>

            <form method="post" id="assignForm">
                <input type="hidden" name="action" value="assign">
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">Student</label>
                        <select id="selStudent" name="roll_no" class="form-select" required>
                        <option value="">Select</option>
                        <?php foreach ($students as $s): $n = trim($s['first_name'].' '.($s['middle_name']?:'').' '.($s['last_name']?:'')); ?>
                            <option value="<?=e($s['roll_no'])?>" data-course="<?=e($s['course_code'])?>" data-sem="<?=e($s['semester'])?>"><?=e($s['roll_no'].' - '.$n.' ('.$s['course_code'].')')?></option>
                        <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Course Code</label>
                        <input id="course_code" name="course_code" class="form-control" required>
                    </div>
                    
                </div>

                <div class="row g-2 mt-2">
                <div class="col-md-6">
                        <label class="form-label">Semester</label>
                        <input id="semester" name="semester" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Academic Year Label (e.g. I YEAR)</label>
                    <input name="year_label" class="form-control" placeholder="I YEAR" required>
                </div>
                </div>

                <hr>
                <div class="row g-2">
                    <div class="col-md-3"><label class="form-label">Tution Fees</label>
                        <input name="uniform" class="form-control" type="number" value="0" id="uniform">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Registration</label>
                        <input name="registration" class="form-control" type="number" value="0" id="registration">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Caution</label>
                        <input name="caution" class="form-control" type="number" value="0" id="caution">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Activity</label>
                        <input name="activity" class="form-control" type="number" value="0" id="activity">
                    </div>
                    </div>
                <div class="row g-2  mt-4">
                    <div class="col-md-5">
                        <label class="form-label">Calculate Total fees</label>
                        <input name="total_fees" class="form-control" type="number" readonly id="total">                
                    </div>
                    <div class="col-md-5">
                        <br>
                        <button type="button" id="calculate_all" class="btn btn-sm btn-primary" style="width:30%;font-size:20px;">calculate</button>
                    </div>
                </div>
                <hr>
                <div class="d-flex align-items-center gap-2">
                <label class="form-label mb-0">Installments</label>
                <button type="button" id="useDefault" class="btn btn-sm btn-outline-secondary">Use 4 Installments</button>
                <button type="button" id="addInstall" class="btn btn-sm btn-outline-primary">Add Installment Row</button>
                </div>

                <div id="installmentsArea" class="mt-2"></div>

                <div class="mt-3">
                <button class="btn btn-primary">Assign Fees</button>
                </div>
            </form>
            </div>
        </main>

<script>
const sel = document.getElementById('selStudent');
sel.addEventListener('change', ()=>{
  const opt = sel.selectedOptions[0];
  if(!opt) return;
  document.getElementById('course_code').value = opt.dataset.course || '';
  document.getElementById('semester').value = opt.dataset.sem || '';
});

//calculate fees
document.getElementById('calculate_all').addEventListener('click', ()=> {
        const registration = parseFloat(document.getElementById('registration').value) || 0;
        const caution = parseFloat(document.getElementById('caution').value) || 0;
        const activity = parseFloat(document.getElementById('activity').value) || 0;
        const uniform = parseFloat(document.getElementById('uniform').value) || 0;

        const total = registration + caution + activity + uniform;

        // Display total
        document.getElementById('total').value = total.toFixed(2);
});
// Installment builder
function buildInstallments(n, amounts = [], dates = []) {
  const cont = document.getElementById('installmentsArea');
  cont.innerHTML = '';
  for (let i = 0; i < n; i++) {
    const a = amounts[i] || '';
    const d = dates[i] || '';
    cont.insertAdjacentHTML('beforeend',
      `<div class="row g-2 mb-2">
        <div class="col-md-6">
          <input name="inst_amount[]" class="form-control" placeholder="Installment ${i+1} Amount" value="${a}" required>
        </div>
        <div class="col-md-6">
          <input name="inst_due[]" type="date" class="form-control" value="${d}" required>
        </div>
      </div>`);
  }
}


document.getElementById('useDefault').addEventListener('click', ()=> buildInstallments(4));
document.getElementById('addInstall').addEventListener('click', ()=>{
  const cont = document.getElementById('installmentsArea');
  const count = cont.querySelectorAll('input[name="inst_amount[]"]').length;
  buildInstallments(count + 1, Array.from(cont.querySelectorAll('input[name="inst_amount[]"]')).map(i=>i.value), Array.from(cont.querySelectorAll('input[name="inst_due[]"]')).map(i=>i.value));
});

// initial: show 4 rows by default
buildInstallments(4);
</script>
</body>
</html>
