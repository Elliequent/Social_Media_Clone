<?php

//Declaring variables to prevent errors
$fname = "";        //First name
$lname = "";        //Last name
$em = "";           //email
$em2 = "";          //email 2
$password = "";     //password
$password2 = "";    //password 2
$date = "";         //Sign up date 
$error_array = array();     //Holds error messages - Errors occur and they are pushed into this array

if(isset($_POST['register_button'])){		        // IF register button is pressed

	//Registration form values

	//First name
	$fname = strip_tags($_POST['reg_fname']);       //Remove html tags (Security - prevents HTML Injections)
	$fname = str_replace(' ', '', $fname);          //remove spaces
	$fname = ucfirst(strtolower($fname));           //Uppercase first letter (Lower case everything first and then uppercase first letter)
	$_SESSION['reg_fname'] = $fname;                //Stores first name into session variable

	//Last name
	$lname = strip_tags($_POST['reg_lname']);       
	$lname = str_replace(' ', '', $lname);          
	$lname = ucfirst(strtolower($lname));           
	$_SESSION['reg_lname'] = $lname;               

	//email
	$em = strip_tags($_POST['reg_email']);          
	$em = str_replace(' ', '', $em);                
	$em = ucfirst(strtolower($em)); 
	$_SESSION['reg_email'] = $em; 

	//email 2
	$em2 = strip_tags($_POST['reg_email2']); 
	$em2 = str_replace(' ', '', $em2); 
	$em2 = ucfirst(strtolower($em2)); 
	$_SESSION['reg_email2'] = $em2; 

	//Password
	$password = strip_tags($_POST['reg_password']); 
	$password2 = strip_tags($_POST['reg_password2']);

	$date = date("Y-m-d"); // Sets current date

	if($em == $em2) {
		//Check if email is in valid format 
		if(filter_var($em, FILTER_VALIDATE_EMAIL)) {
			// Stores email in vaild format
			$em = filter_var($em, FILTER_VALIDATE_EMAIL);

			//Check if email already exists 
			$e_check = mysqli_query($con, "SELECT email FROM users WHERE email='$em'");
			//Count the number of rows returned
			$num_rows = mysqli_num_rows($e_check);
			// If email already in use
			if($num_rows > 0) {
				array_push($error_array, "This email address is already registered<br>");	// Enters into error array
			}

		}
		else {
			array_push($error_array, "Invalid email format<br>");	// Enters into error array
		}


	}
	else {
		array_push($error_array, "Email addresses do not match<br>");	// Enters into error array

	}	// End of $em == $em2 IF STATEMENT


	if(strlen($fname) > 25 || strlen($fname) < 2) {	// Checks if first name is between 2 and 25 characters long
		array_push($error_array, "Your first name must be between 2 and 25 characters<br>");	// Enters into error array
	}

	if(strlen($lname) > 25 || strlen($lname) < 2) {	// Checks if last name is between 2 and 25 characters long
		array_push($error_array,  "Your last name must be between 2 and 25 characters<br>");	// Enters into error array
	}

	if($password != $password2) {	// Checks if password 1 and password 2 match
		array_push($error_array,  "Your passwords do not match<br>");	// Enters into error array
	}
	else {
		if(preg_match('/[^A-Za-z0-9]/', $password)) {	// Checks passwords only contain English lettering and numbers
			array_push($error_array, "Your password can only contain english characters or numbers<br>"); // Enters into error array
		}
	}

	if(strlen($password > 30 || strlen($password) < 5)) {	// Checks if password is between 5 and 30 characters long
		array_push($error_array, "Your password must be betwen 5 and 30 characters<br>");	// Enters into error array
	}


	if(empty($error_array)) {	// If there are no errors in the error array then user info is generated
		$password = md5($password); //Encrypt password before sending to database - Calculates the MDhash of the password

		//Generate username by concatenating first name and last name
		$username = strtolower($fname . "_" . $lname);	// First name _ last name in lowercase
		$check_username_query = mysqli_query($con, "SELECT username FROM users WHERE username='$username'");	// Checks if username already exits within the database


		$i = 0; 
		//if username exists add number to username
		while(mysqli_num_rows($check_username_query) != 0) {	// If username does not exits in database
			$i++; //Add 1 to i
			$username = $username . "_" . $i; 
			$check_username_query = mysqli_query($con, "SELECT username FROM users WHERE username='$username'");
		}	// Adds a number at the end of the username depending on how many of the existing usernames exist = ie ian_fraser_1

		//Profile picture assignment
		$rand = rand(1, 16); //Random number between 1 and 16

		if($rand == 1)
			$profile_pic = "Assets/Images/Profile_Pictures/Defaults/head_deep_blue.png";	// Sets user profile picture to random colour
		else if($rand == 2)
			$profile_pic = "Assets/Images/Profile_Pictures/Defaults/head_emerald.png";
		else if($rand == 3)
			$profile_pic = "Assets/Images/Profile_Pictures/Defaults/head_alizarin.png";
		else if($rand == 4)
			$profile_pic = "Assets/Images/Profile_Pictures/Defaults/head_belize_hole.png";
		else if($rand == 5)
			$profile_pic = "Assets/Images/Profile_Pictures/Defaults/head_carrot.png";
		else if($rand == 6)
			$profile_pic = "Assets/Images/Profile_Pictures/Defaults/head_green_sea.png";
		else if($rand == 7)
			$profile_pic = "Assets/Images/Profile_Pictures/Defaults/head_nephritis.png";
		else if($rand == 8)
			$profile_pic = "Assets/Images/Profile_Pictures/Defaults/head_pete_river.png";
		else if($rand == 9)
			$profile_pic = "Assets/Images/Profile_Pictures/Defaults/head_pomegranate.png";
		else if($rand == 10)
			$profile_pic = "Assets/Images/Profile_Pictures/Defaults/head_pumpkin.png";
		else if($rand == 11)
			$profile_pic = "Assets/Images/Profile_Pictures/Defaults/head_red.png";
		else if($rand == 12)
			$profile_pic = "Assets/Images/Profile_Pictures/Defaults/head_sun_flower.png";
		else if($rand == 13)
			$profile_pic = "Assets/Images/Profile_Pictures/Defaults/head_turqoise.png";
		else if($rand == 14)
			$profile_pic = "Assets/Images/Profile_Pictures/Defaults/head_wet_asphalt.png";
		else if($rand == 15)
			$profile_pic = "Assets/Images/Profile_Pictures/Defaults/head_wisteria.png";
		else if($rand == 16)
			$profile_pic = "Assets/Images/Profile_Pictures/Defaults/head_amethyst.png";
			

		// SQL entry - creating a new user with the inputed details
		$query = mysqli_query($con, "INSERT INTO users VALUES ('', '$fname', '$lname', '$username', '$em', '$password', '$date', '$profile_pic', '0', '0', 'no', ',')");
		// Successful account creation message - error messages displayed if errors in error array
		array_push($error_array, "<span style='color: #14C800;'>Account Created</span><br>");

		//Clear session variables - When session ends
		$_SESSION['reg_fname'] = "";
		$_SESSION['reg_lname'] = "";
		$_SESSION['reg_email'] = "";
		$_SESSION['reg_email2'] = "";

	}	// End of empty($error_array) IF STATEMENT

}	    // End of isset($_POST['register_button']) IF STATEMENT

?>