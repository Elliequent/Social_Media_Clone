<?php

include("Includes/header.php");
include("Includes/Form_Handlers/settings_handler.php");

?>

<div class="main_column column">

    <h4>Account Settings</h4>

    <a href="upload.php">
    <?php echo " <img src=' " . $user['profile_pic'] . " ' class='small_profile_pics'> " ?>
    </a> <br> <br>

    <h4>Change your details</h4> <br>

    <?php 

        $user_data_query = mysqli_query($con, "SELECT first_name, last_name, email FROM users WHERE username = '$userLoggedIn'");
        $row = mysqli_fetch_array($user_data_query);

        $first_name = $row['first_name'];
        $last_name = $row['last_name'];
        $email = $row['email'];

    ?>

    <form action="settings.php" method="POST">
        First Name: <input type="text" name="first_name" value=" <?php echo $first_name ?> " id="settings_input"> <br>
        Last Name: <input type="text" name="last_name" value=" <?php echo $last_name ?> " id="settings_input"> <br>
        Email: <input type="text" name="email" style="margin-left: 35px;" value=" <?php echo $email ?> " id="settings_input"> <br> <br>
        <?php echo $message; ?>
        <input type="submit" name="update_details" id="save_details" class="info settings_buttons" value="Update details"> <br> <br>
    </form>

    <h4> Change password </h4> <br>

    <form action="settings.php" method="POST">
        Old password: <input type="password" style="margin-left: 50px;" name="old_password" id="settings_input"> <br>
        New password: <input type="password" style="margin-left: 43px;" name="new_password_1" id="settings_input"> <br>
        New password again: <input type="password" name="new_password_2" id="settings_input"> <br> <br>
        <?php echo $password_message; ?>
        <input type="submit" name="update_password" id="save_password" class="info settings_buttons" value="Update password"> <br> <br>
    </form>

    <h4> Close account </h4> <br>

    <form action="settings.php" method="POST">
        <input type="submit" name="close_account" id="close_account" class="danger settings_buttons" value="Close Account">
    </form>

</div>