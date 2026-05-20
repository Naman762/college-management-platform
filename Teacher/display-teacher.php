<!---------------- Session starts form here ----------------------->
<?php  
	session_start();
	if (!$_SESSION["LoginTeacher"])
	{
		header('location:../login/login.php');
	}
		require_once "../connection/connection.php";
	
			// teacher_id and email
		$teacher_id = $_SESSION["teacher_id"];
		$teacher_email = $_SESSION["teacher_email"];

	?>


<!---------------- Session Ends form here ------------------------>
<!doctype html>
<html lang="en">
	<head>
		<title>Admin - Teacher Information</title>
		<style>
			.teacher-design {
				align:center;
			}
			</style>
	</head>
	<body>
		<?php include('../common/common-header.php') ?>
		<?php include('../common/teacher-sidebar.php') ?>

		<main role="main" class="col-xl-10 col-lg-9 col-md-8 ml-sm-auto px-md-4 main-background mb-2 w-100">
			<div class="sub-main">
				<div class="text-center d-flex flex-wrap flex-md-nowrap pt-3 pb-2 mb-4 text-white admin-dashboard pl-3">
					<h4 class="">Teacher Profile Infromation</h4>
				</div>
				<div class="row">
					<div class="col-lg-12 col-md-12">
                   
                            <div class="container mt-4">
                            <div class="card shadow p-4">
	<?php
		if($teacher_id){
			$query="select * from teacher_info where teacher_id='$teacher_id'";
		}
		else{
			$query="select * from teacher_info where email='$teacher_email'";
		}
		
		$run=mysqli_query($con,$query);
		while ($row=mysqli_fetch_array($run)) {
	?>
		
		
									<div class="container  mt-1 border border-secondary mb-1 teacher-design">
										<div class="row text-white bg-primary pt-5">
											<div class="col-md-4">
												<?php  $profile_image= $row["profile_image"] ?>
												<img class="ml-5 mb-5" height='270px' width='250px' src="../admin/images/<?php echo $row['profile_image']; ?>" />
											</div>
											<div class="col-md-8">
												<h3 class="ml-5"><?php echo $row['first_name']." ".$row['middle_name']." ".$row['last_name'] ?></h3><br>
												<div class="row">
													<div class="col-md-6">
														<h5>Father Name:</h5> <?php echo $row['father_name']  ?><br><br>
														<h5>Email:</h5> <?php echo $row['email']  ?><br><br>
														<h5>Contact:</h5> <?php echo $row['phone_no']  ?><br><br>
													</div>
													<div class="col-md-6">
														<h5>Address:</h5> <?php echo $row['permanent_address']  ?><br><br>
														<h5>CNIC:</h5> <?php echo $row['cnic']  ?><br><br>
														<h5>Teacher I'd:</h5> <?php echo $row['teacher_id']  ?><br><br>
													</div>		
												</div>
											</div>
											<hr>
										</div>
										<div class="row pt-3">
											<div class="col-md-4"><h5>Status:</h5> <?php echo $row['teacher_status']  ?></div>
											<div class="col-md-4"><h5>Gender:</h5> <?php echo $row['gender']  ?></div>
											<div class="col-md-4"><h5>Date of Birth:</h5> <?php echo $row['dob']  ?></div>
										</div>
										<div class="row pt-3">
											<div class="col-md-4"><h5>Phone No:</h5> <?php echo $row['other_phone']  ?></div>
											<div class="col-md-4"><h5>State:</h5> <?php echo $row['state']  ?></div>
											<div class="col-md-4"><h5>Last Qualification:</h5> <?php echo $row['last_qualification']  ?></div>
										</div>
										<div class="row pt-3">
											<div class="col-md-4"><h5>Permanent Adress:</h5> <?php echo $row['permanent_address']  ?></div>
											<div class="col-md-4"><h5>Current Address:</h5> <?php echo $row['current_address']  ?></div>
											<div class="col-md-4"><h5>Place of Birth:</h5> <?php echo $row['place_of_birth']  ?></div>
										</div>
										<div class="row pt-3">
											<div class="col-md-4"><h5>Last Experience From</h5> <?php echo $row['matric_complition_date']  ?></div>
											<div class="col-md-4"><h5>Last Experience To</h5> <?php echo $row['matric_awarded_date']  ?></div>
											<div class="col-md-4"><h5>Experience Certificate:</h5> <?php echo $row['matric_certificate']  ?></div>
										</div>
										<div class="row pt-3">
											<div class="col-md-4"><h5>12th Complition Date:</h5> <?php echo $row['fa_complition_date']  ?></div>
											<div class="col-md-4"><h5>12th Awarded Date:</h5> <?php echo $row['fa_awarded_date']  ?></div>
											<div class="col-md-4"><h5>12th Certificate:</h5> <?php echo $row['fa_certificate']  ?></div>
										</div>
										<div class="row pt-3">
											<div class="col-md-4"><h5>Graduation Complition Date:</h5> <?php echo $row['ba_complition_date']  ?></div>
											<div class="col-md-4"><h5>Graduation Awarded Date:</h5> <?php echo $row['ba_awarded_date']  ?></div>
											<div class="col-md-4"><h5>Graduation Certificate:</h5> <?php echo $row['ba_certificate']  ?></div>
										</div>
										<div class="row pt-3">
											<div class="col-md-4"><h5>Master Complition Date:</h5> <?php echo $row['ma_complition_date']  ?></div>
											<div class="col-md-4"><h5>Master Awarded Date:</h5> <?php echo $row['ma_awarded_date']  ?></div>
											<div class="col-md-4"><h5>Master Certificate:</h5> <?php echo $row['ma_certificate']  ?></div>
										</div>
									</div>
	<?php } ?>
							</div>
							</div>
					</div>
				</div>
			</div>
		</main>
</body>
</html>
