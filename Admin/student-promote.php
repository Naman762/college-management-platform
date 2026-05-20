<?php  
	session_start();
	if (!$_SESSION["LoginAdmin"])
	{
		header('location:../login/login.php');
	}
		require_once "../connection/connection.php";

// PROMOTION LOGIC
if (isset($_POST['promote_btn'])) {
    $roll_no = $_POST['roll_no'];
    $current_sem = $_POST['current_sem'];
    $next_sem = $current_sem + 1;

    //Check if result exists for current semester
    $check_result = mysqli_query($con, "SELECT * FROM student_results WHERE roll_no='$roll_no' AND semester='$current_sem'");
    if (mysqli_num_rows($check_result) == 0) {
        echo "<script>alert('Cannot promote! Result for Semester $current_sem not found. Please enter and declare result first.');</script>";
    } else {
        // Mark current semester as completed
        mysqli_query($con, "UPDATE student_academics 
                            SET status='Completed' 
                            WHERE roll_no='$roll_no' 
                            AND semester='$current_sem'");

        // Insert next semester record
        mysqli_query($con, "INSERT INTO student_academics (roll_no, course_code, semester, session_year, status)
                            SELECT roll_no, course_code, '$next_sem', session, 'Active'
                            FROM student_info WHERE roll_no='$roll_no'");

        // Update semester in main student_info
        mysqli_query($con, "UPDATE student_info SET semester='$next_sem' WHERE roll_no='$roll_no'");

        // Assign next semester subjects
        $subquery = "SELECT subject_code FROM course_subjects 
                     WHERE course_code=(SELECT course_code FROM student_info WHERE roll_no='$roll_no') 
                     AND semester='$next_sem'";
        $subresult = mysqli_query($con, $subquery);

        $assign_date = date("Y-m-d");
        $session_year = date("Y") . "-" . (date("Y")+1);

        while ($row = mysqli_fetch_assoc($subresult)) {
            $subject_code = $row['subject_code'];
            mysqli_query($con, "INSERT INTO student_subjects (roll_no, subject_code, semester, session_year, assign_date)
                                VALUES ('$roll_no', '$subject_code', '$next_sem', '$session_year', '$assign_date')");
        }

        echo "<script>alert('Student $roll_no promoted to semester $next_sem successfully!');</script>";
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
					<h4>Promote Student</h4>
				</div>
                <div class="alert alert-danger" role="alert">Use Promote page carefully <code>(To promote student in next semester )</code></div>
            </div>
            <div class="container-fluid">
                <h3 class="text-center">Promote Students to Next Semester</h3>

                    <div class="row">
                        <!-- Filter Form -->
                        <div class="col-md-3">
                            <form method="get" class="card p-3 shadow-sm">
                                <h5 class="mb-3">Filter Students</h5>
                                <div class="mb-3">
                                    <label class="form-label">Course</label>
                                    <select name="course" class="form-select" required>
                                        <option value="">Select Course</option>
                                        <?php
                                        $course_query = mysqli_query($con, "SELECT DISTINCT course_code FROM student_info");
                                        while ($row = mysqli_fetch_assoc($course_query)) {
                                            $selected = (isset($_GET['course']) && $_GET['course'] == $row['course_code']) ? 'selected' : '';
                                            echo "<option value='{$row['course_code']}' $selected>{$row['course_code']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Semester</label>
                                    <select name="semester" class="form-select" required>
                                        <option value="">Select Semester</option>
                                        <?php
                                        for ($i = 1; $i <= 6; $i++) {
                                            $selected = (isset($_GET['semester']) && $_GET['semester'] == $i) ? 'selected' : '';
                                            echo "<option value='$i' $selected>$i</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">Show Students</button>
                            </form>
                        </div>

                        <!-- Students Table -->
                        <div class="col-md-9">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="mb-3">Student List</h5>
                                    <table class="table table-bordered table-striped align-middle">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Roll No</th>
                                                <th>Name</th>
                                                <th>Course</th>
                                                <th>Semester</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if (isset($_GET['course']) && isset($_GET['semester'])) {
                                                $course = $_GET['course'];
                                                $semester = $_GET['semester'];

                                                $query = "SELECT roll_no, CONCAT(first_name, ' ', last_name) AS name, course_code, semester 
                                                        FROM student_info 
                                                        WHERE course_code='$course' AND semester='$semester'";
                                                $result = mysqli_query($con, $query);

                                                if (mysqli_num_rows($result) > 0) {
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                        echo "<tr>
                                                                <td>{$row['roll_no']}</td>
                                                                <td>{$row['name']}</td>
                                                                <td>{$row['course_code']}</td>
                                                                <td>{$row['semester']}</td>
                                                                <td>
                                                                    <form method='post' style='display:inline;'>
                                                                        <input type='hidden' name='roll_no' value='{$row['roll_no']}'>
                                                                        <input type='hidden' name='current_sem' value='{$row['semester']}'>
                                                                        <button type='submit' name='promote_btn' class='btn btn-success btn-sm'>
                                                                            Promote
                                                                        </button>
                                                                    </form>
                                                                </td>
                                                            </tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='5' class='text-center text-muted'>No students found.</td></tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='5' class='text-center text-muted'>Select course and semester to view students.</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </main>
    </body>
</html>