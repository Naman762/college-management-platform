<?php
session_start();
if (!$_SESSION["LoginAdmin"]) {
    header('location:../login/login.php');
}
require_once "../connection/connection.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Student Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include('../common/common-header.php') ?>
<?php include('../common/admin-sidebar.php') ?>

<main role="main" class="col-xl-10 col-lg-9 col-md-8 ml-sm-auto px-md-4 mb-2 w-100">
    <div class="sub-main">
        <div class="bar-margin text-center d-flex flex-wrap flex-md-nowrap pt-3 pb-2 mb-3 text-white admin-dashboard pl-3">
			<h4>View Student Results</h4>
		</div>
    </div>
    <div class="container mt-4">
        <!-- Filter Form -->
        <form method="get" class="row mb-4">
            <div class="col-md-4">
                <label class="form-label">Course</label>
                <select name="course_code" class="form-select" required>
                    <option value="">Select Course</option>
                    <?php
                    $course_q = mysqli_query($con, "SELECT DISTINCT course_code FROM student_results");
                    while ($row = mysqli_fetch_assoc($course_q)) {
                        $selected = (isset($_GET['course_code']) && $_GET['course_code'] == $row['course_code']) ? 'selected' : '';
                        echo "<option value='{$row['course_code']}' $selected>{$row['course_code']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-4">
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
            <div class="col-md-4 align-self-end">
                <button class="btn btn-primary w-100">Filter</button>
            </div>
        </form>

        <!-- Results Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-bordered table-striped align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>Roll No</th>
                            <th>Name</th>
                            <th>Course</th>
                            <th>Semester</th>
                            <th>SGPA</th>
                            <th>Result Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (isset($_GET['course_code']) && isset($_GET['semester'])) {
                            $course = $_GET['course_code'];
                            $sem = $_GET['semester'];

                            $query = "
                                SELECT sr.*, si.first_name, si.last_name 
                                FROM student_results sr 
                                JOIN student_info si ON sr.roll_no = si.roll_no
                                WHERE sr.course_code='$course' AND sr.semester='$sem'
                                ORDER BY sr.roll_no ASC";
                            $res = mysqli_query($con, $query);

                            if (mysqli_num_rows($res) > 0) {
                                while ($row = mysqli_fetch_assoc($res)) {
                                    echo "<tr>
                                            <td>{$row['roll_no']}</td>
                                            <td>{$row['first_name']} {$row['last_name']}</td>
                                            <td>{$row['course_code']}</td>
                                            <td>{$row['semester']}</td>
                                            <td>{$row['sgpa']}</td>
                                            <td><span class='badge bg-".($row['result_status']=='Pass'?'success':'danger')."'>{$row['result_status']}</span></td>
                                            <td>
                                                <a href='view-marksheet.php?roll_no={$row['roll_no']}&course_code={$row['course_code']}&semester={$row['semester']}'
                                                   class='btn btn-sm btn-info'>
                                                   View Marksheet
                                                </a>
                                            </td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-muted text-center'>No results found.</td></tr>";
                            }
                        } else {
                            $query1 = "
                                SELECT sr.*, si.first_name, si.last_name 
                                FROM student_results sr 
                                JOIN student_info si ON sr.roll_no = si.roll_no
                                ORDER BY sr.roll_no ASC";
                            $res1 = mysqli_query($con, $query1);

                            if (mysqli_num_rows($res1) > 0) {
                                while ($row = mysqli_fetch_assoc($res1)) {
                                    echo "<tr>
                                            <td>{$row['roll_no']}</td>
                                            <td>{$row['first_name']} {$row['last_name']}</td>
                                            <td>{$row['course_code']}</td>
                                            <td>{$row['semester']}</td>
                                            <td>{$row['sgpa']}</td>
                                            <td><span class='badge bg-".($row['result_status']=='Pass'?'success':'danger')."'>{$row['result_status']}</span></td>
                                            <td>
                                                <a href='view-marksheet.php?roll_no={$row['roll_no']}&course_code={$row['course_code']}&semester={$row['semester']}'
                                                   class='btn btn-sm btn-info'>
                                                   View Marksheet
                                                </a>
                                            </td>
                                          </tr>";
                                }} 
                                echo "<tr><td colspan='7' class='text-muted text-center'>Select seperate course and semester to view results.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!--student result history-->
</main>

</body>
</html>
