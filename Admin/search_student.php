<?php
session_start();
require_once "../connection/connection.php";

if (isset($_POST['btnSearch'])) {
    $search = mysqli_real_escape_string($con, $_POST['search']);

    // Query to match roll_no, name (first/middle/last), semester, or course
    $query = "
        SELECT * FROM student_info
        WHERE 
            roll_no LIKE '%$search%' OR
            first_name LIKE '%$search%' OR
            middle_name LIKE '%$search%' OR
            last_name LIKE '%$search%' OR
            semester LIKE '%$search%' OR
            course_code LIKE '%$search%'
    ";

    $run = mysqli_query($con, $query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Search Student</title>
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
</head>
<body>
<?php include('../common/common-header.php'); ?>
<?php include('../common/admin-sidebar.php'); ?>

<main role="main" class="col-xl-10 col-lg-9 col-md-8 ml-sm-auto px-md-4 mb-2">
    <div class="container-fluid mt-4">
        <h4>Search Results for: <span class="text-primary"><?php echo htmlspecialchars($search ?? ''); ?></span></h4>
        <table class="table table-bordered table-striped mt-3">
            <thead class="thead-dark">
                <tr>
                    <th>Roll No</th>
                    <th>Name</th>
                    <th>Course</th>
                    <th>Semester</th>
                    <th>Mobile No</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if (isset($run) && mysqli_num_rows($run) > 0) {
                while ($row = mysqli_fetch_assoc($run)) {
                    echo "<tr>
                        <td>{$row['roll_no']}</td>
                        <td>{$row['first_name']} {$row['middle_name']} {$row['last_name']}</td>
                        <td>{$row['course_code']}</td>
                        <td>{$row['semester']}</td>
                        <td>{$row['mobile_no']}</td>
                        <td>{$row['email']}</td>
                        <td>
                            <a href='display-student.php?roll_no={$row['roll_no']}' class='btn btn-info btn-sm'>View</a>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='7' class='text-center text-danger'>No matching records found.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
