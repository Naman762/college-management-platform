<!---------------- Session starts form here ----------------------->
<?php  
	session_start();
	if (!$_SESSION["LoginAdmin"])
	{
		header('location:../login/login.php');
	}
		require_once "../connection/connection.php";
	?>
<!---------------- Session Ends form here ------------------------>
<?php
$course_code = $_POST['course_code'] ?? ''; 
$semester    = $_POST['semester'] ?? '';
$month       = $_POST['month'] ?? date('m');
$year        = $_POST['year'] ?? date('Y');
?>
<!doctype html>
<html lang="en">
	<head>
		<title>Admin - Student Attendance report</title>
	</head>
	<body>
		<?php include('../common/common-header.php') ?>
		<?php include('../common/admin-sidebar.php') ?>  

		<main role="main" class="col-xl-10 col-lg-9 col-md-8 ml-sm-auto px-md-4 mb-2 w-100">
			<div class="sub-main">
				<div class="bar-margin text-center d-flex flex-wrap flex-md-nowrap pt-3 pb-2 mb-3 text-white admin-dashboard pl-3">
					<h4>Student Attendance Report </h4>
				</div>
                
                <div class="row">
                    <div class="col-12">
                        <!-- Filter Form -->
                            <form method="POST" class="form-row mb-4">
                                <div class="col-md-3">
                                    <label>Class</label>
                                    <select class="browser-default custom-select" name="course_code" id="course_code">
											<option >Select Course</option>
											<?php
											$teacher_id=$_SESSION['teacher_id'];
											$query="select distinct(course_code) as course_code from courses";
											$run=mysqli_query($con,$query);
											while($row=mysqli_fetch_array($run)) {
												echo	"<option value=".$row['course_code'].">".$row['course_code']."</option>";
											}
											?>
										</select>
                                </div>

                                <div class="col-md-3">
                                    <label>Semester</label>
                                    <select class="browser-default custom-select" name="semester" id="semester">
											<option >Select Semester</option>
											<?php
											$teacher_id=$_SESSION['teacher_id'];
											$query="select distinct(semester) as semester from course_subjects";
											$run=mysqli_query($con,$query);
											while($row=mysqli_fetch_array($run)) {
											echo	"<option value=".$row['semester'].">".$row['semester']."</option>";
											}
											?>
										</select>
                                </div>

                                <div class="col-md-2">
                                    <label>Month</label>
                                    <select class="form-control" name="month">
                                        <?php for ($m=1; $m<=12; $m++): ?>
                                            <option value="<?= $m ?>" <?= $m==$month ? 'selected':'' ?>>
                                                <?= date('F', mktime(0,0,0,$m,1)) ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label>Year</label>
                                    <input type="number" class="form-control" name="year" value="<?= $year ?>">
                                </div>

                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary btn-block">Generate</button>
                                </div>
                            </form>

                    </div>
                </div>
<?php



// 1. Get students
$students = mysqli_query($con, "SELECT roll_no, first_name, last_name 
                                 FROM student_info 
                                 WHERE course_code='$course_code' 
                                 AND semester='$semester'");

$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
?>

<div class="table-responsive">
<table class="table table-bordered text-center">
    <thead class="thead-dark">
        <tr>
            <th>Students</th>
            <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                <th><?php echo $d; ?></th>
            <?php endfor; ?>
            <th>TP</th>
            <th>TA</th>
            <th>TL</th>
            <th>TH</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($stu = mysqli_fetch_assoc($students)): ?>
            <?php
            $roll_no = $stu['roll_no'];
            $present = $absent = $late = $holiday = 0;
            ?>
            <tr>
                <td class="text-left"><?php echo $stu['first_name']." ".$stu['last_name']; ?></td>
                <?php 
                $present = $absent = $late = 0;
                for ($d = 1; $d <= $daysInMonth; $d++): 
                    $date = sprintf("%04d-%02d-%02d", $year, $month, $d);
                    $res = mysqli_query($con, "SELECT attendance FROM student_attendance 
                                                WHERE roll_no='$roll_no' 
                                                AND course_code='$course_code' 
                                                AND semester='$semester'
                                                AND attendance_date='$date'");
                    $att = mysqli_fetch_assoc($res);
                    $status = $att['attendance'] ?? null;

                    if ($status === '1') { echo "<td class='text-success font-weight-bold'>P</td>"; $present++; }
                    elseif ($status === '0') { echo "<td class='text-danger font-weight-bold'> A </td>"; $absent++; }
                    elseif ($status === '2') { echo "<td class='text-warning font-weight-bold'> L </td>"; $late++; }
                    else { echo "<td>-</td>"; }
                endfor; 
                // calculate holidays
                    $holiday = ($absent + $late);
                ?>
                <td style="background-color:#343a40;color:#fff;"><?= $present; ?></td>
                <td style="background-color:#343a40;color:#fff;"><?= $absent; ?></td>
                <td style="background-color:#343a40;color:#fff;"><?= $late; ?></td>
                <td style="background-color:#343a40;color:#fff;"><?= $holiday; ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
</div>
            </div>
            </main>
            </body>
            </html>
