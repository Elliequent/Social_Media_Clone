<?php  

require 'Config/config.php';
require 'Includes/Form_Handlers/register_handler.php';
require 'Includes/Form_Handlers/login_handler.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Welcome to Swirlfeed!</title>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="Assets/CSS/register_style.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
	<script src= "Assets/JavaScript/register.js"></script>
</head>
<body>

	<?php 
		// When registering the screen goes back to the sign in button - but if error in signup the screen will remain until error fixed
		if(isset($_POST['register_button'])) {
			echo '
			<script>
	
			$(document).ready(function() {
				$("#first").hide();
				$("#second").show();
			});
	
			</script>
	
			';
		}
	
	?>

	<div class="wrapper">
		<div class="login_box">

			<div class="login_header">
				<!-- Header with logo -->
				<h1>Swirlfeed</h1>
				Login or sign up below
				
			</div>
			<br>
			<div id="first">

				<form action="register.php" method="POST">

					<!-- Login section -->
					<input type="email" name="log_email" placeholder="Email Address" autocomplete="off" value="<?php 
					if(isset($_SESSION['log_email'])) {		// Stores email entry in session aata
						echo $_SESSION['log_email'];
					} 
					?>" required >

					<br>
					<input type="password" name="log_password" autocomplete="off" placeholder="Password"
					><br>
					<input type="submit" name="login_button" value="Login">
					<br>
					<a href="#" id="signup" class="signup">Need an account? Register here</a> <br>

					<?php
					if(in_array("The Email or Password you have provided does not match our records", $error_array)) echo "The Email or Password you have provided does not match our records";
					?>

				</form>

			</div>

			<div id="second">

				<form action="register.php" method="POST">
					<!-- Create new account section -->
					<input type="text" name="reg_fname" autocomplete="off" placeholder="First Name" value="<?php 
					if(isset($_SESSION['reg_fname'])) {
						echo $_SESSION['reg_fname'];
					} 
					?>" required>
					<br>
					<?php if(in_array("Your first name must be between 2 and 25 characters<br>", $error_array)) echo "Your first name must be between 2 and 25 characters<br>"; ?>
					<!-- IF this error is in the error array then display this message -->
					
					<input type="text" name="reg_lname" autocomplete="off" placeholder="Last Name" value="<?php 
					if(isset($_SESSION['reg_lname'])) {
						echo $_SESSION['reg_lname'];
					} 
					?>" required>
					<br>
					<?php if(in_array("Your last name must be between 2 and 25 characters<br>", $error_array)) echo "Your last name must be between 2 and 25 characters<br>"; ?>
					<!-- IF this error is in the error array then display this message -->

					<input type="email" name="reg_email" autocomplete="off" placeholder="Email" value="<?php 
					if(isset($_SESSION['reg_email'])) {
						echo $_SESSION['reg_email'];
					} 
					?>" required>
					<br>

					<input type="email" name="reg_email2" autocomplete="off" placeholder="Confirm Email" value="<?php 
					if(isset($_SESSION['reg_email2'])) {
						echo $_SESSION['reg_email2'];
					} 
					?>" required>
					<br>
					<?php if(in_array("This email address is already registered<br>", $error_array)) echo "This email address is already registered<br>"; 
					else if(in_array("Invalid email format<br>", $error_array)) echo "Invalid email format<br>";
					else if(in_array("Email addresses do not match<br>", $error_array)) echo "Email addresses do not match<br>"; ?>
					<!-- IF this error is in the error array then display this message -->

					<input type="password" name="reg_password" placeholder="Password" required>
					<br>
					<input type="password" name="reg_password2" placeholder="Confirm Password" required>
					<br>
					<?php if(in_array("Your passwords do not match<br>", $error_array)) echo "Your passwords do not match<br>"; 
					else if(in_array("Your password can only contain english characters or numbers<br>", $error_array)) echo "Your password can only contain english characters or numbers<br>";
					else if(in_array("Your password must be betwen 5 and 30 characters<br>", $error_array)) echo "Your password must be betwen 5 and 30 characters<br>"; ?>
					<!-- IF this error is in the error array then display this message in order - Will not display all, only in order -->

					<input type="submit" name="register_button" value="Register">
					<br>

					<?php if(in_array("<span style='color: #14C800;'>Account Created</span><br>", $error_array)) echo "<span style='color: #14C800;'>Account Created</span><br>"; ?>
					<a href="#" id="signin" class="signin">Already have an account? Login here</a> <br>

				</form>

			</div>
		</div>
	</div>

</body>
</html>