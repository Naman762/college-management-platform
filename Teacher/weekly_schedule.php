<!---------------- Session starts form here ----------------------->
<?php  
	session_start();
	if (!$_SESSION["LoginTeacher"])
	{
		header('location:../login/login.php');
	}
		require_once "../connection/connection.php";
	

			// teacher_id and email
		$teacher_id = $_SESSION["teacher_id"];
		$teacher_email = $_SESSION["teacher_email"];

   

    //teacher weekly schedule code start here
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') 
    {    
        $subject_id = $_POST['course_id'];
        echo $subject_id;
        $week_start = $_POST['week_start'];
        $week_end   = $_POST['week_end'];
    
        // Validate that teacher submits on Saturday
      /*  date_default_timezone_set('Asia/Kolkata');
         $today = date('N'); // 1=Mon, 7=Sun
        if ($today != 6) { 
            $_SESSION['error'] = "You can only add weekly schedule on Saturday.";
            header("Location: weekly_schedule.php"); 
            exit;

         }*/
    
        // // Validate selected week is upcoming Mon-Sat
        $next_monday = date('Y-m-d', strtotime('next monday'));
         $next_saturday = date('Y-m-d', strtotime('next saturday'));
    
         if ($week_start != $next_monday || $week_end != $next_saturday) {
            $_SESSION['error'] = "You can only add Monday to Saturday schedule.";
            header("Location: weekly_schedule.php"); 
            exit;
         }
        
           //checking if the schedule is already uploaded
            
            $check = $con->prepare("SELECT schedule_id FROM weekly_schedule 
            WHERE teacher_id = ? AND subject_id = ? 
            AND week_start = ? AND week_end = ?");
            $check->bind_param("isss", $teacher_id, $subject_id, $week_start, $week_end);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
            $_SESSION['error'] = "You already uploaded a schedule for this subject in this week.";
            header("Location: weekly_schedule.php");
            exit;
            }
            $check->close();
        
            //end code for checking if the schedule is already uploaded

        // Insert into weekly_schedule
        $stmt = $con->prepare("INSERT INTO weekly_schedule (teacher_id, subject_id, week_start, week_end) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $teacher_id, $subject_id, $week_start, $week_end);
        $stmt->execute();
        $schedule_id = $stmt->insert_id;
    
        // Loop through topics
        foreach ($_POST['topics'] as $i => $topic_name) {
            $class_date   = $_POST['class_date'][$i]; // new date field
            $youtube_link = $_POST['links'][$i];
            $notes_desc = $_POST['notes_desc'][$i];

            // Handle file upload
            $file_name = null;
            if (!empty($_FILES['notes_file']['name'][$i])) {
                $upload_dir = "uploads/";
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    
                $file_tmp = $_FILES['notes_file']['tmp_name'][$i];
                $file_name = time() . "_" . basename($_FILES['notes_file']['name'][$i]);
                move_uploaded_file($file_tmp, $upload_dir . $file_name);
            }
    
            // Insert into schedule_topics
            $stmt2 = $con->prepare("INSERT INTO schedule_topics (schedule_id, class_date, topic_name, youtube_link, notes_description, notes_file) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param("isssss", $schedule_id, $class_date, $topic_name, $youtube_link, $notes_desc, $file_name);
            $stmt2->execute();
        }
    
         //  If validation passes, proceed with saving schedule
    $_SESSION['success'] = "Weekly schedule added successfully!";
    header("Location: weekly_schedule.php"); 
    exit;
    }
    
    //weekly schedule ends

?>


<!---------------- Session Ends form here ------------------------>
<!doctype html>
<html lang="en">
	<head>
		<title>Teachers - Teacher Information</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

		<style>
			.teacher-design {
				align:center;
			}
			</style>
            
	</head>
	<body>
		<?php include('../common/common-header.php') ?>
		<?php include('../common/teacher-sidebar.php') ?>

        <!-- session for alert and success message -->
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
    <?php
    if (isset($_SESSION['error'])) {
        echo '<div class="toast align-items-center text-bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                  <div class="toast-body">'
                    . $_SESSION['error'] .
                  '</div>
                  <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
              </div>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo '<div class="toast align-items-center text-bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                  <div class="toast-body">'
                    . $_SESSION['success'] .
                  '</div>
                  <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
              </div>';
        unset($_SESSION['success']);
    }
    ?>
</div>

   <!-- session end for alert and success -->
        <main role="main" class="col-xl-10 col-lg-9 col-md-8 ml-sm-auto px-md-4 main-background mb-2 w-100">
			<div class="sub-main">
				<div class="text-center d-flex flex-wrap flex-md-nowrap pt-3 pb-2 mb-4 text-white admin-dashboard pl-3">
					<h4 class="">Update Your Weekly Report Infromation <?php echo $teacher_id;?>
                    </h4>
				</div>
				<div class="row">
					<div class="col-lg-12 col-md-12">
                   
                            <div class="container-fluid mt-4">
                            <div class="card shadow p-4">
                                <h4 class="mb-3">Weekly Schedule</h4>
                                <form method="post" action="weekly_schedule.php" enctype="multipart/form-data">
                                
                                <!-- Week Start -->
                                <div class="row">
                                <div class="col-4">
                                    <div class="mb-3">
                                        <label class="form-label">Week Start</label>
                                        <input type="date" name="week_start" id="week_start" class="form-control" required>
                                    </div>
                                </div>

                                <!-- Week End -->
                                <div class="col-4">
                                    <div class="mb-3">
                                        <label class="form-label">Week End</label>
                                        <input type="date" name="week_end" id="week_end" class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-4">
                                    <div class="mb-3">
                                        <label class="form-label">Select Subject:</label>
                                        <select class="browser-default custom-select form-select" name="course_id" required>
                                        <?php
                                            // fetch teacher's assigned courses
                                        
                                            $query="SELECT tc.subject_code, s.subject_name
                                            FROM teacher_courses tc
                                            JOIN course_subjects s ON tc.subject_code = s.subject_code
                                            WHERE tc.teacher_id= '$teacher_id'";
                                            $res=mysqli_query($con,$query);
                                            while ($row = mysqli_fetch_assoc($res)) {
                                                echo "<option value='{$row['subject_code']}'>{$row['subject_name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                </div>

                                <div class="container-fluid mt-4">
                                        
                                        <table id="scheduleTable" class="table table-bordered table-striped align-middle text-center">
                                            <thead class="table-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>Date</th>
                                                <th>Topic</th>
                                                <th>YouTube Link</th>
                                                <th>Notes Description</th>
                                                <th>Notes Upload</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td class="row-id">1</td>
                                                <td><input type="date" name="class_date[]" class="form-control class-date" required></td>
                                                <td><input type="text" class="form-control" name="topics[]" required></td>
                                                <td><input type="text" class="form-control" name="links[]"></td>
                                                <td><textarea class="form-control" name="notes_desc[]"></textarea></td>
                                                <td><input type="file" class="form-control" name="notes_file[]"></td>
                                                <td>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">❌</button>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>

                                        <div class="d-flex gap-3">
                                            <button type="button" class="btn btn-success" onclick="addRow()">➕ Add Row</button>
                                            <button type="submit" class="btn btn-primary">💾 Save Weekly Schedule</button>
                                        </div>
                                </div>

                                </form>
                            </div>
                            </div>

                    </div>
                </div>
            </div>
        </main>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

        <script>

let rowCount = 1;

function addRow() {
    rowCount++;
    let table = document.getElementById("scheduleTable").getElementsByTagName('tbody')[0];
    let newRow = table.insertRow();

    newRow.innerHTML = `
      <td class="row-id">${rowCount}</td>
      <td><input type="date" name="class_date[]" class="form-control" required></td>
      <td><input type="text" name="topics[]" class="form-control" required></td>
      <td><input type="text" name="links[]" class="form-control"></td>
      <td><textarea name="notes_desc[]" class="form-control"></textarea></td>
      <td><input type="file" name="notes_file[]" class="form-control"></td>
      <td><button type="button" onclick="removeRow(this)">❌</button></td>
    `;
}

function removeRow(btn) {
    let row = btn.closest("tr");
    row.remove();
    // reset IDs
    let ids = document.querySelectorAll("#scheduleTable .row-id");
    ids.forEach((td, i) => td.textContent = i+1);
    rowCount = ids.length;
}


const today = new Date().toISOString().split("T")[0];
    document.getElementById("week_start").setAttribute("min", today);

    document.getElementById("week_start").addEventListener("change", function () {
        let startDate = new Date(this.value);

        if (isNaN(startDate)) return; // invalid date, skip

        // Add 6 days
        let endDate = new Date(startDate);
        endDate.setDate(startDate.getDate() + 5);

        // Format YYYY-MM-DD
        let formattedEnd = endDate.toISOString().split("T")[0];

        // Set week_end automatically
        document.getElementById("week_end").value = formattedEnd;
    });


    document.addEventListener("DOMContentLoaded", function () {
        let toastElList = [].slice.call(document.querySelectorAll('.toast'))
        let toastList = toastElList.map(function (toastEl) {
            let option = { delay: 4000 }; // 4 sec
            let bsToast = new bootstrap.Toast(toastEl, option);
            bsToast.show();
            return bsToast;
        })
    });


</script>

    </body>
    </html>