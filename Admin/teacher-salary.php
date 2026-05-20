<!---------------- Session starts form here ----------------------->
<?php  
	session_start();	
	if (!$_SESSION["LoginAdmin"])
	{
		header('location:../login/login.php');
	}
		require_once "../connection/connection.php";
	?>
<!---------------- Session Ends form here ------------------------>

<!--*********************** PHP code starts from here for data insertion into database ******************************* -->
<?php 
 
 	if (isset($_POST['btn_save'])) {

 		$teacher_id=$_POST["teacher_id"];

 		$basic_salary=$_POST["basic_salary"];

 		$medical_allowance=$_POST["medical_allowance"];
 		
 		$hr_allowance=$_POST["hr_allowance"];
 		
 		$scale=$_POST["scale"];
 		
 		$query="insert into teacher_salary_allowances(teacher_id,basic_salary,medical_allowance,hr_allowance,scale)values('$teacher_id','$basic_salary','$medical_allowance','$hr_allowance','$scale')";
 		$run=mysqli_query($con, $query);
 		if ($run) {
 			echo "Your Data has been submitted";
 		}
 		else {
 			echo "Your Data has not been submitted";
 		}
 	}



 	if (isset($_POST['btn_sub'])) {

 		$teacher_id=$_POST["teacher_id"];

 		$query="select * from teacher_salary_allowances where teacher_id='$teacher_id'";
 		$run=mysqli_query($con, $query);
 		while ($row=mysqli_fetch_array($run)) {
 			$total_amount=$row['basic_salary']+($row['basic_salary']*$row['medical_allowance']/100)+($row['basic_salary']*$row['hr_allowance']/100);
 			$query1="INSERT INTO teacher_salary_report(teacher_id, total_amount, status) VALUES ('$teacher_id','$total_amount','Paid')";
 			$run1=mysqli_query($con, $query1);

	 		if ($run1) {  ?>
	 			<script type="text/javascript">
	 				alert("Salary has been paid to I'd is : "+<?php echo $row['teacher_id'] ?>);
	 			</script>
	 		<?php }
	 		else { ?>
	 			<script type="text/javascript">
	 				alert("Salary has not been paid due to some errors");
	 			</script>
	 		<?php }
 	    }
 	}
?>
<!--*********************** PHP code end from here for data insertion into database ******************************* -->


