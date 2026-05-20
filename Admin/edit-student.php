<?php
session_start();
if (!$_SESSION["LoginAdmin"]) {
    header('location:../login/login.php');
    exit();
}
require_once "../connection/connection.php";

$roll_no = $_GET['roll_no'];
$query = "SELECT * FROM student_info WHERE roll_no='$roll_no'";
$result = mysqli_query($con, $query);
$student = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Student Details</title>
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
</head>
<body>
<?php include('../common/common-header.php'); ?>
<?php include('../common/admin-sidebar.php'); ?>

<main role="main" class="col-xl-10 col-lg-9 col-md-8 ml-sm-auto px-md-4 mb-2">
<div class="container mt-4">
    <h3 class="mb-3 text-primary">Edit Student Details</h3>
    <form method="POST" action="update-student.php" enctype="multipart/form-data">
        <input type="hidden" name="roll_no" value="<?php echo $student['roll_no']; ?>">

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control" value="<?php echo $student['first_name']; ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>Middle Name</label>
                <input type="text" name="middle_name" class="form-control" value="<?php echo $student['middle_name']; ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>Last Name</label>
                <input type="text" name="last_name" class="form-control" value="<?php echo $student['last_name']; ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Father Name</label>
                <input type="text" name="father_name" class="form-control" value="<?php echo $student['father_name']; ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo $student['email']; ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>Mobile No</label>
                <input type="text" name="mobile_no" class="form-control" value="<?php echo $student['mobile_no']; ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>State</label>
                <input type="text" name="state" class="form-control" value="<?php echo $student['state']; ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>Semester</label>
                <input type="text" name="semester" class="form-control" value="<?php echo $student['semester']; ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>Gender</label>
                <select name="gender" class="form-control">
                    <option <?php if($student['gender']=='Male') echo 'selected'; ?>>Male</option>
                    <option <?php if($student['gender']=='Female') echo 'selected'; ?>>Female</option>
                    <option <?php if($student['gender']=='Other') echo 'selected'; ?>>Other</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Course Code</label>
                <input type="text" name="course_code" class="form-control" value="<?php echo $student['course_code']; ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>Session</label>
                <input type="text" name="session" class="form-control" value="<?php echo $student['session']; ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>Category</label>
                <input type="text" name="category" class="form-control" value="<?php echo $student['category']; ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Date of Birth</label>
                <input type="date" name="dob" class="form-control" value="<?php echo $student['dob']; ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>Admission Date</label>
                <input type="date" name="admission_date" class="form-control" value="<?php echo $student['admission_date']; ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>Aadhar Number</label>
                <input type="text" name="aadhar" class="form-control" value="<?php echo $student['aadhar']; ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Permanent Address</label>
                <textarea name="permanent_address" class="form-control"><?php echo $student['permanent_address']; ?></textarea>
            </div>
            <div class="col-md-4 mb-3">
                <label>Current Address</label>
                <textarea name="current_address" class="form-control"><?php echo $student['current_address']; ?></textarea>
            </div>
            <div class="col-md-4 mb-3">
                <label>Place of Birth</label>
                <input type="text" name="place_of_birth" class="form-control" value="<?php echo $student['place_of_birth']; ?>">
            </div>
        </div>

        <hr>
        <h5>Academic Info</h5>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Matric Completion Date</label>
                <input type="date" name="matric_complition_date" class="form-control" value="<?php echo $student['matric_complition_date']; ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>Matric Percentage</label>
                <input type="text" name="matric_per" class="form-control" value="<?php echo $student['matric_per']; ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>Matric Certificate</label><br>
                <input type="file" name="matric_certificate" class="form-control">
                <?php if($student['matric_certificate']) echo "<a href='images/{$student['matric_certificate']}' target='_blank'>Current File</a>"; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>12th Completion Date</label>
                <input type="date" name="SS_complition_date" class="form-control" value="<?php echo $student['SS_complition_date']; ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>12th Percentage</label>
                <input type="text" name="SS_per" class="form-control" value="<?php echo $student['SS_per']; ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>12th Certificate</label><br>
                <input type="file" name="SS_certificate" class="form-control">
                <?php if($student['SS_certificate']) echo "<a href='images/{$student['SS_certificate']}' target='_blank'>Current File</a>"; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Transfer Certificate</label>
                <input type="file" name="transfer_cer" class="form-control">
                <?php if($student['transfer_cer']) echo "<a href='images/{$student['transfer_cer']}' target='_blank'>Current File</a>"; ?>
            </div>
            <div class="col-md-4 mb-3">
                <label>Migration</label>
                <input type="file" name="migration" class="form-control">
                <?php if($student['migration']) echo "<a href='images/{$student['migration']}' target='_blank'>Current File</a>"; ?>
            </div>
            <div class="col-md-4 mb-3">
                <label>ABC ID</label>
                <input type="text" name="abc_id" class="form-control" value="<?php echo $student['abc_id']; ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>ABC ID PDF</label>
                <input type="file" name="abc_img" class="form-control">
                <?php if($student['abc_img']) echo "<a href='images/{$student['abc_img']}' target='_blank'>Current File</a>"; ?>
            </div>
            <div class="col-md-4 mb-3">
                <label>Profile Image</label>
                <input type="file" name="profile_image" class="form-control">
                <?php if($student['profile_image']) echo "<a href='images/{$student['profile_image']}' target='_blank'>Current File</a>"; ?>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" name="update" class="btn btn-success">Update Details</button>
            <a href="display-student.php?roll_no=<?php echo $student['roll_no']; ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
</main>
</body>
</html>
