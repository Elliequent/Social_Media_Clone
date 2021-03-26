<?php

if(isset($_POST['login_button'])) {             // IF Login button is pressed

    $email =filter_var($_POST['log_email'], FILTER_SANITIZE_EMAIL);     // Store email variable if in the correct format

    $_SESSION['log_email'] = $email;                                    // Stores email entered in textbox during session

    $password = md5($_POST['log_password']);                             // Stores password in MD5 encryption

    $check_database_query = mysqli_query($con, "SELECT * FROM users WHERE email='$email' AND password='$password'");
    $check_login_query = mysqli_num_rows($check_database_query);         // Checks database for user data entered
                                                                        // This should retun a 1 or a 0
    if($check_login_query == 1) {                                        // If a user exits (1) then the account is loaded 
        // Gathers user data into variable                              // into the current session as a username                
        $row = mysqli_fetch_array($check_database_query);    
        // Isolating username from user array           
        $username = $row['username'];

        // if user account is set to closed then login back into that account will set account to NOT closed
        $user_closed_query = mysqli_query($con, "SELECT * FROM users WHERE email = '$email' AND user_closed = 'yes'");
        if(mysqli_num_rows($user_closed_query) == 1) {
            $reopen_account = mysqli_query($con, "UPDATE users SET user_closed = 'no' WHERE email = '$email' ");
        }

        $_SESSION['username'] = $username;                              // Session now stores username - Needed for profiles etc
        header("location: index.php");                                  // Redirects user to Index page - once logged in
        exit();

    } else {
        array_push($error_array, "The Email or Password you have provided does not match our records");
    }   // If not account is found an error message is presented

}       // End of isset($_POST['login_button']) IF STATEMENT


?>