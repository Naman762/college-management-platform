<?php
// admin_fees_payment.php (Upgraded)
$DB_HOST='localhost'; $DB_NAME='imperial_college'; $DB_USER='root'; $DB_PASS='';
try {
    $pdo=new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",$DB_USER,$DB_PASS,[
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
    ]);
}catch(Exception $e){ die($e->getMessage()); }

function e($v){ return htmlspecialchars($v??'',ENT_QUOTES); }
session_start();

// Fetch students
$students = $pdo->query("SELECT roll_no, first_name, middle_name, last_name, course_code FROM student_info ORDER BY roll_no")->fetchAll();

$studentfees = null; $installments = [];

if (isset($_GET['roll']) && $_GET['roll']) {
    $roll = $_GET['roll'];
    // Fetch the latest active fee record for this student (for current year)
    $studentfees = $pdo->prepare("SELECT * FROM student_fees WHERE roll_no=? ORDER BY id DESC LIMIT 1");
    $studentfees->execute([$roll]);
    $studentfees = $studentfees->fetch();

    if ($studentfees) {
        $insts = $pdo->prepare("SELECT * FROM fee_installments WHERE student_fee_id=? ORDER BY installment_no");
        $insts->execute([$studentfees['id']]);
        $installments = $insts->fetchAll();
    }
}

