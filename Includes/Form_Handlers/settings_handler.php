<?php

// User details
if (isset($_POST['update_details'])) {

    // When update user details is pressed the values in the text fields is updated in the database
    $first_name = strip_tags($_POST['first_name']);
    $last_name = strip_tags($_POST['last_name']);
    $email = strip_tags($_POST['email']);

    $email_check = mysqli_query($con, "SELECT * FROM users WHERE email = '$email'");
    $row = mysqli_fetch_array($email_check);

    @$matched_user = $row['username'];

    if ($matched_user == "" || $matched_user === $userLoggedIn) {

        $message = "Details updated <br> <br>";

        $query = mysqli_query($con, "UPDATE users SET first_name = '$first_name', last_name = '$last_name', email = '$email' WHERE username = '$userLoggedIn'");

    }   else    {

        $message = "That email is already in use <br> <br>";

    }

}   else    {

    $message = "";

}


// Password
if (isset($_POST['update_password'])) {

    // Old password is confirmed and if both new passwords match then the new password is added to the database
    $old_password = strip_tags($_POST['old_password']);
    $new_password_1 = strip_tags($_POST['new_password_1']);
    $new_password_2 = strip_tags($_POST['new_password_2']);

    $password_query = mysqli_query($con, "SELECT password FROM users WHERE username = '$userLoggedIn'");
    $row = mysqli_fetch_array($password_query);
    $db_password = $row['password'];

    if (md5($old_password) == $db_password) {   // If encrypted password matches user stored password

        if ($new_password_1 == $new_password_2) {

            if (strlen($new_password_1) <= 4) {

                $password_message = "Sorry, your password must be greater than 4 characters <br> <br>";

            }   else if ($new_password_1 > 25) {

                $password_message = "Sorry, your password must be less than 25 characters <br> <br>";

            }   else    {

                $new_password_md5 = md5($new_password_1);
                $password_query = mysqli_query($con, "UPDATE users SET password = '$new_password_md5' WHERE username = '$userLoggedIn'");

                $password_message = "Password changed <br> <br>";

            }

        }   else    {

            $password_message = "Your passwords do not match <br> <br>";

        }

    }   else    {

        $password_message = "Your old password do not match our records <br> <br>";

    }

}   else    {

    $password_message = "";

}


// Close account
if (isset($_POST['close_account'])) {

    header("Location: close_account.php");

}

?>