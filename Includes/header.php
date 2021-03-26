<?php

require 'Config/config.php';
include("Includes/Classes/User.php");
include("Includes/Classes/Post.php");
include("Includes/Classes/Message.php");
include("Includes/Classes/Notification.php");

if (isset($_SESSION['username'])) {                             // Sets user login variable to be used throughout page
                                                                // Prevents loging into the website without log on
    $userLoggedIn = $_SESSION['username'];
    $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username = '$userLoggedIn'");
    $user = mysqli_fetch_array($user_details_query);            // Collects user data for display on site
} else {
    header("Location: register.php");                           // Redirects user to register if session doesn't have username (logged in)
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <!-- Title and Favicon -->
    <title>SwirlFeed</title>
    <link rel="shortcut icon" href="Assets/Images/Favicon/favicon.ico" type="image/x-icon">
    <link rel="icon" href="Assets/Images/Favicon/favicon.ico" type="image/x-icon">

    <!-- meta -->
    <meta charset="UTF-8">
    <meta name="description" content="Example Social Media Platform">
    <meta name="keywords" content="Social Media, Messages, Tags, Posts">
    <meta name="author" content="Ian Fraser">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="Assets/JavaScript/bootstrap.js"></script>
    <script src="Assets/JavaScript/bootbox.js"></script>
    <script src="Assets/JavaScript/jcrop_bits.js"></script>
    <script src="Assets/JavaScript/jquery.Jcrop.js"></script>
    <script src="Assets/JavaScript/demo.js"></script>
    <script src="https://kit.fontawesome.com/34d9021f0e.js"></script>

    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="Assets/CSS/jquery.Jcrop.css">
    <link rel="stylesheet" type="text/css" href="Assets/CSS/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="Assets/CSS/style.css">

</head>

<body>

    <!-- Navbar Section -->
    <div class="top_bar">
        <div class="logo_img">
            <img src="Assets/Images/Logo/logo.png" alt="SwirlFeed Logo">
        </div>
        <div class="logo">
            <a href="index.php">Swirlfeed</a>
        </div>

        <!-- search bar function -->
        <div class="search">
            <form action="search.php" method="GET" name="search_form">

                <input type="text" onkeyup="getLiveSearchUsers(this.value, '<?php echo $userLoggedIn ?>')" name="q" placeholder="Search.." autocomplete="off" id="search_text_input">
                
                <div class="button_holder">
                    <i class="fas fa-search fa-lg"></i>
                </div>

            </form>

            <div class="search_results">
                
            </div>

            <div class="search_results_footer_empty">
                
            </div>

        </div>


        <nav>

        <?php

            // Unread messages
            $message = new Message($con, $userLoggedIn);
            $num_messages = $message->getUnreadNumber();

            // Unread notifications
            $notification = new Notification($con, $userLoggedIn);
            $num_notifications = $notification->getUnreadNumber();

            // Unread friend requests
            $user_obj = new User($con, $userLoggedIn);
            $num_requests = $user_obj->getNumberOfFriendRequests();

        ?>

            <!-- Navbar buttons -->
            <a href="index.php">
                <i class="fas fa-home fa-lg"></i>
            </a>
            <a href="javascript:void(0);" onclick="getDropdownData('<?php echo $userLoggedIn ?>', 'message')">
                <i class="fas fa-envelope fa-lg"></i>

                <?php       // Displays number of unread messages if above 0

                    if($num_messages > 0) {

                        echo '<span class="notification_badge" id="unread_message"> ' . $num_messages . ' </span>';

                    }
                    
                ?>

            </a>
            <a href="javascript:void(0);" onclick="getDropdownData('<?php echo $userLoggedIn ?>', 'notification')">
                <i class="fas fa-bell fa-lg"></i>

                <?php       // Displays number of unread notifications if above 0

                    if($num_notifications > 0) {

                        echo '<span class="notification_badge" id="unread_notification"> ' . $num_notifications . ' </span>';

                    }

                ?>

            </a>
            <a href="requests.php">
                <i class="fas fa-users fa-lg"></i>

                <?php       // Displays number of unread notifications if above 0

                    if($num_requests > 0) {

                        echo '<span class="notification_badge" id="unread_requests"> ' . $num_requests . ' </span>';

                    }

                ?>

            </a>
            <a href="settings.php">
                <i class="fas fa-cog fa-lg"></i>
            </a>
            <a href="Includes/Handlers/logout.php">
                <i class="fas fa-sign-out-alt fa-lg"></i>
            </a>
        </nav>

        <div class="dropdown_data_window" style="height: 0px; border: none;"></div>
        <input type="hidden" id="dropdown_data_type" value="">

    </div>


    <!-- Script for infiniate scrolling within message sub window and flags notifications -->
    <script>																			// AJAX - calls handler ajax_load_posts and loads most recent 10 posts															
		var userLoggedIn = '<?php echo $userLoggedIn; ?>';								// Creates user loggin variable

        // Infinite scrolling for message inner window
		$(document).ready(function() {

			$('.dropdown_data_window').scroll(function() {
				var inner_height = $('.dropdown_data_window').innerHeight(); 			            // Height of Div containing 10 posts
				var scroll_top = $('.dropdown_data_window').scrollTop();				            // Defined top of page
				var page = $('.dropdown_data_window').find('.nextPageDropdownData').val();	        // Find next ten posts
				var noMoreData = $('.dropdown_data_window').find('.noMoreDropdownData').val();	    // If there are no more posts- post this message

				// When user scrolls to bottom of page this script kicks in to load more messages
				if ((scroll_top + inner_height >= $('.dropdown_data_window')[0].scrollHeight) && noMoreData == 'false') {

                    var pageName;
                    var type = $('#dropdown_data_type').val();

                    if(type == "notification") {

                        pageName = "ajax_load_notifications.php";

                    }   else if(type == "message") {

                        pageName = "ajax_load_message.php";

                    }

                    console.log(type);
                    console.log(pageName);

					// Next call to ajax for next 10 posts
					var ajaxReq = $.ajax({
						url: "Includes/Handlers/" + pageName,
						type: "POST",
						data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
						cache:false,

						success: function(response) {
							$('.dropdown_data_window').find('.nextPageDropdownData').remove(); 
							$('.dropdown_data_window').find('.noMoreDropdownData').remove(); 		
                            $('.dropdown_data_window').append(response);

						}


					});

				} //End of IF (document.body.scrollHeight == document.body.scrollTop + window.innerHeight) && noMorePosts == 'false') 

				return false;

			}); //End (window).scroll(function())

		});	// End of (document).ready(function()

	</script>

    <!-- Wrapper class for each page -->
    <div class="wrapper">