// Handle payment
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='pay') {
    $inst_id = (int)$_POST['installment_id'];
    $pay = (float)$_POST['amount'];
    $pdate = $_POST['paid_date'] ?: date('Y-m-d');
    $mode = $_POST['mode'] ?? 'Cash';
    $roll = $_POST['roll'];

    $instStmt = $pdo->prepare("SELECT * FROM fee_installments WHERE id=?");
    $instStmt->execute([$inst_id]);
    $inst = $instStmt->fetch();
    if(!$inst) die("Installment not found");

    $pdo->beginTransaction();
    $remainingInThis = $inst['amount'] - $inst['paid_amount'];
    $toApply = $pay;

    // Apply payment to current installment
    $applyNow = min($remainingInThis, $toApply);
    $newPaid = $inst['paid_amount'] + $applyNow;
    $receiptBase = 'RCPT-'.strtoupper(substr(uniqid(), -8));

    $pdo->prepare("UPDATE fee_installments 
                   SET paid_amount=?, paid_date=?, receipt_no=?, payment_mode=? 
                   WHERE id=?")
        ->execute([$newPaid, $pdate, $receiptBase, $mode, $inst_id]);

    $toApply -= $applyNow;

    // If extra amount, apply to next unpaid installments automatically
    if ($toApply > 0) {
        $nextStmt = $pdo->prepare("SELECT * FROM fee_installments 
                                   WHERE student_fee_id=? AND amount>paid_amount 
                                   ORDER BY installment_no");
        $nextStmt->execute([$inst['student_fee_id']]);
        while ($row = $nextStmt->fetch()) {
            if ($toApply <= 0) break;
            if ($row['id'] == $inst_id) continue;
            $rem = $row['amount'] - $row['paid_amount'];
            if ($rem <= 0) continue;
            $ap = min($rem, $toApply);
            $newp = $row['paid_amount'] + $ap;
            $pdo->prepare("UPDATE fee_installments 
                           SET paid_amount=?, paid_date=?, receipt_no=?, payment_mode=? 
                           WHERE id=?")
                ->execute([$newp, $pdate, 'RCPT-'.strtoupper(substr(uniqid(), -8)), $mode, $row['id']]);
            $toApply -= $ap;
        }
    }

    // Recalculate total paid/due
    $sfid = $inst['student_fee_id'];
    $totals = $pdo->prepare("SELECT SUM(paid_amount) AS paid, SUM(amount) AS total FROM fee_installments WHERE student_fee_id=?");
    $totals->execute([$sfid]);
    $row = $totals->fetch();
    $totalPaid = $row['paid'] ?? 0;
    $total = $row['total'] ?? 0;
    $due = $total - $totalPaid;
    $pdo->prepare("UPDATE student_fees SET paid_amount=?, due_amount=? WHERE id=?")
        ->execute([$totalPaid, $due, $sfid]);

    $pdo->commit();

    header("Location: admin_fees_payment.php?roll=".urlencode($roll));
    exit;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Fees Payment</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
#payModal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); align-items:center; justify-content:center; }
</style>
</head>
<body>
<?php include('../common/common-header.php'); ?>
<?php include('../common/admin-sidebar.php'); ?>
<main role="main" class="col-xl-10 col-lg-9 col-md-8 ml-sm-auto px-md-4 w-100">
<div class="sub-main">
    <div class="text-center d-flex flex-wrap flex-md-nowrap pt-3 pb-2 mb-3 text-white admin-dashboard pl-3">
        <div class="d-flex"><h4 class="mr-5">Fees Management System</h4></div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-8">
            <h3>Record Payment</h3>

            <form method="get" class="row g-2 mb-3">
                <div class="col-md-6">
                    <select name="roll" class="form-select">
                        <option value="">Select student</option>
                        <?php foreach($students as $s): 
                            $n = trim($s['first_name'].' '.($s['middle_name']?:'').' '.($s['last_name']?:'')); ?>
                            <option value="<?=e($s['roll_no'])?>" <?=isset($_GET['roll'])&&$_GET['roll']==$s['roll_no']?'selected':''?>><?=e($s['roll_no'].' - '.$n.' ('.$s['course_code'].')')?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-primary">Load</button></div>
            </form>

            <?php if($studentfees): ?>
                <div class="card mt-3"><div class="card-body">
                    <h5><?=e($studentfees['roll_no'])?> — Total Payable ₹<?=e($studentfees['payable_fees'])?></h5>
                    <p>Paid: ₹<?=e($studentfees['paid_amount'])?> | Due: ₹<?=e($studentfees['due_amount'])?></p>
                    <table class="table table-bordered table-sm align-middle">
                        <thead class="table-dark">
                            <tr><th>#</th><th>Due Date</th><th>Amount</th><th>Paid</th><th>Paid Date</th><th>Receipt</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($installments as $ins): ?>
                            <tr>
                                <td><?=e($ins['installment_no'])?></td>
                                <td><?=e($ins['due_date'])?></td>
                                <td><?=e($ins['amount'])?></td>
                                <td><?=e($ins['paid_amount'])?></td>
                                <td><?=e($ins['paid_date'])?></td>
                                <td>
                                    <?php if($ins['receipt_no']): ?>
                                        <a href="generate_receipt.php?r=<?=e($ins['receipt_no'])?>" target="_blank">View</a>
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                                <td>
                                    <?php if($ins['paid_amount'] < $ins['amount']): ?>
                                        <button class="btn btn-sm btn-primary" onclick="openPay(<?=e($ins['id'])?>, '<?=e($studentfees['roll_no'])?>', <?=e($ins['amount'] - $ins['paid_amount'])?>)">Pay</button>
                                    <?php else: ?><span class="badge bg-success" style="font-size:15px;">Paid</span><?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div></div>
            <?php endif; ?>
        </div>

        <div class="col-4">
            <div class="card"><img src="../Images/payment.jpeg" class="card-img-top"><div class="card-body">
                <p class="card-text">Use this section to record student installment payments and print receipts.</p>
            </div></div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div id="payModal">
<div class="bg-white p-4 rounded shadow" style="max-width:420px;width:90%">
<form method="post">
    <input type="hidden" name="action" value="pay">
    <input type="hidden" name="installment_id" id="inst_id">
    <input type="hidden" name="roll" id="roll">
    <div class="mb-2"><label>Amount</label><input name="amount" id="pay_amount" class="form-control" required></div>
    <div class="mb-2"><label>Paid Date</label><input name="paid_date" type="date" value="<?=date('Y-m-d')?>" class="form-control"></div>
    <div class="mb-2"><label>Mode</label><select name="mode" class="form-select"><option>Cash</option><option>Card</option><option>UPI</option></select></div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary">Submit</button>
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
    </div>
</form>
</div>
</div>

<script>
function openPay(id, roll, amt){
  document.getElementById('inst_id').value=id;
  document.getElementById('roll').value=roll;
  document.getElementById('pay_amount').value=amt;
  document.getElementById('payModal').style.display='flex';
}
function closeModal(){ document.getElementById('payModal').style.display='none'; }
</script>

</main>
</body>
</html>
