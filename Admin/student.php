<!---------------- Session starts form here ----------------------->
<?php  
	session_start();
	if (!$_SESSION["LoginAdmin"])
	{
		header('location:../login/login.php');
	}
		require_once "../connection/connection.php";
		$_SESSION["LoginStudent"]="";
	?>
<!---------------- Session Ends form here ------------------------>


<!--*********************** PHP code starts from here for data insertion into database ******************************* -->


<?php  
//to get roll number automatically

	// Default
	$next_roll = 101;

	// If course is fixed OR you want global roll numbers:
	$sql = "SELECT roll_no FROM student_info ORDER BY CAST(roll_no AS UNSIGNED) DESC LIMIT 1";
	$result = mysqli_query($con, $sql);
	if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $next_roll = intval($row['roll_no']) + 1;
	}

 	if (isset($_POST['btn_save'])) {

		$roll_no= $_POST["roll_no"];

 		$first_name=$_POST["first_name"];

 		$middle_name=$_POST["middle_name"];
 		
 		$last_name=$_POST["last_name"];
 		
 		$father_name=$_POST["father_name"];
 		
 		$email=$_POST["email"];
 		
 		$mobile_no=$_POST["mobile_no"];

 		$course_code=$_POST['course_code'];

 		$session=$_POST['session'];
 		
 		$prospectus_issued=$_POST["prospectus_issued"];
 		
 		$semester_num=$_POST["semester_no"];
 		
 		$aadhar=$_POST["aadhar"];
 		
 		$dob=$_POST["dob"];
 		 		
 		$gender=$_POST["gender"];
 		
		$permanent_address=$_POST["permanent_address"];
 		
 		$current_address=$_POST["current_address"];
 		
 		$place_of_birth=$_POST["place_of_birth"];

		$state_stu = $_POST["state"];
 		
 		$matric_complition_date=$_POST["matric_complition_date"];

		$matric_per = $_POST["matric_per"];
 		
 		$SS_complition_date=$_POST["SS_complition_date"];

		$SS_per = $_POST["SS_per"];

		$abc_id = $_POST["abc_id"];

		$category = $_POST["category"];

 		$password=$_POST['password'];

 		$role=$_POST['role'];

 		

// *****************************************Images upload code starts here********************************************************** 
		$profile_image = $_FILES['profile_image']['name'];$tmp_name=$_FILES['profile_image']['tmp_name'];$path = "images/".$profile_image;move_uploaded_file($tmp_name, $path);

		$matric_certificate = $_FILES['matric_certificate']['name'];$tmp_name=$_FILES['matric_certificate']['tmp_name'];$path = "images/".$matric_certificate;move_uploaded_file($tmp_name, $path);

		$SS_certificate = $_FILES['SS_certificate']['name'];$tmp_name=$_FILES['SS_certificate']['tmp_name'];$path = "images/".$SS_certificate;move_uploaded_file($tmp_name, $path);

		$transfer_cer = $_FILES['transfer_cer']['name'];$tmp_name=$_FILES['transfer_cer']['tmp_name'];$path = "images/".$transfer_cer;move_uploaded_file($tmp_name, $path);

		$migration = $_FILES['migration']['name'];$tmp_name=$_FILES['migration']['tmp_name'];$path = "images/".$migration;move_uploaded_file($tmp_name, $path);

		$abc_img = $_FILES['abc_img']['name'];$tmp_name=$_FILES['abc_img']['tmp_name'];$path = "images/".$abc_img;move_uploaded_file($tmp_name, $path);

// *****************************************Images upload code end here********************************************************** 

		//username and password code
		$username = strtolower(substr($first_name, 0, 3) . substr($last_name, -3) . rand(100, 999));

		// Step 1: Find the last admission number for this course
		$sql = "SELECT admission_number 
		FROM student_info 
		WHERE course_code = '$course_code' 
		ORDER BY CAST(roll_no AS UNSIGNED) DESC LIMIT 1";
		$result = mysqli_query($con, $sql);

		if(mysqli_num_rows($result) > 0){
			$row = mysqli_fetch_assoc($result);
			$last_adm = $row['admission_number'];

			// Extract last number (after last "/")
			$parts = explode("/", $last_adm);
			$last_no = intval(end($parts));
			$new_no = $last_no + 1;
		} else {
			$new_no = 101; // First student starts from 101
		}

		// Step 2: Create new admission number
		$admission_number = "ACPS/" . $course_code . "/" . $new_no;

 		$query="Insert into student_info(roll_no,first_name,middle_name,last_name,father_name,email,mobile_no,course_code,session,profile_image,prospectus_issued,semester,aadhar,dob,gender,permanent_address,current_address,place_of_birth,state,matric_complition_date,matric_certificate,SS_complition_date,SS_certificate,transfer_cer,migration,abc_id,abc_img,category,SS_per,matric_per,admission_number)values('$roll_no','$first_name','$middle_name','$last_name','$father_name','$email','$mobile_no','$course_code','$session','$profile_image','$prospectus_issued','$semester_num','$aadhar','$dob','$gender','$permanent_address','$current_address','$place_of_birth','$state_stu','$matric_complition_date','$matric_certificate','$SS_complition_date','$SS_certificate','$transfer_cer','$migration','$abc_id','$abc_img','$category','$SS_per','$matric_per','$admission_number')";

 		$run=mysqli_query($con, $query);
 		if ($run) {
			mysqli_query($con, "INSERT INTO student_academics(roll_no, course_code, semester, session_year, status) VALUES ('$roll_no', '$course_code', '$semester_num', '$session', 'Active')");


			$student_id = $roll_no;
			$assign_date=date("d-m-y");
			//assign student subjects
			$subquery = "SELECT subject_code FROM course_subjects WHERE course_code='$course_code' AND semester='$semester_num'";
			$subresult = mysqli_query($con, $subquery);
				while($row = mysqli_fetch_assoc($subresult)){
					$subject_id = $row['subject_code'];
					mysqli_query($con,"INSERT INTO student_subjects(roll_no, subject_code, semester, session_year,assign_date)VALUES('$student_id','$subject_id','$semester_num','$session','$assign_date')");
				}
 			echo "Your Data and subjects has been submitted";
 		}
 		else {
 			echo "Your Data has not been submitted";
 		}
 		$query2="insert into login(user_id,Password,Role,email)values('$username','$password','$role','$email')";
 		$run2=mysqli_query($con, $query2);
 		if ($run2) {
 			echo "Your Data has been submitted into login";
 		}
 		else {
 			echo "Your Data has not been submitted into login";
 		}
		
 	}

