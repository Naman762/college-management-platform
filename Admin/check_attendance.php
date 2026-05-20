<?php
session_start();
if (!$_SESSION["LoginAdmin"])
{
    header('location:../login/login.php');
}
    require_once "../connection/connection.php";

if(isset($_POST['attendance_date']) && isset($_POST['course_code']) && isset($_POST['semester'])){
    $date = $_POST['attendance_date'];
    $course_code = $_POST['course_code'];
     $semester = $_POST['semester'];
    

    $sql = "SELECT COUNT(*) as total FROM student_attendance 
            WHERE course_code='$course_code' 
              AND semester='$semester' 
              AND attendance_date='$date' LIMIT 1";
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($result);

    if($row['total'] > 0){
        echo "exists";
    } else {
        echo "ok";
    }
}
?>