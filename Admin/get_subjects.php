<?php
require_once "../connection/connection.php";

if (!isset($_GET['roll_no'], $_GET['course_code'], $_GET['semester'])) {
    echo "<tr><td colspan='4'>Invalid request</td></tr>"; exit;
}
$roll = mysqli_real_escape_string($con, $_GET['roll_no']);
$course = mysqli_real_escape_string($con, $_GET['course_code']);
$sem = (int)$_GET['semester'];

// If overall result summary exists for this student and semester -> show message
$chk = mysqli_query($con, "SELECT * FROM student_results WHERE roll_no='$roll' AND semester='$sem'");
if (mysqli_num_rows($chk) > 0) {
    echo "<tr><td colspan='4' class='text-center text-success p-3'><strong>Result already uploaded for $roll (Semester $sem)</strong></td></tr>";
    exit;
}

// Otherwise fetch subjects assigned to student (from student_subjects or course_subjects)
$q = mysqli_query($con, "SELECT cs.subject_code, cs.subject_name 
    FROM course_subjects cs 
    WHERE cs.course_code='$course' AND cs.semester='$sem'");

if (mysqli_num_rows($q) == 0) {
    echo "<tr><td colspan='4' class='text-center text-muted'>No subjects found</td></tr>";
    exit;
}

while ($row = mysqli_fetch_assoc($q)) {
    $code = htmlspecialchars($row['subject_code']);
    $name = htmlspecialchars($row['subject_name']);
    echo "<tr>
            <td>$code <input type='hidden' name='subject_code[]' value='$code'></td>
            <td class='text-start'>$name</td>
            <td><input type='number' name='internal_marks[]' min='0' max='100' class='form-control form-control-sm' required></td>
            <td><input type='number' name='external_marks[]' min='0' max='100' class='form-control form-control-sm' required></td>
          </tr>";
}