?>


<?php  
	if (isset($_POST['btn_save2'])) {
		$course_code=$_POST['course_code'];

		$semester=$_POST['semester'];

		$roll_no=$_POST['roll_no'];

		$subject_code=$_POST['subject_code'];

		$date=date("d-m-y");

		$query3="insert into student_courses(course_code,semester,roll_no,subject_code,assign_date)values('$course_code','$semester','$roll_no','$subject_code','$date')";
		$run3=mysqli_query($con,$query3);
		if ($run3) {
 			echo "Your Data has been submitted";
 		}
 		else {
 			echo "Your Data has not been submitted";
 		}


	}
?>
<!--*********************** PHP code end from here for data insertion into database ******************************* -->
 
<!doctype html>
<html lang="en">
	<head>
		<title>Admin - Register Student</title>
	</head>
	<body>
		<?php include('../common/common-header.php') ?>
		<?php include('../common/admin-sidebar.php') ?>
		<main role="main" class="col-xl-10 col-lg-9 col-md-8 ml-sm-auto px-md-4 w-100">
			<div class="sub-main">
				<div class="text-center d-flex flex-wrap flex-md-nowrap pt-3 pb-2 mb-3 text-white admin-dashboard pl-3">
					<div class="d-flex">
						<h4 class="mr-5">Student Management System </h4>
						<button type="submit" class="btn btn-primary ml-5" data-toggle="modal" data-target=".bd-example-modal-lg" name="add_stu">Add Student</button>
					</div>
				</div>
				<div class="col-md-2 pt-3 w-100">
  				    <!-- Large modal -->
					<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
					   <div class="modal-dialog modal-lg">
						    <div class="modal-content">
							    <div class="modal-header bg-info text-white">
							        <h4 class="modal-title text-center">Add New Student</h4>
						        </div>
							    <div class="modal-body">
							        <form action="student.php" method="POST" enctype="multipart/form-data">
										<div class="row mt-3">
											<div class="col-md-4">
											    <div class="form-group">
											        <label for="exampleInputEmail1">Applicant First Name:*</label>
											        <input type="text" name="first_name" class="form-control" required>
											    </div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
												    <label for="exampleInputPassword1">Applicant Middle Name:</label>
												    <input type="text" name="middle_name" class="form-control">
											    </div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
												    <label for="exampleInputPassword1" required>Applicant Last Name:*</label>
												    <input type="text" name="last_name" class="form-control">
											    </div>
											</div>
								  		</div>
								  		<div class="row">
											<div class="col-md-4">
											    <div class="form-group">
											        <label for="exampleInputEmail1">Fatherr Name:*</label>
											        <input type="text" name="father_name" class="form-control" required>
											    </div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
												    <label for="exampleInputPassword1">Student Roll No:</label>
												    <input type="text" name="roll_no" class="form-control" value="<?php echo $next_roll; ?>" readonly>
											    </div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
												    <label for="exampleInputPassword1">Applicant Email:*</label>
												    <input type="email" name="email" class="form-control" id="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" required>
													<div id="email_status"></div>
											    </div>
											</div>
								  		</div>
								  		<div class="row">
											<div class="col-md-4">
											    <div class="form-group">
											        <label for="exampleInputEmail1">Course which you want?: </label>
											        <select class="browser-default custom-select" name="course_code">
													    <option >Select Course</option>
													    <?php
															$query="select course_code from courses";
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
												    <label for="exampleInputPassword1">Select Session:</label>
												    <select class="browser-default custom-select" name="session">
													    <option >Select Session</option>
													    <?php
															$query="select session from sessions";
															$run=mysqli_query($con,$query);
															while($row=mysqli_fetch_array($run)) {
															 echo	"<option value=".$row['session'].">".$row['session']."</option>";
															}
														?>
													</select>

											    </div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
												    <label for="exampleInputPassword1">Your Profile Image:</label>
												    <input type="file" name="profile_image" placeholder="Student Age" class="form-control">
											    </div>
											</div>
								  		</div>
								  		<div class="row">
											<div class="col-md-4">
											    <div class="form-group">
											        <label for="exampleInputEmail1">Prospectus Issude: </label>
											        <select class="browser-default custom-select" name="prospectus_issued">
													  <option>Select Option</option>
													  <option value="Yes">Yes</option>
													  <option value="No">No</option>
													</select>
											    </div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
												    <label for="exampleInputPassword1">Select Semester</label>
												    <select class="browser-default custom-select" name="semester_no">
													  <option>Select Option</option>
													  <option value="1">1</option>
													  <option value="2">2</option>
													  <option value="2">3</option>
													  <option value="2">4</option>
													  <option value="2">5</option>
													  <option value="2">6</option>
													</select>
											    </div>
											</div>
											
											<div class="col-md-4">
												<div class="form-group">
												    <label for="exampleInputPassword1">Aadhar-no.:</label>
												    <input type="text" name="aadhar" data-inputmask="'mask': 9999-9999-9999" placeholder="XXXX-XXXX-XXXX" class="form-control">
											    </div>
											</div>
								  		</div>
								  		<div class="row">
											<div class="col-md-4">
											    <div class="form-group">
											        <label for="exampleInputEmail1">Date of Birth: </label>
											        <input type="date" name="dob" class="form-control">
											    </div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
												    <label for="exampleInputPassword1">Mobile No:*</label>
												    <input type="number" name="mobile_no" class="form-control" required>
											    </div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
												    <label for="exampleInputPassword1">Gender:</label>
												    <select class="browser-default custom-select" name="gender">
													  <option>Select Gender</option>
													  <option value="Male">Male</option>
													  <option value="Female">Female</option>
													</select>
											    </div>
											</div>
								  		</div>
								  		<div class="row">
											<div class="col-md-6">
											    <div class="form-group">
											        <label for="exampleInputEmail1">Permanent Address: </label>
											        <input type="text" name="permanent_address" class="form-control">
											    </div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
												    <label for="exampleInputPassword1">Current Address:</label>
												    <input type="text" name="current_address" class="form-control">
											    </div>
											</div>
								  		</div>
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
												    <label for="exampleInputPassword1">Place of Birth:</label>
												    <input type="text" name="place_of_birth" class="form-control">
											    </div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
												    <label for="exampleInputPassword1">State</label>
															<select id="state" name="state" class="form-control" required>
															<option value="">-- Select State --</option>
															<option value="Andhra Pradesh">Andhra Pradesh</option>
															<option value="Arunachal Pradesh">Arunachal Pradesh</option>
															<option value="Assam">Assam</option>
															<option value="Bihar">Bihar</option>
															<option value="Chhattisgarh">Chhattisgarh</option>
															<option value="Goa">Goa</option>
															<option value="Gujarat">Gujarat</option>
															<option value="Haryana">Haryana</option>
															<option value="Himachal Pradesh">Himachal Pradesh</option>
															<option value="Jharkhand">Jharkhand</option>
															<option value="Karnataka">Karnataka</option>
															<option value="Kerala">Kerala</option>
															<option value="Madhya Pradesh">Madhya Pradesh</option>
															<option value="Maharashtra">Maharashtra</option>
															<option value="Manipur">Manipur</option>
															<option value="Meghalaya">Meghalaya</option>
															<option value="Mizoram">Mizoram</option>
															<option value="Nagaland">Nagaland</option>
															<option value="Odisha">Odisha</option>
															<option value="Punjab">Punjab</option>
															<option value="Rajasthan">Rajasthan</option>
															<option value="Sikkim">Sikkim</option>
															<option value="Tamil Nadu">Tamil Nadu</option>
															<option value="Telangana">Telangana</option>
															<option value="Tripura">Tripura</option>
															<option value="Uttar Pradesh">Uttar Pradesh</option>
															<option value="Uttarakhand">Uttarakhand</option>
															<option value="West Bengal">West Bengal</option>
															<!-- Union Territories -->
															<option value="Andaman and Nicobar Islands">Andaman and Nicobar Islands</option>
															<option value="Chandigarh">Chandigarh</option>
															<option value="Dadra and Nagar Haveli and Daman and Diu">Dadra and Nagar Haveli and Daman and Diu</option>
															<option value="Delhi">Delhi</option>
															<option value="Jammu and Kashmir">Jammu and Kashmir</option>
															<option value="Ladakh">Ladakh</option>
															<option value="Lakshadweep">Lakshadweep</option>
															<option value="Puducherry">Puducherry</option>
