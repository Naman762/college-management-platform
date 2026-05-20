<?php
require_once "../connection/connection.php";

if (isset($_GET['course_code']) && isset($_GET['semester'])) {
    $course = $_GET['course_code'];
    $semester = $_GET['semester'];

    $query = "SELECT roll_no, CONCAT(first_name, ' ', last_name) AS name 
              FROM student_info 
              WHERE course_code='$course' AND semester='$semester'";

    $result = mysqli_query($con, $query);

    echo "<option value=''>Select Student</option>";
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<option value='{$row['roll_no']}'>{$row['name']} ({$row['roll_no']})</option>";
        }
    } else {
        echo "<option value=''>No students found</option>";
    }
}
?>
