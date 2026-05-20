<?php  
session_start();
if (!$_SESSION["LoginAdmin"]) {
    header('location:../login/login.php');
}
require_once "../connection/connection.php";

// ---------- Save Marks Logic ----------
// Save Result
if (isset($_POST['save_result'])) {
    $roll_no = $_POST['roll_no'];
    $course_code = $_POST['course_code'];
    $semester = $_POST['semester'];

    //Prevent duplicate result upload
    $check_result = mysqli_query($con, "SELECT * FROM student_results WHERE roll_no='$roll_no' AND course_code='$course_code' AND semester='$semester'");
    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>alert('Result for this student (Semester $semester) already exists!');
        window.location.href = 'admin_result_entry.php';
        </script>";
        exit;
    }

    //File upload handling
    $marksheet_file = null;
    if (isset($_FILES['marksheet_file']) && $_FILES['marksheet_file']['error'] === 0) {
        $file_name = $roll_no . "_sem" . $semester . "_" . time() . "." . pathinfo($_FILES['marksheet_file']['name'], PATHINFO_EXTENSION);
        $target_dir = "uploads/marksheets/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        move_uploaded_file($_FILES['marksheet_file']['tmp_name'], $target_dir . $file_name);
        $marksheet_file = $file_name;
    }

    //Grading and insertion
    $total_credit_points = 0;
    $total_credits = 0;
    $fail_flag = false;

    if (!empty($_POST['subject_code'])) {
        foreach ($_POST['subject_code'] as $i => $subject_code) {
            $internal = (int)$_POST['internal_marks'][$i];
            $external = (int)$_POST['external_marks'][$i];
            $total = $internal + $external;

            if ($total >= 90) { $grade = "O"; $credit = 10; }
            elseif ($total >= 80) { $grade = "A+"; $credit = 9; }
            elseif ($total >= 70) { $grade = "A"; $credit = 8; }
            elseif ($total >= 60) { $grade = "B+"; $credit = 7; }
            elseif ($total >= 50) { $grade = "B"; $credit = 6; }
            elseif ($total >= 40) { $grade = "C"; $credit = 5; }
            else { $grade = "F"; $credit = 0; $fail_flag = true; }

            $result = ($grade == "F") ? "F" : "P";

            mysqli_query($con, "INSERT INTO student_marks 
                (roll_no, course_code, semester, subject_code, internal_marks, external_marks, total_marks, grade_letter, credit_points, result)
                VALUES ('$roll_no', '$course_code', '$semester', '$subject_code', '$internal', '$external', '$total', '$grade', '$credit', '$result')");

            $total_credit_points += $credit;
            $total_credits += 10; // assume 10 per subject
        }
    } else {
        echo "<script>alert('No subjects found for this course/semester!');</script>";
        exit;
    }

    // SGPA calculation
    $sgpa = ($total_credits > 0) ? round(($total_credit_points / ($total_credits / 10)), 2) : 0.0;
    $status = $fail_flag ? "Fail" : "Pass";

    mysqli_query($con, "INSERT INTO student_results (roll_no, course_code, semester, sgpa, result_status, marksheet_file)
                        VALUES ('$roll_no', '$course_code', '$semester', '$sgpa', '$status', '$marksheet_file')");

    //Success + reload fix
    echo "<script>
        alert('Result for $roll_no (Semester $semester) saved successfully!');
        window.location.href='admin_result_entry.php';
    </script>";
}
?>

<!doctype html>
<html lang="en">
<head>
    <title>Admin - Result Entry</title>
    <meta charset="utf-8">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fc; }
        .card { border-radius: 15px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
        .form-label { font-weight: 600; }
        .subject-row:hover { background-color: #f1f3f9; }
        .btn-modern {
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: #fff; border: none;
        }
        .btn-modern:hover {
            background: linear-gradient(135deg, #2e59d9, #224abe);
            color: #fff;
        }
    </style>
</head>
<body>
<?php include('../common/common-header.php') ?>
<?php include('../common/admin-sidebar.php') ?>  

<main role="main" class="col-xl-10 col-lg-9 col-md-8 ml-sm-auto px-md-4 mb-2 w-100 dash-style">
    <div class="sub-main">
        <div class="bar-margin text-center d-flex flex-wrap flex-md-nowrap pt-3 pb-2 mb-3 text-white admin-dashboard pl-3">
			<h4>Student Result Entry & Declaration</h4>
		</div>
    </div>
    <div class="container py-4">
        <div class="text-center mb-4">
            <h3 class="fw-bold" style="color:black;">Student Result Entry & Declaration</h3>
        </div>

        <!-- Filter Form -->
        <div class="card p-4 mb-4">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Select Course</label>
                    <select name="course_code" class="form-select" required>
                        <option value="">Select</option>
                        <?php
                        $res = mysqli_query($con, "SELECT DISTINCT course_code FROM student_info");
                        while ($r = mysqli_fetch_assoc($res)) {
                            $sel = (isset($_GET['course_code']) && $_GET['course_code']==$r['course_code'])?'selected':'';
                            echo "<option value='{$r['course_code']}' $sel>{$r['course_code']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Semester</label>
                    <select name="semester" class="form-select" required>
                        <option value="">Select</option>
                        <?php for($i=1;$i<=6;$i++){ 
                            $sel=(isset($_GET['semester']) && $_GET['semester']==$i)?'selected':''; 
                            echo "<option value='$i' $sel>$i</option>";
                        } ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Select Student</label>
                    <select name="roll_no" id="student_select" class="form-select" required>
                        <option value="">Select Course First</option>
                    </select>
                </div>

                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-modern px-4 mt-3" style="color:white;">Load Subjects</button>
                </div>
            </form>
        </div>

        <!-- Marks Entry Table -->
       <!-- Marks Entry Table -->
       <?php
if (isset($_GET['roll_no']) && isset($_GET['semester']) && isset($_GET['course_code'])) {
    $roll_no = $_GET['roll_no'];
    $semester = $_GET['semester'];
    $course_code = $_GET['course_code'];

    $subq = mysqli_query($con, "SELECT subject_code, subject_name FROM course_subjects 
                                WHERE course_code='$course_code' AND semester='$semester'");

    if (mysqli_num_rows($subq) > 0) {
        echo '
        <form method="post" class="card p-4" enctype="multipart/form-data">
            <input type="hidden" name="roll_no" value="'.$roll_no.'">
            <input type="hidden" name="course_code" value="'.$course_code.'">
            <input type="hidden" name="semester" value="'.$semester.'">

            <h5 class="mb-3 text-primary">Enter Marks for '.$roll_no.' (Semester '.$semester.')</h5>
            <table class="table table-bordered table-hover text-center align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Internal Marks</th>
                        <th>External Marks</th>
                    </tr>
                </thead>
                <tbody>';
        
        while ($row = mysqli_fetch_assoc($subq)) {
            echo '
                <tr>
                    <td>'.$row['subject_code'].' 
                        <input type="hidden" name="subject_code[]" value="'.$row['subject_code'].'">
                    </td>
                    <td>'.$row['subject_name'].'</td>
                    <td><input type="number" name="internal_marks[]" class="form-control" min="0" max="40" required></td>
                    <td><input type="number" name="external_marks[]" class="form-control" min="0" max="60" required></td>
                </tr>';
        }

        echo '
                </tbody>
            </table>

            <div class="mb-3">
                <label class="form-label">Upload University Online Marksheet (Optional)</label>
                <input type="file" name="marksheet_file" accept=".pdf,.jpg,.jpeg,.png" class="form-control">
            </div>

            <button type="submit" name="save_result" class="btn btn-success">Save Result</button>
        </form>';
    }
}
?>


    </div>
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    //When course changes — reset everything
    $("select[name='course_code']").on("change", function() {
        $("#semester_select").html("<option value=''>Select Semester</option>");
        $("#student_select").html("<option value=''>Select Course First</option>");
        $("#subject_table_body").html("<tr><td colspan='4' class='text-center text-muted'>Select course and semester</td></tr>");
    });

    //When semester changes — load students for that course+semester
    $("select[name='semester']").on("change", function() {
        var courseCode = $("select[name='course_code']").val();
        var semester = $(this).val();

        if (courseCode && semester) {
            $.get("get_students.php", { course_code: courseCode, semester: semester }, function(data) {
                $("#student_select").html(data);
                $("#subject_table_body").html("<tr><td colspan='4' class='text-center text-muted'>Select student to load subjects</td></tr>");
            });
        } else {
            $("#student_select").html("<option value=''>Select Course and Semester</option>");
        }
    });

    // When student changes — load subjects directly
    $("#student_select").on("change", function() {
    var rollNo = $(this).val();
    var courseCode = $("select[name='course_code']").val();
    var semester = $("select[name='semester']").val();

    if (rollNo && courseCode && semester) {
        $.get("get_subjects.php", { roll_no: rollNo, course_code: courseCode, semester: semester }, function(data) {
            $("#subject_table_body").html(data);
        });
    }
});


    //Reset form after result saved (called after save)
    function resetFormAfterSave() {
        $("#student_select").html("<option value=''>Select Student</option>");
        $("#subject_table_body").html("<tr><td colspan='4' class='text-center text-muted'>Select course and semester</td></tr>");
    }

    // Optional: if you want to auto-reset after saving
    $(document).on("resultSaved", function() {
        resetFormAfterSave();
    });
});

</script>

</body>
</html>
