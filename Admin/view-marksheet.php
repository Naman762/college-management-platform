<?php
require_once "../connection/connection.php";

if (!isset($_GET['roll_no']) || !isset($_GET['semester'])) {
    die("Invalid request.");
}

$roll_no = $_GET['roll_no'];
$semester = $_GET['semester'];

// Fetch student info
$student_q = mysqli_query($con, "SELECT * FROM student_info WHERE roll_no='$roll_no'");
$student = mysqli_fetch_assoc($student_q);

// Fetch course details
$course_code = $student['course_code'];

// Fetch subject-wise marks
$marks_q = mysqli_query($con, "SELECT sm.*, cs.subject_name 
    FROM student_marks sm 
    JOIN course_subjects cs ON sm.subject_code = cs.subject_code 
    WHERE sm.roll_no='$roll_no' AND sm.semester='$semester' AND sm.course_code='$course_code'");

// Fetch overall result
$result_q = mysqli_query($con, "SELECT * FROM student_results 
    WHERE roll_no='$roll_no' AND semester='$semester'");
$result = mysqli_fetch_assoc($result_q);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Marksheet - <?php echo $student['first_name'] . " " . $student['last_name']; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Times New Roman', serif;
            background: #fff;
            margin: 40px;
        }
        .marksheet-container {
            border: 1px solid #000;
            padding: 30px 50px;
        }
        .marksheet-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .marksheet-header h3 {
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
            border: 1px solid #000 !important;
        }
        .info-row {
            margin-bottom: 8px;
            font-size: 15px;
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="marksheet-container">
    <div class="marksheet-header">
        <img src="../Images/mlsu-logo.png" alt="University Logo" height="80"><br>
        <h3>MOHANLAL SUKHADIA UNIVERSITY, UDAIPUR</h3>
        <h5>STATEMENT OF MARKS</h5>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="info-row"><b>Name:</b> <?php echo $student['first_name'] . " " . $student['last_name']; ?></div>
            <div class="info-row"><b>Father’s Name:</b> <?php echo $student['father_name']; ?></div>
            <div class="info-row"><b>Mother’s Name:</b> <?php echo $student['mother_name']; ?></div>
            <div class="info-row"><b>Medium:</b> <?php echo $student['medium']; ?></div>
        </div>
        <div class="col-md-6">
            <div class="info-row"><b>Roll No:</b> <?php echo $student['roll_no']; ?></div>
            <div class="info-row"><b>Enrollment No:</b> <?php echo $student['enrollment_no']; ?></div>
            <div class="info-row"><b>Course:</b> <?php echo $course_code; ?></div>
            <div class="info-row"><b>Semester:</b> <?php echo $semester; ?></div>
        </div>
    </div>

    <h6 class="text-center mb-2"><b><?php echo strtoupper($course_code); ?> (CBCS) SEMESTER <?php echo $semester; ?> Examination</b></h6>

    <table class="table table-bordered">
        <thead class="table-secondary">
            <tr>
                <th>Subject Code</th>
                <th>Course Name</th>
                <th>Max Marks</th>
                <th>External Marks</th>
                <th>Internal Marks</th>
                <th>Total Marks</th>
                <th>Grade Letter</th>
                <th>Credit Points</th>
                <th>Result</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $grand_total = 0;
            $max_total = 0;
            while ($row = mysqli_fetch_assoc($marks_q)) {
                $max_total += 100;
                $grand_total += $row['total_marks'];
                echo "
                <tr>
                    <td>{$row['subject_code']}</td>
                    <td>{$row['subject_name']}</td>
                    <td>100</td>
                    <td>{$row['external_marks']}</td>
                    <td>{$row['internal_marks']}</td>
                    <td>{$row['total_marks']}</td>
                    <td>{$row['grade_letter']}</td>
                    <td>{$row['credit_points']}</td>
                    <td>{$row['result']}</td>
                </tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="text-end mt-3">
        <b>SGPA:</b> <?php echo $result['sgpa']; ?> &nbsp;&nbsp;&nbsp; 
        <b>Result:</b> <?php echo $result['result_status']; ?>
    </div>

    <div class="footer mt-4">
        <div><b>College:</b> Adarsh College of Professional Studies, Abu Road</div>
        <div><small>G - Passed by Grace, F - Failed, Ab - Absent, * - Failed in Ext/Int course</small></div>
        <div><b>Result Declared on:</b> <?php echo date('d/m/Y'); ?></div>
        <div class="text-end"><b>Controller of Examination</b></div>
    </div>

    <?php if (!empty($result['marksheet_file'])): ?>
    <div class="mt-4">
        <b>Attached Internal Marksheet:</b>
        <a href="uploads/marksheets/<?php echo $result['marksheet_file']; ?>" target="_blank" class="btn btn-outline-primary btn-sm ms-2">
            View / Download
        </a>
    </div>
    <?php endif; ?>

    <div class="no-print mt-4 text-center">
        <button onclick="window.print()" class="btn btn-success">🖨️ Print Marksheet</button>
        <a href="admin_view_results.php" class="btn btn-secondary">← Back</a>
    </div>
</div>

</body>
</html>
