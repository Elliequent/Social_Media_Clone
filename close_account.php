<?php

include("Includes/header.php");

if (isset($_POST['cancel'])) {

    header("Location: settings.php");

}

if (isset($_POST['close_account'])) {

    $closed_query = mysqli_query($con, "UPDATE users SET user_closed = 'yes' WHERE username = '$userLoggedIn'");
    session_destroy();
    header("Location: register.php");

}

?>

<div class="main_column column">

    <h4> Close Account </h4> <br> <br>

    <div class="closing_text_area">
        <p> Are you sure you wish to close your account? </p>
        <p> Closing your account will hide your profile and all your posts, but they will be there when you come back </p>
        <p> You can reopen your account at anytime by logging in to your account </p> <br> <br>
    </div>

    <form action="close_account.php" method="POST" class="closing_text_area">
        <input type="submit" name="close_account" id="close_account" class="danger settings_buttons" value="Yes, close account">
        <input type="submit" name="cancel" id="update_details" class="info settings_buttons" value="No, take me back">
    </form>



</div>