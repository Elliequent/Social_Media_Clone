<?php

include("Includes/header.php");
//session_destroy();                                                    // Removes session data to ensure login must occur

$message_obj = new Message($con, $userLoggedIn);

if(isset($_GET['profile_username'])) {                                  // Extracts username from URL
	$username = $_GET['profile_username'];
    $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username = '$username'");
    $user_array = mysqli_fetch_array($user_details_query);              // Accessing profile data of the user selected profile

    $num_friends = (substr_count($user_array['friend_array'], ",")) -1; // Counts users friends by counting commas between each friend
}

if(isset($_POST['remove_friend'])) {
    $user = new User($con, $userLoggedIn);
    $user->removeFriend($username);
}

if(isset($_POST['add_friend'])) {
    $user = new User($con, $userLoggedIn);
    $user->sendRequest($username);
}

if(isset($_POST['respond_request'])) {
    header("Location: requests.php");
}

if(isset($_POST['post_message'])) {
    if(isset($_POST['message_body'])) {
        $body = mysqli_real_escape_string($con, $_POST['message_body']);
        $date = date("Y-m-d H:i:s");
        $message_obj->sendMessage($username, $body, $date);
    }
}

?>

<!-- Requires HTAccess file - generates website URLs based upon the individual username of the user -->

<style>

    .wrapper {
        margin-left: 0px;
        padding-left: 0px;
    }

