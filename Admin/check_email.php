<?php  
	session_start();
	if (!$_SESSION["LoginAdmin"])
	{
		header('location:../login/login.php');
	}
		require_once "../connection/connection.php";
	
        if (isset($_POST['email'])) {
            $email = $_POST['email'];
        
            $query = "SELECT * FROM login WHERE email = '$email'";
            $result = mysqli_query($con, $query);
        
            if (mysqli_num_rows($result) > 0) {
                echo "exists"; // Email already taken
            } else {
                echo "available"; // Email is free
            }
        }
?>