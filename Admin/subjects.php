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
		$subject_code=$_POST['subject_code'];
		$subject_name=$_POST['subject_name'];
		$semester=$_POST['semester'];
		$course_code=$_POST['course_code'];
		$credit_hours=$_POST['credit_hours'];

		$query="insert into course_subjects(subject_code,subject_name,course_code,semester,credit_hours)values('$subject_code','$subject_name','$course_code','$semester','$credit_hours')";
		$run=mysqli_query($con,$query);
		if ($run) {
			echo "successfully";
		}
		else{
			echo "not";
		}
	}

	if(isset($_POST['upload'])){
		$subject_id = $_POST['subject_id'];
		$syllabus_pdf = null;
	
		if(isset($_FILES['syllabus_pdf']) && $_FILES['syllabus_pdf']['error'] == 0){
			$target_dir = "uploads/syllabus/";
			if(!is_dir($target_dir)){
				mkdir($target_dir, 0777, true);
			}
	
			$file_name = time() . "_" . basename($_FILES['syllabus_pdf']['name']);
			$target_file = $target_dir . $file_name;
	
			if(move_uploaded_file($_FILES['syllabus_pdf']['tmp_name'], $target_file)){
				$syllabus_pdf = $file_name;
	
				// Update syllabus file in DB
				$sql = "UPDATE course_subjects SET syllabus_pdf='$syllabus_pdf' WHERE subject_code='$subject_id'";
				mysqli_query($con, $sql);
				echo "<script>alert('Syllabus uploaded successfully!'); window.location='subjects.php';</script>";
			} else {
				echo "<script>alert('File upload failed!');</script>";
			}
		}
	}
?>


<!doctype html>
<html lang="en">
	<head>
		<title>Admin - Subjects</title>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/css/bootstrap.min.css" integrity="sha512-2bBQCjcnw658Lho4nlXJcc6WkV/UxpE/sAokbXPxQNGqmNdQrWqtw26Ns9kFF/yG792pKR1Sx8/Y1Lf1XN4GKA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

	</head>
	<body>
		<?php include('../common/common-header.php') ?>
		<?php include('../common/admin-sidebar.php') ?>  

		<main role="main" class="col-xl-10 col-lg-9 col-md-8 ml-sm-auto px-md-4 mb-2 w-100">
			<div class="sub-main">
				<div class="bar-margin text-center d-flex flex-wrap flex-md-nowrap pt-3 pb-2 mb-3 text-white admin-dashboard pl-3">
					<h4>Subject Management System </h4>
				</div>

				<div class="row">
					<div class="col-md-12">
						<form action="subjects.php" method="post">
							<div class="row mt-3">
								<div class="col-md-6">
									<div class="form-group">
										<label for="exampleInputEmail1">Subject Code: </label>
										<input type="text" name="subject_code" class="form-control" required placeholder="Enter Subject Code" required>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="exampleInputPassword1">Subject Name:</label>
										<input type="text" name="subject_name" class="form-control" required placeholder="Enter Subject Name" required>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="exampleInputPassword1">Semester:</label>
										<input type="text" name="semester" class="form-control" required placeholder="Enter Semester" required>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="exampleInputEmail1">Course Code:</label>
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
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="exampleInputPassword1">Credit Hours:</label>
										<input type="number" name="credit_hours" class="form-control"  placeholder="Enter Subject Credit Hours" required>
									</div>
								</div>
								<div class="col-md-6 mt-4">
									<div class="form-group pt-2">
										<input type="submit" name="sub" value="Add Subject" class="btn btn-primary">
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
				
				<div class="row">
					<div class="col-md-12">
						<!-- Button trigger modal -->
							<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
							UPLOAD SYLLABUS 
							</button>

							<!-- Modal -->
							<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content">
									<div class="modal-header">
										<h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
										<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
									</div>
									<div class="modal-body">
										<!--form to upload syllabus-->
										<h2>Upload Syllabus PDF</h2>

										<form method="POST" enctype="multipart/form-data">
											<label for="subject_id">Select Subject:</label>
											<select name="subject_id" class="browser-default custom-select" required>
												<option value="">-- Select Subject --</option>
												<?php
												$result = mysqli_query($con, "SELECT subject_name, subject_code, semester FROM course_subjects");
												while($row = mysqli_fetch_assoc($result)){
													echo "<option value='{$row['subject_code']}'>{$row['subject_code']} - {$row['subject_name']} (Sem {$row['semester']})</option>";
												}
												?>
											</select>
											<br><br>
											<input type="file" name="syllabus_pdf" accept="application/pdf" required>
											<br><br>
											<button type="submit" class="btn btn-primary" name="upload">Upload</button>
										</form>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
										<button type="button" class="btn btn-primary">Save changes</button>
									</div>
									</div>
								</div>
							</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 ml-2">
						<section class="mt-3">
							<table class="w-100 table-elements table-three-tr" cellpadding="3">
								<tr class="table-tr-head table-three text-white">
									<th>Sr.No</th>
									<th>Subject Code</th>
									<th>Subject Name</th>
									<th>Course Code</th>
									<th>Semester</th>
									<th>Credit Hours</th>
									<th>Action</th>
								</tr>
								<?php
									$sr=1;
									$query="select subject_code,subject_name,course_code,semester,credit_hours from course_subjects";
									$run=mysqli_query($con,$query);
									while($row=mysqli_fetch_array($run)) {
									echo	"<tr>";
									echo	"<td>".$sr++."</td>";
									echo	"<td>".$row['subject_code']."</td>";
									echo	"<td>".$row['subject_name']."</td>";
									echo	"<td>".$row['course_code']."</td>";
									echo	"<td>".$row['semester']."</td>";
									echo	"<td>".$row['credit_hours']."</td>";
									echo	"<td width='20'><a class='btn btn-danger' href=delete-function.php?subject_code=".$row['subject_code'].">Delete</a></td>";
									echo	"</tr>";
									} 
								?>
							</table>				
						</section>
					</div>
				</div>
			</div>
		</main>
		<script type="text/javascript" src="../bootstrap/js/jquery.min.js"></script>
		<script type="text/javascript" src="../bootstrap/js/bootstrap.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/js/bootstrap.min.js" integrity="sha512-nKXmKvJyiGQy343jatQlzDprflyB5c+tKCzGP3Uq67v+lmzfnZUi/ZT+fc6ITZfSC5HhaBKUIvr/nTLCV+7F+Q==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	</body>
</html>
