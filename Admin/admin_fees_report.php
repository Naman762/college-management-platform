<?php
// admin_fees_report.php
$DB_HOST='localhost'; $DB_NAME='imperial_college'; $DB_USER='root'; $DB_PASS='';
try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
    ]);
} catch(Exception $e) { die($e->getMessage()); }

function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES); }

session_start();

$courseFilter = $_GET['course'] ?? '';
$semFilter = $_GET['semester'] ?? '';

$q = 'SELECT sf.*, si.first_name, si.middle_name, si.last_name, si.father_name 
      FROM student_fees sf 
      JOIN student_info si ON sf.roll_no = si.roll_no';
$conds = []; $params = [];
if ($courseFilter) { $conds[] = 'sf.course_code = ?'; $params[] = $courseFilter; }
if ($semFilter) { $conds[] = 'sf.semester = ?'; $params[] = $semFilter; }
if ($conds) $q .= ' WHERE ' . implode(' AND ', $conds);
$q .= ' ORDER BY si.first_name, sf.roll_no';

$stmt = $pdo->prepare($q); 
$stmt->execute($params); 
$students = $stmt->fetchAll();

function getInst($pdo, $sfid) {
    $s = $pdo->prepare('SELECT * FROM fee_installments WHERE student_fee_id = ? ORDER BY installment_no');
    $s->execute([$sfid]);
    return $s->fetchAll();
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Fees Report</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<?php include('../common/common-header.php') ?>
<?php include('../common/admin-sidebar.php') ?>

<main role="main" class="col-xl-10 col-lg-9 col-md-8 ml-sm-auto px-md-4 w-100">
<div class="sub-main">
  <div class="text-center d-flex flex-wrap flex-md-nowrap pt-3 pb-2 mb-3 text-white admin-dashboard pl-3">
    <div class="d-flex"><h4 class="mr-5">Fees Management System</h4></div>
  </div>
</div>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 style="color:black;">Fees Report</h3>
    <div class="no-print">
      <button class="btn btn-outline-secondary" onclick="window.print()">Print</button>
    </div>
  </div>

  <form class="row g-2 mb-3">
    <div class="col-md-3">
        <label>Course Code</label>
        <input name="course" class="form-control" placeholder="Course code" value="<?=e($courseFilter)?>"></div>
    <div class="col-md-3">
        <label>Semester</label>
        <input name="semester" class="form-control" placeholder="Semester" value="<?=e($semFilter)?>">
    </div>
    <div class="col-md-3">
     <button class="btn btn-primary" style="margin-top:31px;">Filter</button>
    </div>
  </form>

  <?php foreach ($students as $s): 
    $fullname = trim($s['first_name'].' '.($s['middle_name']?:'').' '.($s['last_name']?:'')); 
    $insts = getInst($pdo, $s['id']); 
  ?>
    <div class="card mb-3">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6"><strong>Student:</strong> <?=e($fullname)?> (<?=e($s['roll_no'])?>)</div>
          <div class="col-md-6"><strong>Father:</strong> <?=e($s['father_name'])?></div>
        </div>

        <div class="row mt-2">
          <div class="col">Uniform: <?=e($s['uniform_fees'])?></div>
          <div class="col">Registration: <?=e($s['registration_fees'])?></div>
          <div class="col">Caution: <?=e($s['caution_money'])?></div>
          <div class="col">Activity: <?=e($s['activity_fees'])?></div>
        </div>

        <div class="row mt-2">
          <div class="col">Payable: <?=e($s['payable_fees'])?></div>
          <div class="col">Paid: <?=e($s['paid_amount'])?></div>
          <div class="col">Due: <?=e($s['due_amount'])?></div>
        </div>

        <div class="table-responsive mt-3">
          <table class="table table-sm align-middle">
            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>Receipt</th>
                <th>Paid Date</th>
                <th>Paid Amt</th>
                <th>Due Date</th>
                <th>Due Amt</th>
                <th class="no-print">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($insts as $it): ?>
                <tr data-id="<?=$it['id']?>">
                  <td><?=e($it['installment_no'])?></td>
                  <td><?=e($it['receipt_no'])?></td>
                  <td><?=e($it['paid_date'])?></td>
                  <td><?=e($it['paid_amount'])?></td>
                  <td><?=e($it['due_date'])?></td>
                  <td><?=e(max(0, $it['amount'] - $it['paid_amount']))?></td>
                  <td class="no-print">
                    <button class="btn btn-sm btn-outline-primary editBtn"
                      data-id="<?=$it['id']?>"
                      data-receipt="<?=e($it['receipt_no'])?>"
                      data-paid="<?=e($it['paid_amount'])?>"
                      data-date="<?=e($it['paid_date'])?>"
                      data-due="<?=e($it['due_date'])?>"
                      data-amt="<?=e($it['amount'])?>">Edit</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  <?php endforeach; ?>
</div>
</main>

<!-- Edit Installment Modal -->
<div class="modal fade" id="editInstallmentModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="editInstallmentForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Installment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="inst_id">
        <div class="mb-2">
          <label>Receipt No</label>
          <input type="text" class="form-control" name="receipt_no" id="inst_receipt">
        </div>
        <div class="mb-2">
          <label>Paid Amount</label>
          <input type="number" class="form-control" name="paid_amount" id="inst_paid">
        </div>
        <div class="mb-2">
          <label>Paid Date</label>
          <input type="date" class="form-control" name="paid_date" id="inst_date">
        </div>
        <div class="mb-2">
          <label>Due Date</label>
          <input type="date" class="form-control" name="due_date" id="inst_due">
        </div>
        <div class="mb-2">
          <label>Installment Amount</label>
          <input type="number" class="form-control" name="amount" id="inst_amt">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function(){
  let modal = new bootstrap.Modal(document.getElementById('editInstallmentModal'));

  $('.editBtn').on('click', function(){
    $('#inst_id').val($(this).data('id'));
    $('#inst_receipt').val($(this).data('receipt'));
    $('#inst_paid').val($(this).data('paid'));
    $('#inst_date').val($(this).data('date'));
    $('#inst_due').val($(this).data('due'));
    $('#inst_amt').val($(this).data('amt'));
    modal.show();
  });

  $('#editInstallmentForm').on('submit', function(e){
    e.preventDefault();
    $.post('update_installment.php', $(this).serialize(), function(res){
      alert(res.message);
      if(res.success){
            alert(res.message);
            modal.hide();
            // update table cell dynamically if needed, or:
            setTimeout(()=>location.reload(), 600);
        }
    }, 'json');
  });
});
</script>

<style>
@media print {
  .no-print { display: none !important; }
  body { background: white; }
}
</style>
</body>
</html>
