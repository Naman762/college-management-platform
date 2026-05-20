 <!---------------- Session starts form here ----------------------->
 <?php  
	session_start();
	if (!$_SESSION["LoginTeacher"])
	{
		header('location:../login/login.php');
	}
		require_once "../connection/connection.php";

		$teacher_id = $_SESSION["teacher_id"];
		$teacher_email = $_SESSION["teacher_email"];
	?>
<!---------------- Session Ends form here ------------------------>


<!doctype html>
<html lang="en">
	<head>
		<title>Teacher - Courses</title>
	</head>
	<body>
		<?php include('../common/common-header.php') ?>
		<?php include('../common/teacher-sidebar.php') ?>  

		<main role="main" class="col-xl-10 col-lg-9 col-md-8 ml-sm-auto px-md-4 main-background mb-2 w-100">
			<div class="sub-main">
				<div class="text-center d-flex flex-wrap flex-md-nowrap pt-3 pb-2 mb-3 text-white admin-dashboard pl-3">
					<h4 class="">Teacher Courses Information</h4>
				</div>

				<div class="card-body">
                     <div class="col-md-4 ">
                        <form action="" method="POST">
                                    <div class="form-group d-flex">
                                  
									<select class="browser-default custom-select m-2" name="get_id">
													    <option >Select your subject code</option>
													    <?php
															$query="SELECT DISTINCT(course_code) from teacher_courses WHERE teacher_id= '$teacher_id'";
															$run=mysqli_query($con,$query);
															while($row=mysqli_fetch_array($run)) {
															 echo	"<option value=".$row['course_code'].">".$row['course_code']."</option>";
															}
														?>
									</select>
									
									<button type="submit" class="btn btn-primary m-2" name="search_by_id">search</button>
                        			</div>
                        </form>
                    </div>                   
                 </div>


				<?php
                  $conn = mysqli_connect("localhost","root","","imperial_college");   
				  
				  
				  if(isset($_POST['search_by_id']))
                            {
                                $id=$_POST['get_id'];
				
                  ?>
				<div class="row">
					<div class="col-md-12">
						<section class="border mt-3">
				
						<table class="w-100 table-elements table-one-tr"cellpadding="2">
									<tr class="pt-5 table-one text-white" style="height: 32px;">
										<th>Subject code</th>
										<th>Subject Name</th>
										<th>Course</th>
										<th>Syllabus</th>
										<th>Assign Date</th>
									</tr>
									<?php  
										$query="SELECT 
										tc.subject_code,
										cs.subject_name,
										tc.course_code,
										cs.syllabus_pdf,
										tc.assign_date
									FROM teacher_courses tc
									JOIN course_subjects cs 
										ON tc.subject_code = cs.subject_code
									WHERE tc.teacher_id = '$teacher_id'";

										$run=mysqli_query($con,$query);
										while($row=mysqli_fetch_array($run)) {
											echo "<tr>";
											echo "<td>" . $row['subject_code'] . "</td>";
											echo "<td>" . $row['subject_name'] . "</td>";
											echo "<td>" . $row['course_code'] . "</td>";
											
											// syllabus pdf link (if available)
											if (!empty($row['syllabus_pdf'])) {
												echo "<td><a href='uploads/syllabus/" . $row['syllabus_pdf'] . "' target='_blank'>Download</a></td>";
											} else {
												echo "<td>Not Available</td>";
											}

											echo "<td>" . $row['assign_date'] . "</td>";
											echo "</tr>";
										}
									?>

								<?php
								
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
	</body>
</html>
								