</select>

											    </div>
											</div>
										</div>	
								  		<div class="row">
											<div class="col-md-4">
											    <div class="form-group">
											        <label for="exampleInputEmail1">Matric Complition Date: </label>
											        <input type="date" name="matric_complition_date" class="form-control">
											    </div>
											</div>
											<div class="col-md-4">
											    <div class="form-group">
											        <label for="exampleInputEmail1">10th Percentage: </label>
											        <input type="text" name="matric_per" class="form-control">
											    </div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
												    <label for="exampleInputPassword1">Upload Matric Certificate:</label>
												    <input type="file" name="matric_certificate" class="form-control" value="there is no image">
											    </div>
											</div>
								  		</div>
								  		<div class="row">
											<div class="col-md-4">
											    <div class="form-group">
											        <label for="exampleInputEmail1">12th Complition Date: </label>
											        <input type="date" name="SS_complition_date" class="form-control">
											    </div>
											</div>
											<div class="col-md-4">
											    <div class="form-group">
											        <label for="exampleInputEmail1">12th Percentage: </label>
											        <input type="text" name="SS_per" class="form-control">
											    </div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
												    <label for="exampleInputPassword1">Upload 12th Certificate:</label>
												    <input type="file" name="SS_certificate" class="form-control" value="there is no image" >
											    </div>
											</div>
								  		</div>
								  		<div class="row">
											<div class="col-md-6">
											    <div class="form-group">
											        <label for="exampleInputEmail1">Transfer Certificate </label>
											        <input type="file" name="transfer_cer" class="form-control" value="there is no image">
											    </div>
											</div>
											
											<div class="col-md-6">
												<div class="form-group">
												    <label for="exampleInputPassword1">Migration</label>
												    <input type="file" name="migration" class="form-control" value="there is no image">
											    </div>
											</div>
								  		</div>
										<div class="row">
											<div class="col-md-6">
											    <div class="form-group">
											        <label for="exampleInputEmail1">ABC ID </label>
											        <input type="number" name="abc_id" class="form-control" required>
											    </div>
											</div>
											
											<div class="col-md-6">
												<div class="form-group">
												    <label for="exampleInputPassword1">ABC ID image</label>
												    <input type="file" name="abc_img" class="form-control" value="there is no image">
											    </div>
											</div>
								  		</div>
										<div class="row">
											<div class="col-md-12">				
												<label for="exampleInputPassword1" >Select Category:</label>
													<input type="radio" name="category" value="GENERAL" id="general"> General
													<input type="radio" name="category" value="OBC" id="obc"> OBC
													<input type="radio" name="category" value="SC" id="sc"> SC
													<input type="radio" name="category" value="ST" id="st"> ST
											</div>
										</div>
								  		<!-- _________________________________________________________________________________
								  											Hidden Values are here
								  		_________________________________________________________________________________ -->
								  		<div>
											<input type="hidden" name="password" value="student123*">
											<input type="hidden" name="role" value="Student">
								  		</div>
								  		<!-- _________________________________________________________________________________
								  											Hidden Values are end here
								  		_________________________________________________________________________________ -->
								  		<div class="modal-footer">
						   		            <input type="submit" class="btn btn-primary" name="btn_save">
		      								<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
									    </div>
									</form>
						        </div>
						    </div>
					   </div>
					</div>
				</div>
				<div class="row w-100">
					<div class="col-md-12 ml-2">
						<section class="mt-3">
							<div class="row">
								<div class="col-md-6">
									<form action="search_student.php" method="post">
										<div class="form-group">
											<label for="exampleInputPassword1"><h5>Search:</h5></label>
											<div class="d-flex">
												<input type="text" name="search" id="search" class="form form-control" placeholder="Enter Roll No, Name, Semester, or Course" required>
												<input class="btn btn-primary px-4 ml-4" type="submit" name="btnSearch" value="Search">
											</div>
										</div>
									</form>
								</div>	
								<div class="col-md-12 pt-5 mb-2">
									<!-- Large modal -->
									<button type="button" class="btn btn-primary ml-auto" data-toggle="modal" data-target=".bd-example-modal-lg1">Assign Subjects</button>
									<a class="btn btn-success" href="student-promote.php"> Promote Student to next SEMESTER</a>
									<div class="modal fade bd-example-modal-lg1" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
										<div class="modal-dialog modal-lg">
												<div class="modal-content">
													<div class="modal-header bg-info text-white">
														<h4 class="modal-title text-center">Assign Subjects To Student</h4>
													</div>
													<div class="modal-body">
														<form action="student.php" method="POST" enctype="multipart/form-data">
															<div class="row mt-3">
																<div class="col-md-6">
																	<div class="form-group">
																		<label for="exampleInputEmail1">Select Course:*</label>
																		<select class="browser-default custom-select" name="course_code" required="">
																			<option >Select Course</option>
																			<?php
																				$query="select course_code from courses";
																				$run=mysqli_query($con,$query);
																				while($row=mysqli_fetch_array($run)) {
																				echo	"<option value=".$row['course_code'].">".$row['course_code']."</option>";
																				}
																			?>
																		</select>
																	</div>
																</div>
																<div class="col-md-6">
																	<div class="form-group">
																		<label for="exampleInputPassword1" required>Enter Semester:*</label>
																		<input type="text" name="semester" class="form-control">
																	</div>
																</div>
															</div>
															<div class="row">
																<div class="col-md-6">
																	<div class="form-group">
																		<label for="exampleInputPassword1">Enter Roll No:*</label>
																		<input type="text" name="roll_no" class="form-control">
																	</div>
																</div>
																<div class="col-md-6">
																	<div class="form-group">
																		<label for="exampleInputPassword1">Select Subject:*</label>
																		<select class="browser-default custom-select" name="subject_code" required="">
																			<option >Select Subject</option>
																			<?php
																				$query="select subject_code from course_subjects";
																				$run=mysqli_query($con,$query);
																				while($row=mysqli_fetch_array($run)) {
																				echo	"<option value=".$row['subject_code'].">".$row['subject_code']."</option>";
																				}
																			?>
																		</select>
																	</div>
																</div>	
															</div>
															<div class="modal-footer">
																<input type="submit" class="btn btn-primary" name="btn_save2">
																<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
															</div>
														</form>
													</div>
												</div>
										</div>
									</div>
								</div>
							</div>
							<table class="w-100 table-elements mb-5 table-three-tr text-center" cellpadding="10">
								<tr class="table-tr-head table-three text-white">
									<th>Roll.No</th>
									<th>Student Name</th>
									<th>Current Address</th>
									<th>Session</th>
									<th>Course ID</th>
									<th>Admission</th>
									<th>Profile</th>
									<th colspan="1">Operations</th>
								</tr>
								<?php 
								$query="select first_name,middle_name,admission_date,last_name,current_address,session,roll_no,profile_image,course_code from student_info";
								$run=mysqli_query($con,$query);
								while($row=mysqli_fetch_array($run)) {?>
									<tr>
										<td><?php echo $row["roll_no"] ?></td>
										<td><?php echo $row["first_name"]." ".$row["middle_name"]." ".$row["last_name"] ?></td>
										<td><?php echo $row["current_address"] ?></td>
										<td><?php echo $row["session"] ?></td>
										<td><?php echo $row["course_code"] ?></td>
										<!-- date_format($date,"Y/m/d H:i:s"); -->
										<td><?php echo date("Y-M-d",strtotime($row["admission_date"])); ?></td>
										<td><?php  $profile_image= $row["profile_image"] ?>
										<img height='50px' width='50px' src=<?php echo "images/$profile_image"  ?> >
										</td>
										<td width='170'> 
											<?php 
												echo "<a class='btn btn-primary' href=display-student.php?roll_no=".$row['roll_no'].">Profile</a> 
												<a class='btn btn-danger' href=delete-function.php?roll_no=".$row['roll_no'].">Delete</a> "
											?>
										</td>
									</tr>
								<?php }
								?>
							</table>				
						</section>
					</div>
				</div>	 
			</div>
		</main>
		<script type="text/javascript" src="../bootstrap/js/jquery.min.js"></script>
		<script type="text/javascript" src="../bootstrap/js/bootstrap.min.js"></script>
		<!-- jQuery (must be included before script) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function(){
    $("#email").on("keyup", function(){
        var email = $(this).val();
        if(email.length > 3){ // only check if more than 3 characters
            $.ajax({
                url: "check_email.php",
                method: "POST",
                data: { email: email },
                success: function(response){
                    if(response.trim() == "exists"){
                        $("#email_status").html(
                          '<div class="alert alert-danger p-1 mt-1">This email is already registered</div>'
                        );
                    } else {
                        $("#email_status").html(
                          '<div class="alert alert-success p-1 mt-1">Email available</div>'
                        );
                    }
                }
            });
        } else {
            $("#email_status").html("");
        }
    });
});
</script>

	</body>
</html>