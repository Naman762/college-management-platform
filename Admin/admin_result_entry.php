<?php
session_start();
if (!$_SESSION["LoginAdmin"]) header('location:../login/login.php');
require_once "../connection/connection.php";

/* -------------------- POST: Save Result -------------------- */
if (isset($_POST['save_result'])) {
    $roll_no = mysqli_real_escape_string($con, $_POST['roll_no']);
    $course_code = mysqli_real_escape_string($con, $_POST['course_code']);
    $semester = (int)$_POST['semester'];

    // Prevent duplicate summary row
    $check = mysqli_query($con, "SELECT * FROM student_results WHERE roll_no='$roll_no' AND course_code='$course_code' AND semester='$semester'");
    if (mysqli_num_rows($check) > 0) {
        // Optionally you can update -- here we block duplicates
        echo "<script>alert('Result already exists for $roll_no semester $semester. Use update option.'); window.location='admin_result_entry.php';</script>";
        exit;
    }

    // Handle file upload
    $marksheet_file = null;
    if (!empty($_FILES['marksheet_file']['name']) && $_FILES['marksheet_file']['error'] === 0) {
        $ext = pathinfo($_FILES['marksheet_file']['name'], PATHINFO_EXTENSION);
        $file_name = $roll_no . "_sem" . $semester . "_" . time() . "." . $ext;
        $target_dir = __DIR__ . "/uploads/marksheets/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        move_uploaded_file($_FILES['marksheet_file']['tmp_name'], $target_dir . $file_name);
        $marksheet_file = $file_name;
    }

    // Validate arrays present
    if (empty($_POST['subject_code']) || !is_array($_POST['subject_code'])) {
        echo "<script>alert('No subjects submitted.'); window.location='admin_result_entry.php';</script>";
        exit;
    }

    $total_credit_points = 0;
    $total_credits = 0;
    $fail_flag = false;

    // Insert per-subject marks into student_marks
    foreach ($_POST['subject_code'] as $i => $subject_code) {
        $subject_code = mysqli_real_escape_string($con, $subject_code);
        $internal = (int)$_POST['internal_marks'][$i];
        $external = (int)$_POST['external_marks'][$i];
        $total = $internal + $external;

        // Grade logic (adjust to your rules)
        if ($total >= 90) { $grade = "O"; $gp = 10; }
        elseif ($total >= 80) { $grade = "A+"; $gp = 9; }
        elseif ($total >= 70) { $grade = "A"; $gp = 8; }
        elseif ($total >= 60) { $grade = "B+"; $gp = 7; }
        elseif ($total >= 50) { $grade = "B"; $gp = 6; }
        elseif ($total >= 40) { $grade = "C"; $gp = 5; }
        else { $grade = "F"; $gp = 0; $fail_flag = true; }

        $res_flag = ($grade === "F") ? 'F' : 'P';
        // credit scheme: you can pull from subjects table if different; here assume 4 credits per subject and credit points=gp*credits
        $credits = 4;
        $credit_points = $gp * $credits;

        mysqli_query($con, "INSERT INTO student_marks 
            (roll_no, course_code, semester, subject_code, internal_marks, external_marks, total_marks, grade_letter, credit_points, result)
            VALUES
            ('$roll_no','$course_code','$semester','$subject_code','$internal','$external','$total','$grade','$credit_points','$res_flag')");

        $total_credit_points += $credit_points;
        $total_credits += $credits;
    }

    // Calculate SGPA (sum credit_points / sum credits) mapped to 10 scale
    $sgpa = ($total_credits > 0) ? round(($total_credit_points / $total_credits), 2) : 0.00;
    $status = $fail_flag ? 'Fail' : 'Pass';

    // Insert semester summary with optional file name
    $marksheet_sqlfile = $marksheet_file ? mysqli_real_escape_string($con, $marksheet_file) : NULL;
    mysqli_query($con, "INSERT INTO student_results (roll_no, course_code, semester, sgpa, result_status, marksheet_file, declared_on)
        VALUES ('$roll_no','$course_code','$semester','$sgpa','$status', " . ($marksheet_sqlfile ? "'$marksheet_sqlfile'" : "NULL") . ", NOW())");

    // Done — redirect to clear form and give immediate feedback
    echo "<script>alert('Result saved for $roll_no (Semester $semester).'); window.location='admin_result_entry.php';</script>";
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin - Result Entry</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<style>
.container-card { margin: 30px auto; max-width:1100px; }
.card-heading { display:flex; justify-content:space-between; align-items:center; }
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
    <div class="card shadow-sm glass-card">
        <div class="card-body">
            <div class="card-heading mb-3">
                <h4>Result Entry & Upload</h4>
            </div>

            <!-- Filter Row -->
            <form id="filterForm" class="row g-3 mb-3" method="get" action="">
                <div class="col-md-4">
                    <label class="form-label">Select Course</label>
                    <select name="course_code" id="course_code" class="form-select" required>
                        <option value="">Select Course</option>
                        <?php
                        $rs = mysqli_query($con, "SELECT DISTINCT course_code FROM student_info");
                        while ($r = mysqli_fetch_assoc($rs)) {
                            $sel = (isset($_GET['course_code']) && $_GET['course_code']==$r['course_code']) ? 'selected' : '';
                            echo "<option value='{$r['course_code']}' $sel>{$r['course_code']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Semester</label>
                    <select name="semester" id="semester" class="form-select" required>
                        <option value="">Select Semester</option>
                        <?php for ($i=1;$i<=6;$i++) {
                            $sel = (isset($_GET['semester']) && $_GET['semester']==$i) ? 'selected' : '';
                            echo "<option value='$i' $sel>$i</option>";
                        } ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Select Student</label>
                    <select name="roll_no" id="student_select" class="form-select" required>
                        <option value="">Select Course & Semester</option>
                        <?php
                        // optionally pre-load when GET present
                        if (isset($_GET['course_code'], $_GET['semester'], $_GET['roll_no'])) {
                            $c = mysqli_real_escape_string($con,$_GET['course_code']);
                            $s = (int)$_GET['semester'];
                            $st = mysqli_query($con, "SELECT roll_no, CONCAT(first_name,' ',last_name) AS name FROM student_info WHERE course_code='$c' AND semester='$s'");
                            while ($r = mysqli_fetch_assoc($st)) {
                                $sel = ($_GET['roll_no']==$r['roll_no']) ? 'selected' : '';
                                echo "<option value='{$r['roll_no']}' $sel>{$r['roll_no']} - {$r['name']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="col-12 text-center mt-2">
                    <button id="loadSubjectsBtn" type="button" class="btn btn-primary">Load Subjects</button>
                </div>
            </form>

            <!-- Marks Form (dynamically filled) -->
            <form id="marksForm" method="post" enctype="multipart/form-data">
                <input type="hidden" name="roll_no" id="h_roll_no" value="<?php echo isset($_GET['roll_no'])?htmlspecialchars($_GET['roll_no']):''; ?>">
                <input type="hidden" name="course_code" id="h_course_code" value="<?php echo isset($_GET['course_code'])?htmlspecialchars($_GET['course_code']):''; ?>">
                <input type="hidden" name="semester" id="h_semester" value="<?php echo isset($_GET['semester'])?htmlspecialchars($_GET['semester']):''; ?>">

                <div class="table-responsive">
                    <table class="table table-bordered" id="subject_table">
                        <thead class="table-primary text-center">
                            <tr>
                                <th>Subject Code</th>
                                <th>Subject Name</th>
                                <th style="width:140px;">Internal Marks</th>
                                <th style="width:140px;">External Marks</th>
                            </tr>
                        </thead>
                        <tbody id="subject_table_body">
                            <tr><td colspan="4" class="text-center text-muted p-3">Select course, semester and student then click "Load Subjects"</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Attach Internal Marksheet (PDF/JPG/PNG) (optional)</label>
                        <input type="file" name="marksheet_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    <div class="col-md-6 d-flex align-items-end justify-content-end">
                        <button type="submit" name="save_result" class="btn btn-success">Save Result</button>
                        &nbsp;<button type="button" id="resetBtn" class="btn btn-secondary">Reset</button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</main>

<script>
$(document).ready(function(){
    // Load students when course or semester change (only if both selected)
    function loadStudents() {
        var course = $("#course_code").val();
        var sem = $("#semester").val();
        $("#subject_table_body").html("<tr><td colspan='4' class='text-center'>Loading students...</td></tr>");
        if (course && sem) {
            $.get("get_students.php", { course_code: course, semester: sem }, function(data) {
                $("#student_select").html(data);
                $("#subject_table_body").html("<tr><td colspan='4' class='text-center text-muted'>Select student and click 'Load Subjects'</td></tr>");
            });
        } else {
            $("#student_select").html("<option value=''>Select Course & Semester</option>");
            $("#subject_table_body").html("<tr><td colspan='4' class='text-center text-muted'>Select course & semester</td></tr>");
        }
    }

    $("#course_code, #semester").on("change", loadStudents);

    // Load subjects for selected student
    $("#loadSubjectsBtn").on("click", function(){
        var course = $("#course_code").val();
        var sem = $("#semester").val();
        var roll = $("#student_select").val();
        if (!course || !sem || !roll) {
            alert("Please select Course, Semester and Student.");
            return;
        }
        // store into hidden inputs
        $("#h_roll_no").val(roll);
        $("#h_course_code").val(course);
        $("#h_semester").val(sem);

        $("#subject_table_body").html("<tr><td colspan='4' class='text-center'>Loading subjects...</td></tr>");
        $.get("get_subjects.php", { roll_no: roll, course_code: course, semester: sem }, function(data) {
            $("#subject_table_body").html(data);
        }).fail(function(){ 
            $("#subject_table_body").html("<tr><td colspan='4' class='text-center text-danger'>Error loading subjects</td></tr>");
        });
    });

    // Reset button
    $("#resetBtn").on("click", function(){
        $("#filterForm")[0].reset();
        $("#marksForm")[0].reset();
        $("#student_select").html("<option value=''>Select Course & Semester</option>");
        $("#subject_table_body").html("<tr><td colspan='4' class='text-center text-muted'>Select course, semester and student then click Load Subjects</td></tr>");
    });
});
</script>
</body>
</html>
