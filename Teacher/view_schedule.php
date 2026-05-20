<?php  
	session_start();
	if (!$_SESSION["LoginTeacher"])
	{
		header('location:../login/login.php');
	}
		require_once "../connection/connection.php";
	
        $teacher_id = $_SESSION["teacher_id"];
		$teacher_email = $_SESSION["teacher_email"];

            // Get from session/login
        $year = isset($_GET['year']) ? $_GET['year'] : date("Y");
        $subject_id = isset($_GET['subject_id']) ? $_GET['subject_id'] : "";
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : "";
        $end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : "";
        
        // Fetch teacher's subjects
        $subjects = mysqli_query($con, "SELECT DISTINCT subject_id FROM weekly_schedule WHERE teacher_id='$teacher_id'");
        
        // Build query
        $query = "SELECT ws.schedule_id, st.topic_id, ws.subject_id, st.topic_name, st.youtube_link, st.notes_description, st.notes_file, st.class_date
                  FROM weekly_schedule ws
                  JOIN schedule_topics st ON ws.schedule_id = st.schedule_id
                  WHERE ws.teacher_id = '$teacher_id'
                  AND YEAR(st.class_date) = '$year'";
        
        if($subject_id != ""){
            $query .= " AND ws.subject_id = '$subject_id'";
        }
        
        if(!empty($start_date) && !empty($end_date)){
            $query .= " AND st.class_date BETWEEN '$start_date' AND '$end_date'";
        }
        
        $query .= " ORDER BY st.class_date ASC";
        $result = mysqli_query($con, $query);

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

        <main role="main" class="col-xl-10 col-lg-9 col-md-8 ml-sm-auto px-md-4 main-background mb-2 w-100">
			<div class="sub-main">
				<div class="text-center d-flex flex-wrap flex-md-nowrap pt-3 pb-2 mb-4 text-white admin-dashboard pl-3">
					<h4 class="">View Schedule Information <?php echo $teacher_id;?></h4>
     	    	</div>
            </div>
            
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <!--view schdule form-->
                        <form method="GET" class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Subject</label>
                                <select name="subject_id" class="form-select">
                                    <option value="">All Subjects</option>
                                    <?php while($sub = mysqli_fetch_assoc($subjects)){ ?>
                                        <option value="<?= $sub['subject_id']; ?>" <?= ($subject_id == $sub['subject_id']) ? "selected" : "" ?>>
                                            <?= $sub['subject_id']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Year</label>
                                <input type="number" name="year" class="form-control" value="<?= $year ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
                            </div>
                            <div class="col-md-2 align-self-end">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </form>

                        <!-- Schedule Table -->
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Srno</th>
                                    <th>Subject</th>
                                    <th>Class-Date</th>
                                    <th>Topic</th>
                                    <th>Links</th>
                                    <th>Notes</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $sr=1;
                                if(mysqli_num_rows($result) > 0){
                                    while($row = mysqli_fetch_assoc($result)){ ?>
                                        <tr>
                                            <td><?= $sr++; ?></td>
                                            <td><?= $row['subject_id']; ?></td>
                                            <td><?= $row['class_date']; ?></td>
                                            <td><?= $row['topic_name']; ?></td>
                                            <td>
                                                <?php if(!empty($row['youtube_link'])){ ?>
                                                    <a href="<?= $row['youtube_link']; ?>" target="_blank">Link</a>
                                                <?php } else { echo "----"; } ?>
                                            </td>
                                            <td>
                                                <?php if(!empty($row['notes_file'])){ ?>
                                                    <a href="uploads/notes/<?= $row['notes_file']; ?>" target="_blank"><?= $row['notes_file']; ?></a>
                                                <?php } else { echo "No Notes"; } ?>
                                            </td>
                                            <td>
                                                <a href="edit_topic.php?id=<?= $row['topic_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="delete_topic.php?id=<?= $row['topic_id']; ?>" class="btn btn-sm btn-danger">DEL</a>
                                            </td>
                                        </tr>
                                <?php } } else { ?>
                                    <tr><td colspan="7" class="text-center">No schedule found</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <!--view schedule form code end-->
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>