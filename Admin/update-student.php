<?php
session_start();
if (!$_SESSION["LoginAdmin"]) {
    header('location:../login/login.php');
    exit();
}
require_once "../connection/connection.php";

if (isset($_POST['update'])) {
    $roll_no = $_POST['roll_no'];
    $fields = [
        'first_name','middle_name','last_name','father_name','email','mobile_no',
        'state','semester','gender','course_code','session','category',
        'dob','admission_date','aadhar','permanent_address','current_address',
        'place_of_birth','matric_complition_date','matric_per','SS_complition_date',
        'SS_per','abc_id'
    ];

    $update_parts = [];
    foreach ($fields as $f) {
        $val = mysqli_real_escape_string($con, $_POST[$f]);
        $update_parts[] = "$f='$val'";
    }

    // Handle file uploads
    $file_fields = ['matric_certificate','SS_certificate','transfer_cer','migration','abc_img','profile_image'];
    foreach ($file_fields as $file) {
        if (!empty($_FILES[$file]['name'])) {
            $filename = time().'_'.basename($_FILES[$file]['name']);
            $target = "images/".$filename;
            move_uploaded_file($_FILES[$file]['tmp_name'], $target);
            $update_parts[] = "$file='$filename'";
        }
    }

    $query = "UPDATE student_info SET ".implode(", ", $update_parts)." WHERE roll_no='$roll_no'";
    if (mysqli_query($con, $query)) {
        echo "<script>alert('Student details updated successfully'); 
              window.location='display-student.php?roll_no=$roll_no';</script>";
    } else {
        echo "<script>alert('Update failed: ".mysqli_error($con)."');</script>";
    }
}
?>
