<?php
// generate_receipt.php
$DB_HOST='localhost'; $DB_NAME='imperial_college'; $DB_USER='root'; $DB_PASS='';
try{ $pdo=new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]); }catch(Exception $e){ die($e->getMessage()); }
if (!isset($_GET['r'])) die('Missing receipt id');
$r = $_GET['r'];

$stmt = $pdo->prepare("SELECT fi.*, sf.payable_fees, sf.paid_amount AS total_paid, sf.due_amount, sf.roll_no, si.first_name, si.middle_name, si.last_name, si.father_name, si.course_code, si.semester
    FROM fee_installments fi
    JOIN student_fees sf ON fi.student_fee_id = sf.id
    JOIN student_info si ON sf.roll_no = si.roll_no
    WHERE fi.receipt_no = ?");
$stmt->execute([$r]); $row = $stmt->fetch();
if (!$row) die('Receipt not found');

require_once __DIR__ . '/fpdf/fpdf.php';
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,8,'ADARSH COLLEGE OF PROFESSIONAL STUDIES - Fee Receipt',0,1,'C');
$pdf->Ln(4);

$name = trim($row['first_name'].' '.($row['middle_name']?:'').' '.($row['last_name']?:''));
$pdf->SetFont('Arial','',11);
$pdf->Cell(0,6,'Receipt No: '.$row['receipt_no'],0,1);
$pdf->Cell(0,6,'Student: '.$name,0,1);
$pdf->Cell(0,6,'Roll: '.$row['roll_no'].' | Course: '.$row['course_code'].' | Sem: '.$row['semester'],0,1);
$pdf->Ln(4);

$pdf->Cell(90,7,'Particulars',1);
$pdf->Cell(30,7,'Amount',1);
$pdf->Cell(30,7,'Paid',1);
$pdf->Cell(40,7,'Remaining',1);
$pdf->Ln();

$pdf->Cell(90,7,'Installment #'.$row['installment_no'].' (Due: '.($row['due_date']?:'N/A').')',1);
$pdf->Cell(30,7,number_format($row['amount'],2),1);
$pdf->Cell(30,7,number_format($row['paid_amount'],2),1);
$rem = $row['amount'] - $row['paid_amount'];
$pdf->Cell(40,7,number_format($rem,2),1);
$pdf->Ln(12);

$pdf->Cell(0,6,'Payment Mode: '.($row['payment_mode']?:'Cash'),0,1);
$pdf->Cell(0,6,'Remarks: '.substr($row['remarks']?:'',0,200),0,1);
$pdf->Ln(20);
$pdf->Cell(0,6,'Signature',0,1,'R');
$pdf->Output('I','Receipt_'.$row['receipt_no'].'.pdf');
exit;
