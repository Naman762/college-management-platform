<?php
// admin_fees_payment.php
$DB_HOST='localhost'; $DB_NAME='imperial_college'; $DB_USER='root'; $DB_PASS='';
try{ $pdo=new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]); }catch(Exception $e){ die($e->getMessage()); }
function e($v){ return htmlspecialchars($v??'',ENT_QUOTES); }
session_start();

$students = $pdo->query('SELECT roll_no, first_name, middle_name, last_name, course_code FROM student_info ORDER BY roll_no')->fetchAll();
$studentfees = null; $installments = [];

if (isset($_GET['roll']) && $_GET['roll']) {
    $roll = $_GET['roll'];
    $studentfees = $pdo->prepare('SELECT * FROM student_fees WHERE roll_no = ? ORDER BY created_at DESC');
    $studentfees->execute([$roll]);
    $studentfees = $studentfees->fetch();
    if ($studentfees) {
        $insts = $pdo->prepare('SELECT * FROM fee_installments WHERE student_fee_id = ? ORDER BY installment_no');
        $insts->execute([$studentfees['id']]);
        $installments = $insts->fetchAll();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'pay') {
    $inst_id = (int)$_POST['installment_id'];
    $pay = (int)$_POST['amount'];
    $pdate = $_POST['paid_date'] ?: date('Y-m-d');
    $mode = $_POST['mode'] ?: 'Cash';
    $roll = $_POST['roll'];

    $instStmt = $pdo->prepare('SELECT * FROM fee_installments WHERE id = ?');
    $instStmt->execute([$inst_id]);
    $inst = $instStmt->fetch();
    if (!$inst) { die('Installment not found'); }

    $pdo->beginTransaction();
    $remainingInThis = $inst['amount'] - $inst['paid_amount'];
    $toApply = $pay;

    // Apply to current installment
    $applyNow = min($remainingInThis, $toApply);
    $newPaid = $inst['paid_amount'] + $applyNow;
    $receiptBase = 'RCPT-'.strtoupper(substr(uniqid(), -8));

    $pdo->prepare('UPDATE fee_installments SET paid_amount = ?, paid_date = ?, receipt_no = ?, payment_mode = ? WHERE id = ?')
        ->execute([$newPaid, $pdate, $receiptBase, $mode, $inst_id]);

    $toApply -= $applyNow;

    // If extra, apply to next unpaid installments
    if ($toApply > 0) {
        $nextStmt = $pdo->prepare('SELECT * FROM fee_installments WHERE student_fee_id = ? AND amount > paid_amount ORDER BY installment_no');
        $nextStmt->execute([$inst['student_fee_id']]);
        while ($row = $nextStmt->fetch(PDO::FETCH_ASSOC)) {
            if ($toApply <= 0) break;
            // skip the installment we've already updated (it might be returned by query)
            if ($row['id'] == $inst_id) continue;
            $rem = $row['amount'] - $row['paid_amount'];
            if ($rem <= 0) continue;
            $ap = min($rem, $toApply);
            $newp = $row['paid_amount'] + $ap;
            $pdo->prepare('UPDATE fee_installments SET paid_amount = ?, paid_date = ?, receipt_no = ?, payment_mode = ? WHERE id = ?')
                ->execute([$newp, $pdate, 'RCPT-'.strtoupper(substr(uniqid(), -8)), $mode, $row['id']]);
            $toApply -= $ap;
        }
    }

    // Recompute student totals
    $sfid = $inst['student_fee_id'];
    $sumPaid = $pdo->prepare('SELECT SUM(paid_amount) AS s FROM fee_installments WHERE student_fee_id = ?');
    $sumPaid->execute([$sfid]);
    $totalPaid = (int)$sumPaid->fetchColumn();
    // payable_fees exists in student_fees
    $sft = $pdo->prepare('SELECT payable_fees FROM student_fees WHERE id = ?');
    $sft->execute([$sfid]);
    $sfr = $sft->fetch();
    $due = max(0, ($sfr['payable_fees'] ?? 0) - $totalPaid);
    $pdo->prepare('UPDATE student_fees SET paid_amount = ?, due_amount = ? WHERE id = ?')->execute([$totalPaid, $due, $sfid]);

    $pdo->commit();
    header('Location: admin_fees_payment.php?roll='.urlencode($roll));
    exit;
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
            <div class="container-fluid">
                <div class="row">
                    <div class="col-8">
                        <h3>Record Payment</h3>

                        <form method="get" class="row g-2 mb-3">
                            <div class="row">
                                <div class="col">                            
                                   <label>Select student to record there fees</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                <select name="roll" class="form-select">
                                    <option value="">Select student</option>
                                    <?php foreach ($students as $s): $n = trim($s['first_name'].' '.($s['middle_name']?:'').' '.($s['last_name']?:'')); ?>
                                    <option value="<?=e($s['roll_no'])?>" <?=isset($_GET['roll']) && $_GET['roll']==$s['roll_no']?'selected':''?>><?=e($s['roll_no'].' - '.$n.' ('.$s['course_code'].')')?></option>
                                    <?php endforeach; ?>
                                </select>
                                </div>
                                <div class="col-md-2"><button class="btn btn-primary">Load</button></div>
                            </div>
                        </form>

                        <?php if ($studentfees): ?>
                        <div class="card mt-3"><div class="card-body">
                            <h5>Installments for <?=e($studentfees['roll_no'])?></h5>
                            <table class="table">
                            <thead><tr><th>#</th><th>Due Date</th><th>Amount</th><th>Paid</th><th>Paid Date</th><th>Action</th></tr></thead>
                            <tbody>
                            <?php foreach ($installments as $ins): ?>
                                <tr>
                                <td><?=e($ins['installment_no'])?></td>
                                <td><?=e($ins['due_date'])?></td>
                                <td><?=e($ins['amount'])?></td>
                                <td><?=e($ins['paid_amount'])?></td>
                                <td><?=e($ins['paid_date'])?></td>
                                <td>
                                    <?php if ($ins['paid_amount'] < $ins['amount']): ?>
                                    <button class="btn btn-sm btn-primary" onclick="openPay(<?=e($ins['id'])?>, '<?=e($studentfees['roll_no'])?>', <?=e($ins['amount'] - $ins['paid_amount'])?>)">Pay</button>
                                    <?php else: ?>
                                    Paid
                                    <?php endif; ?>
                                </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                            </table>
                        </div></div>

                        <!-- Payment Modal (simple) -->
                        <div id="payModal" style="display:none;position:fixed;inset:0;align-items:center;justify-content:center;background:rgba(0,0,0,0.5)">
                            <div style="background:#fff;padding:20px;border-radius:6px;max-width:420px;width:90%;">
                            <form method="post">
                                <input type="hidden" name="action" value="pay">
                                <input type="hidden" name="installment_id" id="inst_id">
                                <input type="hidden" name="roll" id="roll">
                                <div class="mb-2"><label>Amount</label><input name="amount" id="pay_amount" class="form-control" required></div>
                                <div class="mb-2"><label>Paid Date</label><input name="paid_date" type="date" value="<?=date('Y-m-d')?>" class="form-control"></div>
                                <div class="mb-2"><label>Mode</label><select name="mode" class="form-select"><option>Cash</option><option>Card</option><option>UPI</option></select></div>
                                <div class="d-flex gap-2"><button class="btn btn-primary">Submit</button><button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button></div>
                            </form>
                            </div>
                        </div>

                        <script>
                        function openPay(id, roll, minAmt) {
                            document.getElementById('inst_id').value = id;
                            document.getElementById('roll').value = roll;
                            document.getElementById('pay_amount').value = minAmt;
                            document.getElementById('payModal').style.display = 'flex';
                        }
                        function closeModal() { document.getElementById('payModal').style.display = 'none'; }
                        </script>

                        <?php endif; ?>
                    </div>
                    <div class="col-4">
                        <div class="card">
                            <img class="card-img-top" src="../Images/payment.jpeg" alt="Card image cap">
                            <div class="card-body">
                                <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
</body>
</html>