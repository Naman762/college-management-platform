<?php
session_start();
if (!$_SESSION["LoginAdmin"]) {
    header('location:../login/login.php');
}
require_once "../connection/connection.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Result & Performance Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        label,h1,h2,h3{
            color:black!important;
        }
    </style>
</head>
<body>
<?php include('../common/common-header.php') ?>
<?php include('../common/admin-sidebar.php') ?>

<main role="main" class="col-xl-10 col-lg-9 col-md-8 ml-sm-auto px-md-4 mb-4 w-100">
    <div class="sub-main">
        <div class="bar-margin text-center d-flex flex-wrap flex-md-nowrap pt-3 pb-2 mb-3 text-white admin-dashboard pl-3">
			<h4>Student Result Summary</h4>
		</div>
    </div>
    <div class="container-fluid mt-4">
        <ul class="nav nav-tabs" id="analyticsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="student-tab" data-bs-toggle="tab" data-bs-target="#student" type="button" role="tab">Student Progress</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="semester-tab" data-bs-toggle="tab" data-bs-target="#semester" type="button" role="tab">Semester Performance</button>
            </li>
        </ul>

        <div class="tab-content mt-4">
            <!-- STUDENT PROGRESS TAB -->
            <div class="tab-pane fade show active" id="student" role="tabpanel">
                <form method="get" class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Select Student</label>
                        <select name="roll_no" class="form-select" required>
                            <option value="">Select Student</option>
                            <?php
                            $res = mysqli_query($con, "SELECT roll_no, first_name, last_name FROM student_info ORDER BY roll_no ASC");
                            while ($row = mysqli_fetch_assoc($res)) {
                                $sel = (isset($_GET['roll_no']) && $_GET['roll_no'] == $row['roll_no']) ? 'selected' : '';
                                echo "<option value='{$row['roll_no']}' $sel>{$row['roll_no']} - {$row['first_name']} {$row['last_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button class="btn btn-primary w-100">View</button>
                    </div>
                </form>

                <?php
                if (isset($_GET['roll_no'])) {
                    $roll_no = $_GET['roll_no'];

                    $student = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM student_info WHERE roll_no='$roll_no'"));
                    $results = mysqli_query($con, "SELECT * FROM student_results WHERE roll_no='$roll_no' ORDER BY semester ASC");

                    $semesters = [];
                    $sgpa = [];
                    $total = 0;
                    $count = 0;

                    echo "<div class='card shadow-sm p-4'>";
                    echo "<h5 class='text-primary mb-3'>{$student['first_name']} {$student['last_name']} - {$student['course_code']}</h5>";
                    echo "<table class='table table-bordered text-center align-middle'>
                            <thead class='table-dark'>
                                <tr>
                                    <th>Semester</th>
                                    <th>SGPA</th>
                                    <th>Status</th>
                                    <th>Marksheet</th>
                                </tr>
                            </thead>
                            <tbody>";
                    while ($row = mysqli_fetch_assoc($results)) {
                        $semesters[] = "Sem " . $row['semester'];
                        $sgpa[] = $row['sgpa'];
                        $total += $row['sgpa'];
                        $count++;

                        echo "<tr>
                                <td>{$row['semester']}</td>
                                <td>{$row['sgpa']}</td>
                                <td><span class='badge bg-" . ($row['result_status'] == 'Pass' ? 'success' : 'danger') . "'>{$row['result_status']}</span></td>
                                <td>";
                        echo $row['marksheet_file']
                            ? "<a href='uploads/marksheets/{$row['marksheet_file']}' target='_blank' class='btn btn-info btn-sm'>View</a>"
                            : "<span class='text-muted'>N/A</span>";
                        echo "</td></tr>";
                    }
                    echo "</tbody></table></div>";

                    if ($count > 0) {
                        $cgpa = round(array_sum($sgpa) / count($sgpa), 2);
                        $performance = ($cgpa >= 9) ? "Excellent" :
                                       (($cgpa >= 8) ? "Very Good" :
                                       (($cgpa >= 7) ? "Good" :
                                       (($cgpa >= 6) ? "Average" : "Poor")));

                        echo "<div class='alert alert-info mt-3'>
                                <strong>Overall CGPA:</strong> $cgpa &nbsp;&nbsp;|&nbsp;&nbsp;
                                <strong>Performance:</strong> $performance
                              </div>";
                    }
                ?>
                <div class="card mt-4 p-4 shadow-sm">
                    <h5 class="text-center text-secondary mb-3">SGPA Progress Chart</h5>
                    <canvas id="sgpaChart"></canvas>
                </div>
                <script>
                    new Chart(document.getElementById('sgpaChart'), {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode($semesters); ?>,
                            datasets: [{
                                label: 'SGPA',
                                data: <?php echo json_encode($sgpa); ?>,
                                borderColor: '#007bff',
                                backgroundColor: 'rgba(0,123,255,0.1)',
                                fill: true,
                                tension: 0.3
                            }]
                        },
                        options: { scales: { y: { beginAtZero: true, max: 10 } } }
                    });
                </script>
                <?php } ?>
            </div>

            <!-- SEMESTER PERFORMANCE TAB -->
            <div class="tab-pane fade" id="semester" role="tabpanel">
                <form method="get" class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Course Code</label>
                        <select name="course_code" class="form-select" required>
                            <option value="">Select Course</option>
                            <?php
                            $courses = mysqli_query($con, "SELECT DISTINCT course_code FROM student_info");
                            while ($c = mysqli_fetch_assoc($courses)) {
                                $sel = (isset($_GET['course_code']) && $_GET['course_code'] == $c['course_code']) ? 'selected' : '';
                                echo "<option value='{$c['course_code']}' $sel>{$c['course_code']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Semester</label>
                        <select name="semester" class="form-select" required>
                            <option value="">Select</option>
                            <?php for ($i=1; $i<=6; $i++) {
                                $sel = (isset($_GET['semester']) && $_GET['semester']==$i)?'selected':'';
                                echo "<option value='$i' $sel>$i</option>";
                            } ?>
                        </select>
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button class="btn btn-success w-100">View</button>
                    </div>
                </form>

                <?php
                if (isset($_GET['course_code']) && isset($_GET['semester'])) {
                    $course_code = $_GET['course_code'];
                    $semester = $_GET['semester'];

                    $res = mysqli_query($con, "SELECT * FROM student_results WHERE course_code='$course_code' AND semester='$semester'");
                    $total = mysqli_num_rows($res);
                    $pass = mysqli_num_rows(mysqli_query($con, "SELECT * FROM student_results WHERE course_code='$course_code' AND semester='$semester' AND result_status='Pass'"));
                    $fail = $total - $pass;
                    $avg_q = mysqli_fetch_assoc(mysqli_query($con, "SELECT AVG(sgpa) AS avg_sgpa FROM student_results WHERE course_code='$course_code' AND semester='$semester'"));
                    $avg_sgpa = round($avg_q['avg_sgpa'], 2);

                    echo "<div class='row'>
                            <div class='col-md-4'>
                                <div class='card text-center shadow-sm p-3'>
                                    <h5>Total Students</h5>
                                    <h3>$total</h3>
                                </div>
                            </div>
                            <div class='col-md-4'>
                                <div class='card text-center shadow-sm p-3'>
                                    <h5>Passed</h5>
                                    <h3 class='text-success'>$pass</h3>
                                </div>
                            </div>
                            <div class='col-md-4'>
                                <div class='card text-center shadow-sm p-3'>
                                    <h5>Failed</h5>
                                    <h3 class='text-danger'>$fail</h3>
                                </div>
                            </div>
                          </div>";

                    echo "<div class='alert alert-info mt-3 text-center'>
                            <strong>Average SGPA:</strong> $avg_sgpa
                          </div>";

                    // Subject-wise performance
                    $subq = mysqli_query($con, "SELECT cs.subject_name, AVG(sm.total_marks) AS avg_marks
                    FROM student_marks sm
                    JOIN course_subjects cs ON sm.subject_code = cs.subject_code
                    WHERE sm.course_code='$course_code' AND sm.semester='$semester'
                    GROUP BY sm.subject_code ORDER BY avg_marks ASC");
                                            /*
                                            SELECT cs.subject_name, AVG(sm.total_marks) AS avg_marks
FROM student_marks sm
JOIN course_subjects cs ON sm.subject_code = cs.subject_code
WHERE sm.course_code='$course_code' AND sm.semester='$semester'
GROUP BY sm.subject_code ORDER BY avg_marks ASC

                                            */
                    $subs = [];
                    $avg_marks = [];
                    while ($s = mysqli_fetch_assoc($subq)) {
                        $subs[] = $s['subject_name'];
                        $avg_marks[] = round($s['avg_marks'], 2);
                    }

                    $lowest_subject = $subs[0] ?? "N/A";

                    echo "<div class='alert alert-warning text-center mt-3'>
                            <strong>Lowest Average Subject:</strong> $lowest_subject
                          </div>";
                ?>
                <div class="card mt-4 p-4 shadow-sm">
                    <h5 class="text-center text-secondary mb-3">Subject Average Marks</h5>
                    <canvas id="subjectChart"></canvas>
                </div>
                <script>
                    new Chart(document.getElementById('subjectChart'), {
                        type: 'bar',
                        data: {
                            labels: <?php echo json_encode($subs); ?>,
                            datasets: [{
                                label: 'Average Marks',
                                data: <?php echo json_encode($avg_marks); ?>,
                                backgroundColor: 'rgba(40,167,69,0.6)',
                                borderColor: '#28a745',
                                borderWidth: 1
                            }]
                        },
                        options: { scales: { y: { beginAtZero: true, max: 100 } } }
                    });
                </script>
                <?php } ?>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Preserve active tab after page reload
    document.addEventListener("DOMContentLoaded", function() {
        const activeTab = sessionStorage.getItem("activeTab");
        if (activeTab) {
            const tabTrigger = document.querySelector(`button[data-bs-target="${activeTab}"]`);
            if (tabTrigger) {
                new bootstrap.Tab(tabTrigger).show();
            }
        }

        // Listen for tab changes and save current tab
        const tabButtons = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabButtons.forEach(btn => {
            btn.addEventListener('shown.bs.tab', function(e) {
                sessionStorage.setItem("activeTab", e.target.getAttribute("data-bs-target"));
            });
        });
    });
</script>

</body>
</html>
