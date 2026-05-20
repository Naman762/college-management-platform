<?php
$DB_HOST='localhost'; $DB_NAME='imperial_college'; $DB_USER='root'; $DB_PASS='';
header('Content-Type: application/json');
try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
    ]);
} catch(Exception $e) { echo json_encode(['success'=>false,'message'=>$e->getMessage()]); exit; }

$id = $_POST['id'] ?? 0;
$receipt = $_POST['receipt_no'] ?? '';
$paid = floatval($_POST['paid_amount'] ?? 0);
$paid_date = $_POST['paid_date'] ?? null;
$due_date = $_POST['due_date'] ?? null;
$amount = floatval($_POST['amount'] ?? 0);

if(!$id) { echo json_encode(['success'=>false,'message'=>'Invalid installment ID']); exit; }

try {
    $pdo->beginTransaction();

    // Update selected installment
    $upd = $pdo->prepare("UPDATE fee_installments 
        SET receipt_no=?, paid_amount=?, paid_date=?, due_date=?, amount=? 
        WHERE id=?");
    $upd->execute([$receipt, $paid, $paid_date, $due_date, $amount, $id]);

    // Find the parent student_fee_id
    $stmt = $pdo->prepare("SELECT student_fee_id FROM fee_installments WHERE id=?");
    $stmt->execute([$id]);
    $student_fee_id = $stmt->fetchColumn();

    // Recalculate total paid and due
    $totals = $pdo->prepare("SELECT 
                                SUM(paid_amount) AS total_paid, 
                                SUM(amount) AS total_amount 
                             FROM fee_installments 
                             WHERE student_fee_id=?");
    $totals->execute([$student_fee_id]);
    $row = $totals->fetch();
    $totalPaid = $row['total_paid'] ?? 0;
    $totalAmount = $row['total_amount'] ?? 0;
    $due = $totalAmount - $totalPaid;

    // Update student_fees main table
    $up2 = $pdo->prepare("UPDATE student_fees 
                          SET paid_amount=?, due_amount=? 
                          WHERE id=?");
    $up2->execute([$totalPaid, $due, $student_fee_id]);

    $pdo->commit();

    echo json_encode([
        'success'=>true,
        'message'=>'Installment and total updated successfully!'
    ]);
} catch (Exception $ex) {
    $pdo->rollBack();
    echo json_encode(['success'=>false,'message'=>$ex->getMessage()]);
}
