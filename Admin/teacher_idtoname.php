<?php
require_once "connection.php";
header('Content-Type: application/json');

if(isset($_POST['teacher_id'])){
    $teacher_id = intval($_POST['teacher_id']);
    $query = "SELECT first_name, middle_name, last_name 
              FROM teacher_info 
              WHERE teacher_id = $teacher_id";
    $result = mysqli_query($con, $query);

    if(mysqli_num_rows($result) > 0){
        $row = mysqli_fetch_assoc($result);
        $fullName = trim($row['first_name'] . " " . $row['middle_name'] . " " . $row['last_name']);
		echo  json_encode(['success' => true, 'teacher_name' => $fullName]);
       
    } else {
        echo json_encode(['success' => false]);
    }
	}