<!doctype html>
<html lang="en">
	<head>
		<title>Admin - Teacher Salary</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
		<style>
    .calendar {
      width: 300px;
      margin: 20px auto;
      border: 1px solid #dee2e6;
      border-radius: 8px;
      overflow: hidden;
    }
    .calendar th {
      background-color: #f8f9fa;
      text-align: center;
      font-size: 14px;
    }
    .calendar td {
      height: 40px;
      width: 40px;
      text-align: center;
      vertical-align: middle;
      font-size: 14px;
    }
    .highlight {
      background-color: #ffc107 !important;
      color: #000;
      font-weight: bold;
    }
  </style>
	</head>
	<body>
		<?php include('../common/common-header.php') ?>
		<?php include('../common/admin-sidebar.php') ?>
		<main role="main" class="col-xl-10 col-lg-9 col-md-8 ml-sm-auto px-md-4 w-100">
			<div class="sub-main">
				<div class="text-center d-flex flex-wrap flex-md-nowrap pt-3 pb-2 mb-3 text-white admin-dashboard pl-3">
					<div class="d-flex">
						<h4 class="mr-5">Teacher Salary Management System </h4>
						<button type="button" class="btn btn-primary ml-5 mr-5" data-toggle="modal" data-target=".bd-example-modal-lg">Add Salary Scale</button>
						<button type="button" class="btn btn-primary ml-5" data-toggle="modal" data-target=".add_salary">Add Salary</button>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 ml-2">
						<section class=" mt-3">
							<div class="row">
								<div class="col-md-8"></div>
								<div class="col-md-3 ml-5 ">
									<div class="col-md-12 pt-3 ml-5">
										<!-- Large modal -->
										<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
										<div class="modal-dialog modal-lg">
												<div class="modal-content">
													<div class="modal-header bg-info text-white">
														<h4 class="modal-title text-center">Add Salary</h4>
													</div>
													<div class="modal-body">
														<form action="teacher-salary.php" method="post">
															<div class="row mt-3">
																<div class="col-md-6 pr-5">
																	<div class="form-group">
																		<label for="exampleInputEmail1">Teacher I'd: </label>
																		<select class="browser-default custom-select"  name="teacher_id" id="teacher" required>
																			<option value="">--Select Teacher--</option>
																			<?php
																			$sql = "SELECT teacher_id, first_name FROM teacher_info";
																			$result = mysqli_query($con,$sql);
																			while($row = $result->fetch_assoc()) {
																				echo '<option value="'.$row['teacher_id'].'">'.$row['teacher_id'].' - '.$row['first_name'].'</option>';
																			}
																			?>
																		</select>
																	</div>
																</div>
																<div class="col-md-6 pr-5">
																	<div class="form-group">
																		<label for="exampleInputEmail1">Basic Salary:</label>
																		<input type="text" name="basic_salary" class="form-control" placeholder="Enter Basic Salary">
																	</div>
																</div>
															</div>
															<div class="row">
																<div class="col-md-6">
																	<div class="form-group">
																		<label for="exampleInputEmail1">Medical Allowance: </label>
																		<input type="text" name="medical_allowance" class="form-control" placeholder="Enter Medical Allowance">
																	</div>
																</div>
																<div class="col-md-6">
																	<div class="formp">
																		<label for="exampleInputEmail1">House Rent Allowance: </label>
																		<input type="text" name="hr_allowance" class="form-control" placeholder="Enter HR Allowance">
																	</div>
																</div>
															</div>
															<div class="row">
																<div class="col-md-6">
																	<div class="formp">
																		<label for="exampleInputEmail1">Paid Scale: </label>
																		<input type="text" name="scale" class="form-control"placeholder="Enter Paid Scale">
																	</div>
																</div>
															</div>
															<div class="modal-footer">
																<input type="submit" class="btn btn-primary" name="btn_save" value="Save Data">
																<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
															</div> 	
														</form>
													</div>
												</div>
										</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-8"></div>
								<div class="col-md-3 ml-5 ">
									<div class="col-md-12 pt-3 ml-5">
										<!-- Large modal -->
										<div class="modal fade add_salary" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
										<div class="modal-dialog modal-lg">
												<div class="modal-content">
													<div class="modal-header bg-info text-white">
														<h4 class="modal-title text-center">Add Salary</h4>
													</div>
													<div class="modal-body">
														<form action="teacher-salary.php" method="post">
															<div class="row mt-3">
																<div class="col-md-12 pr-5">
																	<div class="form-group">
																		<label for="exampleInputEmail1">Teacher I'd: </label>
																		<input type="text" name="teacher_id" class="form-control" placeholder="Enter Teacher Id">
																	</div>
																</div>
															</div>
															<div class="modal-footer">
																<input type="submit" class="btn btn-primary" name="btn_sub" value="Save Data">
																<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
															</div> 	
														</form>
													</div>
												</div>
										</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-8">
									<div class="col-12">
										<table class="w-100 table-elements table-three-tr" cellpadding="3">
												<tr class="table-tr-head table-three text-white">
													<td colspan="9" class=" text-center text-white"><h4>Teachers Salary Scale</h4></td>
												</tr>
												<tr class="table-tr-head">
													<th>I'd</th>
													<th>Name</th>
													<th>Basic Salary</th>
												</tr>
												<?php  
													$query_1="SELECT ti.teacher_id, ti.first_name, ti.middle_name, ti.last_name, tsa.basic_salary
													FROM teacher_info ti
													INNER JOIN teacher_salary_allowances tsa 
													ON ti.teacher_id = tsa.teacher_id";
													$run_1=mysqli_query($con,$query_1);
													while ($row=mysqli_fetch_array($run_1)) { ?>
														<tr>
															<td><?php echo $row['teacher_id'] ?></td>
															<td><?php echo $row["first_name"]." ".$row["middle_name"]." ".$row["last_name"] ?></td>
															<td><?php echo $row['basic_salary'] ?></td>
														</tr>		
													<?php
													}
												?>
										</table>
									</div>
									<br>
									<div class="col-12">
									<table class="w-100 table-elements table-three-tr" cellpadding="3">
										<tr class="table-tr-head table-three text-white">
											<td colspan="9" class=" text-center text-white"><h4>All Teachers Salary Report</h4></td>
										</tr>
										<tr class="table-tr-head">
											<th>Salary Voucher</th>
											<th>I'd</th>
											<th>Name</th>
											<th>Basic Salary</th>
											<th>Paid Date</th>
											<th>Total Amount</th>
										</tr>
										<?php  
											$query="select tsr.teacher_id,ti.first_name,middle_name,last_name,salary_id,basic_salary,Date(paid_date) as paid_date,total_amount from teacher_salary_allowances tsa inner join teacher_salary_report tsr on tsa.teacher_id=tsr.teacher_id inner join teacher_info ti on ti.teacher_id=tsr.teacher_id";
											$run=mysqli_query($con,$query);
											while ($row=mysqli_fetch_array($run)) { ?>
												<tr>
													<td><?php echo $row['salary_id'] ?></td>
													<td><?php echo $row['teacher_id'] ?></td>
													<td><?php echo $row["first_name"]." ".$row["middle_name"]." ".$row["last_name"] ?></td>
													<td><?php echo $row['basic_salary'] ?></td>
													<td><?php echo $row['paid_date'] ?></td>
													<td><?php echo $row['total_amount'] ?></td>
												</tr>		
											<?php
											}
										?>
									</table>
								</div>
								</div>
								<div class="col">	
									<div class="col-4">
										<div class="container text-center">
											<!-- Year & Month Selectors -->
											<div class="mb-3 d-flex gap-3">
												<h4 id="calendar-title"></h4>
												<select id="month-selector" class="form-select w-auto d-inline-block"></select>
												<select id="year-selector" class="form-select w-auto d-inline-block"></select>
											</div>

											<div id="calendar-container"></div>
										</div>
									</div>
								</div>
							</div>
							<br>
							<div class="row">
								
							</div>				
						</section>
					</div>
				</div>
			</div>
		</main>
		<script type="text/javascript" src="../bootstrap/js/jquery.min.js"></script>
		<script type="text/javascript" src="../bootstrap/js/bootstrap.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