</style>

    <!-- Profile Left side bar -->
    <div class="profile_left">

        <!-- Creates user profile information -->
        <img src="<?php echo $user_array['profile_pic']; ?>" alt="Profile Picture">

        <div class="profile_info">
            <p> <?php echo "Posts: " . $user_array['num_posts']; ?> </p>
            <p> <?php echo "Likes: " . $user_array['num_likes']; ?> </p>
            <p> <?php echo "Friends: " . $num_friends; ?> </p>
        </div>

        <form action="<?php echo $username; ?>" method="POST">

            <?php 
            
                // Friend button - checks if user is closed or already friend
                $profile_user_obj = new User($con, $username); 
                if($profile_user_obj->isClosed()) {                             // If user account is closed redirect to user_closed.php
                    header("Location: user_closed.php");
                }

                $logged_in_user_obj = new User($con, $userLoggedIn); 
            
                if($userLoggedIn != $username) {                                // If the user is not visiting their own profile                  
                    if($logged_in_user_obj->isFriend($username)) {              // If user is already friends with userLoggedIn
                        // Remove friend button
                        echo '<input type="submit" name="remove_friend" class="danger" value="Remove Friend"><br>';
                    }   else if ($logged_in_user_obj->didReceiveRequest($username)) {
                        // Agree to friend request button
                        echo '<input type="submit" name="respond_request" class="warning" value="Friendship Request"><br>';
                    }   else if ($logged_in_user_obj->didSendRequest($username)) {
                        // Awaiting friend request to user
                        echo '<input type="submit" name="" class="default" value="Request Sent"><br>';
                    }   else {
                        // Add friend request to user
                        echo '<input type="submit" name="add_friend" class="success" value="Add Friend"><br>';
                    }

                }

            ?>

        </form>

        <!-- Post message to the users profile being viewed -->
        <input type="submit" class="deep_blue" data-toggle="modal" data-target="#post-form" value="Post Something">

        <?php

                // Displays the number of mutations friends the user and the the profile being viewed share
                if($userLoggedIn != $username) {

                    echo '<div class="profile_info_bottom">';
                    echo $logged_in_user_obj->getMutualFriends($username) . " Mutual Friends";
                    echo '</div>';

                }

        ?>

    </div>

    <!-- Main stream section -->
    <div class="profile_main_column column">

        <?php

            if($username == $userLoggedIn) {                            // If profile being viewed isn't your own display messages first

                echo '  <ul class="nav nav-tabs" role="tablist" id="profileTabs">
                            <li class="nav-item">
                                <a class="nav-link" href="#newsfeed_div" aria-controls="newsfeed_div" role="tab" data-toggle="tab">Newsfeed</a>
                            </li>
                        </ul>
                        
                        <div class="tab-content">
        
                        <div class="tab-pane fade active show" id="newsfeed_div">
        
                            <div class="posts_area"></div>
                            <img id="loading" src="Assets/Images/Icons/loading.gif">
                        
                        </div>';

            }   else    {

                echo '  <ul class="nav nav-tabs" role="tablist" id="profileTabs">
                            <li class="nav-item">
                                <a class="nav-link active" href="#messages_div" aria-controls="messages_div" role="tab" data-toggle="tab">Messages</a>
                            </li>
    
                            <li class="nav-item">
                                <a class="nav-link" href="#newsfeed_div" aria-controls="newsfeed_div" role="tab" data-toggle="tab">Newsfeed</a>
                            </li>
                        </ul>
                        
                        <div class="tab-content">
        
                            <div class="tab-pane fade" id="newsfeed_div">
            
                                <div class="posts_area"></div>
                                <img id="loading" src="Assets/Images/Icons/loading.gif">
                            
                            </div>
                            
                            <div class="tab-pane fade active show" id="messages_div">
                
                            

                            ' ?> <?php
        
                                echo "<h4>You and <a href='" . $username . "' - >" . $profile_user_obj->getFirstAndLastName() . "</a></h4><hr><br>";
        
                                echo "<div class='loaded_messages' id='scroll_messages'>";
                                echo $message_obj->getMessages($username);
                                echo "</div>";
        
                            ?>'

                            <?php 

                            echo
        
                            '<div class="message_post">
        
                            <form action="" method="POST">
        
                                <textarea name="message_body" id="message_textarea" placeholder="Write your message..."></textarea>
                                <input type="submit" name="post_message" class="info" id="message_submit" value="Send">
        
                            </form>
        
                            </div>
        
                            <script>
        
                            // Sets any new messages at the bottom of the page
                            var div = document.getElementById("scroll_messages");
                            div.scrollTop = div.scrollHeight;
        
                            </script>
                        
                        </div>
                
                </div>';

            }

        ?>

    <!-- Modal - Creates a window to add post to the profile being viewed -->
    <div class="modal fade" id="post-form" tabindex="-1" role="dialog" aria-labelledby="postModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">

                    <h5 class="modal-title" id="exampleModalLabel">Post something</h5>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>

                </div>

                <!-- Addes the @username feature -->
                <div class="modal-body">
                    
                    <p><?php 
                    
                    if($username != $userLoggedIn) {
                        
                        echo "To " . $profile_user_obj->getFirstAndLastName(); 
                        
                    }
                    
                    ?></p>    

                    <form class="profile_post" action="" method="POST">
      		            <div class="form-group">

                            <textarea class="form-control" name="post_body"></textarea>
                            <input type="hidden" name="user_from" value="<?php echo $userLoggedIn; ?>">
                            <input type="hidden" name="user_to" value="<?php echo $username; ?>">
                            
      		            </div>
      	            </form>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" name="post_button" id="submit_profile_post">Post</button>

                </div>
             </div>
        </div>
    </div>

    <script>																			// AJAX - calls handler ajax_load_profile_posts and loads most recent 10 posts	
    
    // Copied from index.php to profile.php - edited to display communication between users when viewing profiles (EDITS noted)
    
        var userLoggedIn = '<?php echo $userLoggedIn; ?>';								// Creates user loggin variable
        var profileUsername = '<?php echo $username; ?>';

		$(document).ready(function() {

			$('#loading').show();														// Displays loading.gif

			//Original ajax_load_posts.php request for loading first 10 posts 
			$.ajax({
				url: "Includes/Handlers/ajax_load_profile_posts.php",
				type: "POST",
				data: "page=1&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,    // EDIT - adds profile being viewed
				cache: false,

				success: function(data) {
					$('#loading').hide();												// Hides loading.gif
					$('.posts_area').html(data);										// displays first 10 posts
				}
			});

			//alert("Test 1");   														// Error Checking - AJAX

			$(window).scroll(function() {
				var height = $('.posts_area').height(); 								// Height of Div containing 10 posts
				var scroll_top = $(this).scrollTop();									// Defined top of page
				var page = $('.posts_area').find('.nextPage').val();					// Find next ten posts
				var noMorePosts = $('.posts_area').find('.noMorePosts').val();			// If there are no more posts- post this message

				// When user scrolls to bottom of page this script kicks in to load 10 more posts
				if ((document.body.scrollHeight <= document.scrollingElement.scrollTop + window.innerHeight + 1) && noMorePosts == 'false') {
					// If the user scrolls to the bottom of the page add posts until there are no more posts
					// Issue with this if statement - my window for some reason was off by a few decimal places so fixed with +1

					$('#loading').show();												// Displays loading.gif

					//alert("Test 2");													// Error Checking - IF STATEMENT

					// Next call to ajax for next 10 posts
					var ajaxReq = $.ajax({
						url: "Includes/Handlers/ajax_load_profile_posts.php",
						type: "POST",
						data: "page=" + page + "&userLoggedIn=" + userLoggedIn + userLoggedIn + "&profileUsername=" + profileUsername,
						cache:false,

						success: function(response) {
							$('.posts_area').find('.nextPage').remove(); 				// Removes current limit - limit set in Post.php
							$('.posts_area').find('.noMorePosts').remove(); 			// Removes NoMorePosts statement - Post.php 

							$('#loading').hide();										// Hides loading.gif
							$('.posts_area').append(response);							// Adds next 10 posts to existing 10 posts
						}


					});

				} //End of IF (document.body.scrollHeight == document.body.scrollTop + window.innerHeight) && noMorePosts == 'false') 

				return false;

			}); //End (window).scroll(function())

		});	// End of (document).ready(function()

	</script>


</div>  <!-- Closed tag for wrapper div (in header) -->

</body>
</html>