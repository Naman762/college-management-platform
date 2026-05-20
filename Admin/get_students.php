<?php
require_once "../connection/connection.php";
if (!isset($_GET['course_code']) || !isset($_GET['semester'])) {
    echo "<option value=''>Invalid</option>"; exit;
}
$course = mysqli_real_escape_string($con, $_GET['course_code']);
$sem = (int)$_GET['semester'];

$q = mysqli_query($con, "SELECT roll_no, CONCAT(first_name,' ',last_name) AS name FROM student_info WHERE course_code='$course' AND semester='$sem' ORDER BY first_name");
if (mysqli_num_rows($q) > 0) {
    echo "<option value=''>Select Student</option>";
    while ($r = mysqli_fetch_assoc($q)) {
        echo "<option value='{$r['roll_no']}'>{$r['roll_no']} - {$r['name']}</option>";
    }
} else {
    echo "<option value=''>No students found</option>";
}
