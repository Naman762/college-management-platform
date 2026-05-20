<!---------------- Session starts form here ----------------------->
<?php  
	session_start();
	if (!$_SESSION["LoginAdmin"])
	{
		header('location:../login/login.php');
	}
		require_once "../connection/connection.php";
		$_SESSION['LoginTeacher']="";
	?>
<!---------------- Session Ends form here ------------------------>

<!doctype html>
<html lang="en">
	<head>
		<title>Admin - Register Teacher</title>
	
        <style>
          table td, table th {
    white-space: normal;     /* Allow text to wrap */
    word-wrap: break-word;   /* Break long words if needed */
    max-width: 150px;        /* Optional: control column width */
}
        </style>
    </head>
	<body>
		<?php include('../common/common-header.php') ?>
		<?php include('../common/admin-sidebar.php') ?>

        <main role="main" class="col-xl-10 col-lg-9 col-md-8 ml-sm-auto px-md-4 mb-2 w-100">
			<div class="sub-main">
				<div class="text-center d-flex flex-wrap flex-md-nowrap pt-3 pb-2 mb-3 text-white admin-dashboard pl-3">
					<div class="d-flex">
						<h4 class="mr-5">Subjects Assigned to the Faculties :</h4>
					</div>
				</div>

                
                <table class="w-100 table-elements mb-5 table-three-tr table table-bordered text-wrap" cellpadding="10">
                                <tr class="table-tr-head table-three text-white">
                                                <th> Course ID</th>
                                                <th>Teacher Name</th>
                                                <th>Course Code</th>
                                                <th>Semester</th>
                                                <th>Teacher_id</th>
                                                <th>Subject</th>
                                                <th>Total Classes</th>
                                                <th>Actions</th>
                                            </tr>
                                            <?php 
                                           $query = "SELECT teacher_courses_id, course_code, semester, teacher_id, teacher_name, subject_code, assign_date, total_classes FROM teacher_courses";
                                            $run=mysqli_query($con,$query);
                                            while($row=mysqli_fetch_array($run)) {
                                                echo "<tr>";
                                                echo "<td>".$row["teacher_courses_id"]."</td>";
                                                echo "<td>".$row["teacher_name"]."</td>";
                                                echo "<td>".$row["course_code"]."</td>";
                                                echo "<td>".$row["semester"]."</td>";
                                                echo "<td>".$row["teacher_id"]."</td>";
                                                echo "<td>".$row["subject_code"]."</td>";
                                                echo "<td>".$row["total_classes"]."</td>";
                                                echo	"<td width='170'><a class='btn btn-primary' href=display-teacher.php?teacher_id=".$row['teacher_id'].">Profile</a> <a class='btn btn-danger' href=delete-function.php?teacher_courses_id=".$row['teacher_courses_id'].">Delete</a></td>";
                                                echo "</tr>";
                                            }
                                            ?>
							</table>	
            </div>
        </main>
    </body>
</html>