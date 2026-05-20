<?php  
	session_start();
	if (!$_SESSION["LoginStudent"])
	{
		header('location:../login/login.php');
	}
		require_once "../connection/connection.php";
	
        $roll=$_SESSION['roll_no'];
    ?>
<!---------------- Session Ends form here ------------------------>


<!doctype html>
<html lang="en">
	<head>
		<title>Student - Informations</title>
	</head>
	<body>
		<?php include('../common/common-header.php') ?>
		<?php include('../common/student-sidebar.php') ?>  
        <main role="main" class="col-xl-10 col-lg-9 col-md-8 ml-sm-auto px-md-4 main-background mb-2 w-100">
			<div class="sub-main">
				<div class="text-center d-flex flex-wrap flex-md-nowrap pt-3 pb-2 mb-4 text-white admin-dashboard pl-3">
					<h4 class="">Student Profile Infromation</h4>
				</div>

        <?php
	$query="select * from student_info where roll_no='$roll'";
	$run=mysqli_query($con,$query);
	while ($row=mysqli_fetch_array($run)) {
		?>
		<div class="container  mt-1 border border-secondary mb-1 student-style">
			<div class="row text-white bg-primary pt-5">
				<div class="col-md-4">
					<?php  $profile_image= $row["profile_image"] ?>
					<img class="ml-5 mb-5" height='290px' width='250px' src=<?php echo "images/$profile_image"  ?> >
				</div>
				<div class="col-md-8">
					<h3 class="ml-5"><?php echo $row['first_name']." ".$row['middle_name']." ".$row['last_name'] ?></h3><br>
					<div class="row">
						<div class="col-md-6">
							<h5>Father Name:</h5> <?php echo $row['father_name']  ?><br><br>
							<h5>Email:</h5> <?php echo $row['email']  ?><br><br>
							<h5>Contact:</h5> <?php echo $row['mobile_no']  ?><br><br>
						</div>
						<div class="col-md-6">
							<h5>Address:</h5> <?php echo $row['permanent_address']  ?><br><br>
							<h5>Aadhar-no.:</h5> <?php echo $row['aadhar']  ?><br><br>
							<h5>Roll No:</h5> <?php echo $row['roll_no']  ?><br><br>
						</div>		
					</div>
				</div>
				<hr>
			</div>
			<div class="row pt-3">
				
				<div class="col-md-4"><h5>Prospectus Status:</h5> <?php echo $row['prospectus_issued']  ?></div>
			</div>
			<div class="row pt-3">
				<div class="col-md-4"><h5>Phone No:</h5> <?php echo $row['mobile_no']  ?></div>
				<div class="col-md-4"><h5>State:</h5> <?php echo $row['state']  ?></div>
				<div class="col-md-4"><h5>Semester:</h5> <?php echo $row['semester']  ?></div>
			</div>
			<div class="row pt-3">
				<div class="col-md-4"><h5>Gender:</h5> <?php echo $row['gender']  ?></div>
				<div class="col-md-4"><h5>Course:</h5> <?php echo $row['course_code']  ?></div>
				<div class="col-md-4"><h5>Session:</h5> <?php echo $row['session']  ?></div>
			</div>
			<div class="row pt-3">
				<div class="col-md-4"><h5>Date of Birth:</h5> <?php echo $row['dob']  ?></div>
				<div class="col-md-4"><h5>Admission Date:</h5> <?php echo $row['admission_date']  ?></div>
				<div class="col-md-4"><h5>Category</h5> <?php echo $row['category']  ?></div>
			</div>
			<div class="row pt-3">
				<div class="col-md-4"><h5>Permanent Adress:</h5> <?php echo $row['permanent_address']  ?></div>
				<div class="col-md-4"><h5>Current Address:</h5> <?php echo $row['current_address']  ?></div>
				<div class="col-md-4"><h5>Place of Birth:</h5> <?php echo $row['place_of_birth']  ?></div>
			</div>
			<div class="row pt-3">
				<div class="col-md-4"><h5>Matric Complition Date:</h5> <?php echo $row['matric_complition_date']  ?></div>
				<div class="col-md-4"><h5>Matric Percentage:</h5> <?php echo $row['SS_per']  ?><span>%</span></div>
				<div class="col-md-4"><h5>Matric Certificate:</h5> <?php echo $row['matric_certificate']  ?></div>
			</div>
			<div class="row pt-3">
				<div class="col-md-4"><h5>12th Complition Date:</h5> <?php echo $row['SS_complition_date']  ?></div>
				<div class="col-md-4"><h5>12th Percentage:</h5> <?php echo $row['SS_per']  ?></div>
				<div class="col-md-4"><h5>12th Certificate:</h5> <?php echo $row['SS_certificate']  ?></div>
			</div>
			<div class="row pt-3">
				<div class="col-md-4"><h5>Tranfer Certificate :</h5> <?php 
					if (!empty($row['transfer_cer'])) {
						echo "<a href='images/{$row['transfer_cer']}' target='_blank' style='color:green; font-weight:bold;'>Submitted (Download)</a>";
					} else {
						echo "<span style='color:red; font-weight:bold;'>Not Submitted</span>";
					}
					?>
				</div>
				
				<div class="col-md-4"><h5>Migration:</h5> <?php 
					if (!empty($row['migration'])) {
						echo "<a href='images/{$row['migration']}' target='_blank' style='color:green; font-weight:bold;'>Submitted (Download)</a>";
					} else {
						echo "<span style='color:red; font-weight:bold;'>Not Submitted</span>";
					}
					?>
				</div>
			</div>
			<div class="row pt-3">
				<div class="col-md-4"><h5>ABC id:</h5> <?php echo $row['abc_id']  ?></div>
				<div class="col-md-4"><h5>ABC id PDF</h5> <?php 
						if (!empty($row['abc_img'])) {
							echo "<a href='images/{$row['abc_img']}' target='_blank' style='color:green; font-weight:bold;'>Submitted (Download)</a>";
						} else {
							echo "<span style='color:red; font-weight:bold;'>Not Submitted</span>";
						}
					?>
				</div>
			</div>
		</div>
	<?php } ?>
                    </div>
                    </main>
</body>
</html>