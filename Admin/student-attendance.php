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

if (isset($_POST['sub'])) {
    $date = $_POST['atten_date'];
	$course_code = $_POST['course_code'];
	echo $course_code;
	$semester = $_POST['semester'];
	echo $semester;

    foreach ($_POST['attendance'] as $roll_no => $status) {
       
        // Check if already exists
        $check = mysqli_query($con, "SELECT attendance_id FROM student_attendance 
                                     WHERE roll_no='$roll_no' 
                                     AND course_code='$course_code' 
                                     AND semester='$semester' 
                                     AND attendance_date='$date'");
        if (mysqli_num_rows($check) > 0) {
            // Update
            mysqli_query($con, "UPDATE student_attendance 
                                SET attendance='$status' 
                                WHERE roll_no='$roll_no' 
                                AND course_code='$course_code' 
                                AND semester='$semester' 
                                AND attendance_date='$date'");
        } else {
            // Insert
            mysqli_query($con, "INSERT INTO student_attendance 
                                (course_code, semester, roll_no, attendance, attendance_date) 
                                VALUES ('$course_code', '$semester', '$roll_no', '$status', '$date')");
        }
    }
    echo "Attendance saved successfully!";
}
?>

<!doctype html>
<html lang="en">
	<head>
		<title>Admin - Student Attendance</title>
		<style>
			.atten{
				align-items: center;
    justify-content: center;
    gap: 10px;
			}
			.table td{
				font-size: 15px;
    			font-weight: 600;
			}
			</style>
	</head>
	<body>
		<?php include('../common/common-header.php') ?>
		<?php include('../common/admin-sidebar.php') ?>  

		<main role="main" class="col-xl-10 col-lg-9 col-md-8 ml-sm-auto px-md-4 mb-2 w-100">
			<div class="sub-main">
				<div class="bar-margin text-center d-flex flex-wrap flex-md-nowrap pt-3 pb-2 mb-3 text-white admin-dashboard pl-3">
					<h4>Student Attendance Management System </h4>
				</div>
				<div class="row w-100">
					<div class="col-md-12">
						<form action="student-attendance.php" method="post">
							<div class="row mt-3">
								<div class="col-md-4">
									<div class="form-group" style="z-index: 10;">
										<label>Select Course</label>
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
								</div>
								<div class="col-md-4">
									<div class="form-group">
									<label for="exampleInputEmail1">Select Semester:</label>
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
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label>Today's Date</label>
										<input type="date" name="dob" id="attendance_date" class="form-control">
										<small id="errorMsg" class="text-danger"></small>
									</div>
								</div>
								<div class="col-md-6">
									<input type="submit" name="submit" class="btn btn-primary px-5" value="Press" id="submitBtn">
								</div>
							</div>	
						</form>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<section class="border mt-3">
							<table class="w-100 table-elements table-three-tr table" cellpadding="3" id="attendanceTable">
								<tr class="table-tr-head table-three text-white">
									<th>Sr.No</th>
									<th>Roll No</th>
									<th>Course Name</th>
									<th>Semester</th>
									<th>Student Name</th>
									<th>Father's Name</th>
									<th>Present/Absent</th>
									<th>Percentage</th>
									<th>Mark Present</th>
									<th>Mark Absent</th>
									<th>Mark Leave</th>
								</tr>
								<?php  
								$i=1;
								$count=0;
								$conn=mysqli_connect("localhost","root","","imperial_college");
								
								if (isset($_POST['submit'])) {
									$course_code=$_POST['course_code'];
									$semester=$_POST['semester'];
									$date = $_POST['dob'];
								
									// Fetch students
									$que="SELECT roll_no,first_name,middle_name,last_name,father_name,course_code,semester 
										  FROM student_info 
										  WHERE course_code='$course_code' AND semester='$semester'";
									$run=mysqli_query($conn,$que);
								
									while ($row=mysqli_fetch_array($run)) {
										$count++;
										$roll_no=$row['roll_no'];
								
										// Fetch previous attendance for this student on this date
										$attStatus = '';
										$attQuery="SELECT attendance FROM student_attendance 
												   WHERE roll_no='$roll_no' 
												   AND course_code='$course_code' 
												   AND semester='$semester' 
												   AND attendance_date='$date'";

										$attRes=mysqli_query($conn,$attQuery);
										if($attRow=mysqli_fetch_assoc($attRes)) {
											$attStatus=$attRow['attendance']; // 1=Present, 0=Absent, 2=Leave
										}
										?>								
										<form action="student-attendance.php" method="post">
										<tr>
											<td><?php echo $i++ ?></td>
											<?php $roll_no=$row['roll_no']; ?>
											
											<td><?php echo $row['roll_no'] ?></td>
											<input type="hidden" name="roll_no" value=<?php echo $row['roll_no'] ?> >
											
											<td><?php echo $row['course_code'] ?></td>
											<input type="hidden" name="course_code" value=<?php echo $row['course_code'] ?> >
											
											<td><?php echo $row['semester'] ?></td>
											<input type="hidden" name="semester" value=<?php echo $row['semester'] ?> >
											
											<td><?php echo $row['first_name']." ".$row['middle_name']." ".$row['last_name'] ?></td>
											
											<td><?php echo $row['father_name'] ?></td>

											<?php  
												// Show total attendance summary
												$query1="SELECT COUNT(attendance_id) as total, SUM(attendance=1) as presents 
														FROM student_attendance 
														WHERE roll_no='$roll_no' AND course_code='$course_code'";
												$run1=mysqli_query($conn,$query1); 
												while ($row1=mysqli_fetch_array($run1)) { ?>
													<td class="text-center">
														<?php echo $row1['presents']." | ".($row1['total']-$row1['presents']) ?>
													</td>
													<td>
														<?php echo $row1['total'] > 0 ? round(($row1['presents']*100)/$row1['total'])."%" : "0" ?>
													</td>
												<?php } ?>
											
											<!-- Attendance Options -->
											<td>
												<input type="checkbox" name="attendance[<?php echo $roll_no ?>]" value="1"
													<?php echo ($attStatus==='1') ? 'checked' : '' ?>> Present
											</td>
											<td>
												<input type="checkbox" name="attendance[<?php echo $roll_no ?>]" value="0"
													<?php echo ($attStatus==='0') ? 'checked' : '' ?>> Absent
											</td>
											<td>
												<input type="checkbox" name="attendance[<?php echo $roll_no ?>]" value="2"
													<?php echo ($attStatus==='2') ? 'checked' : '' ?>> Leave
											</td>
											<input type="hidden" name="count" value="<?php echo $count ?>">
											<input type="hidden" name="atten_date" value="<?php echo $date?>"
										</tr>
										
								<?php		
									}
									}
								?>

							</table>				
							<div class="text-center">
							<input type="submit" name="sub" value="Enter student Attendence" class="btn btn-primary px-5 text-center">
							</div>
							</form>

						</section>
					</div>
				</div>
			</div>
		</main>
		<script type="text/javascript" src="../bootstrap/js/jquery.min.js"></script>
		<script type="text/javascript" src="../bootstrap/js/bootstrap.min.js"></script>
		<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
		<script>
$(document).on('change', '#attendanceTable input[type=checkbox]', function(){
  var $tr = $(this).closest('tr');
  // uncheck other checkboxes in same row
  $tr.find('input[type=checkbox]').not(this).prop('checked', false);
});

$(document).ready(function () {
    function checkAttendance() {
        var date = $("input[name='dob']").val();
		console.log(date);
		var course_code = $("select[name='course_code']").val();
		console.log(course_code);
        var semester = $("select[name='semester']").val();
		console.log(semester);
        

        if (course_code && semester && date) {
            $.ajax({
                url: "check_attendance.php",
                type: "POST",
                data: {
                    course_code: course_code,
                    semester: semester,
                    attendance_date: date
                },
                success: function (response) {
					console.log("Server response:", response);
                    if (response.trim() === "exists") {
                        $("#errorMsg").text("Attendance already marked for this date!")
                                      .css("color", "red");
                       
                    } else {
                        $("#errorMsg").text("");
                       
                    }
                }
            });
        } else {
            $("#errorMsg").text("");
            
        }
    }

    // Run check when any of the three inputs change
    $("select[name='course_code'], select[name='semester'], input[name='dob']").on("change", checkAttendance);
});

</script>

	</body>
</html>

 