<script>
function generateCalendar(year, month) {
  const container = document.getElementById("calendar-container");
  container.innerHTML = ""; // clear old
  const monthNames = [
    "January","February","March","April","May","June",
    "July","August","September","October","November","December"
  ];

  document.getElementById("calendar-title").textContent = `${monthNames[month]} ${year}`;

  let firstDay = new Date(year, month, 1).getDay();
  let daysInMonth = new Date(year, month + 1, 0).getDate();

  let calendarHTML = `
    <div class="calendar">
      <table class="table table-bordered mb-0">
        <thead>
          <tr><th colspan="7">${monthNames[month]} ${year}</th></tr>
          <tr>
            <th>Su</th><th>Mo</th><th>Tu</th><th>We</th><th>Th</th><th>Fr</th><th>Sa</th>
          </tr>
        </thead>
        <tbody><tr>
  `;

  // Empty cells before 1st day
  for (let i = 0; i < firstDay; i++) {
    calendarHTML += "<td></td>";
  }

  // Days
  for (let day = 1; day <= daysInMonth; day++) {
    let classes = "";
    if (day === 20 || day === 21 || day === 22) {
      classes = "highlight";
    }

    calendarHTML += `<td class="${classes}">${day}</td>`;

    // New row on Saturday
    if ((day + firstDay) % 7 === 0) {
      calendarHTML += "</tr><tr>";
    }
  }

  calendarHTML += "</tr></tbody></table></div>";
  container.innerHTML = calendarHTML;
}

function setupSelectors() {
  const monthSelector = document.getElementById("month-selector");
  const yearSelector = document.getElementById("year-selector");
  const now = new Date();
  const currentMonth = now.getMonth();
  const currentYear = now.getFullYear();

  // Months
  const monthNames = [
    "January","February","March","April","May","June",
    "July","August","September","October","November","December"
  ];
  monthNames.forEach((m, i) => {
    let opt = document.createElement("option");
    opt.value = i;
    opt.textContent = m;
    if (i === currentMonth) opt.selected = true;
    monthSelector.appendChild(opt);
  });

  // Years (current year ± 5)
  for (let y = currentYear - 5; y <= currentYear + 5; y++) {
    let opt = document.createElement("option");
    opt.value = y;
    opt.textContent = y;
    if (y === currentYear) opt.selected = true;
    yearSelector.appendChild(opt);
  }

  // Events
  monthSelector.addEventListener("change", () => {
    generateCalendar(parseInt(yearSelector.value), parseInt(monthSelector.value));
  });
  yearSelector.addEventListener("change", () => {
    generateCalendar(parseInt(yearSelector.value), parseInt(monthSelector.value));
  });

  // Initial calendar
  generateCalendar(currentYear, currentMonth);
}

setupSelectors();
</script>

	</body>
</html>

