<?php  

require 'Config/config.php';
include("Includes/Classes/User.php");
include("Includes/Classes/Post.php");
include("Includes/Classes/Notification.php");

if (isset($_SESSION['username'])) {
	$userLoggedIn = $_SESSION['username'];
	$user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$userLoggedIn'");
	$user = mysqli_fetch_array($user_details_query);
}
else {
	header("Location: register.php");
}

?>

<!DOCTYPE html>

<head>

	<!-- Title and Favicon -->
	<title></title>

 	<!-- CSS -->
	 <link rel="stylesheet" type="text/css" href="Assets/CSS/style.css">

</head>

<body>

	<style>

		* {
			font-size: 12px;
			font-family: Arial, Helvetica, Sans-serif;
			background-color: #ffffff;
		}

	</style>

	<script>

		function toggle() {
			var element = document.getElementById("comment_section");

			if(element.style.display == "block") 									// Sets the toggle of the comment display
				element.style.display = "none";
			else 
				element.style.display = "block";
		}

	</script>

	<?php  

		//Get id of post
		if(isset($_GET['post_id'])) {
			$post_id = $_GET['post_id'];
		}

		$user_query = mysqli_query($con, "SELECT added_by, user_to FROM posts WHERE id='$post_id'");
		$row = mysqli_fetch_array($user_query);											// Data required for building post comments

		$posted_to = $row['added_by'];													// Who the comment is posted to
		$user_to = $row['user_to'];

		if(isset($_POST['postComment' . $post_id])) {
			$post_body = $_POST['post_body'];											// Post contains
			$post_body = mysqli_escape_string($con, $post_body);
			$date_time_now = date("Y-m-d H:i:s");										// Time of comment

			// Inserting comments into database
			$insert_post = mysqli_query($con, "INSERT INTO comments VALUES ('', '$post_body', '$userLoggedIn', '$posted_to', '$date_time_now', 'no', '$post_id')");

			if($posted_to != $userLoggedIn) {	// If someone comments on a post - creates notification 

				$notification = new Notification($con, $userLoggedIn);
                $notification->insertNotification($post_id, $posted_to, "comment");

			}
			
			if ($user_to != 'none' && $user_to != $userLoggedIn)	{	// If someone posts a comment @ someone

				$notification = new Notification($con, $userLoggedIn);
                $notification->insertNotification($post_id, $user_to, "profile_comment");

			}

			$get_commenters = mysqli_query($con, "SELECT * FROM comments WHERE post_id = '$post_id'");
			$notified_users = array();

			while($row = mysqli_fetch_array($get_commenters)) {

				// If someone writes a comment people that aren't the original poster or yours get notified
				// Also !in_array section prevents the same user getting multiple notifications from a single post
				if($row['posted_by'] != $posted_to && $row['posted_by'] != $user_to 
				&& $row['posted_by'] != $userLoggedIn && !in_array($row['posted_by'], $notified_users)) {

					$notification = new Notification($con, $userLoggedIn);
					$notification->insertNotification($post_id, $row['posted_by'], "comment_non_owner");

					array_push($notified_users, $row['posted_by']);

				}

			}

			echo "<p> Comment Posted! </p>";
		}

	?>

	<!-- Comment box that appears when pressing the comment -->
	<form action="comment_frame.php?post_id=<?php echo $post_id; ?>" id="comment_form" name="postComment<?php echo $post_id; ?>" method="POST">
		
		<textarea name="post_body"></textarea>
		<input type="submit" name="postComment<?php echo $post_id; ?>" value="Post">

	</form>

	<!-- Load comments -->
	<?php
		$get_comments = mysqli_query($con, "SELECT * FROM comments WHERE post_id = '$post_id' ORDER BY id ASC");
		$count = mysqli_num_rows($get_comments);								// Number of comments

		if($count != 0) {	

			while($comment = mysqli_fetch_array($get_comments)) {				// Cycles though comments

				$comment_body = $comment['post_body'];
				$posted_to = $comment['posted_to'];
				$posted_by = $comment['posted_by'];
				$date_added = $comment['date_added'];
				$removed = $comment['removed'];

				// Timeframe - When post was posted from now
				$date_time_now = date("Y-m-d H:i:s");
				$start_date = new DateTime($date_added);                        // Time of post
				$end_date = new DateTime($date_time_now);                       // Current time
				$interval = $start_date->diff($end_date);                       // Length of time between post and current time
				$time_message = "";

				// Timeframe cascade - starts at over a year ago and ends at 1 second ago depending on $interval
				if($interval->y >= 1) {                                         // If post is equal to or longer than a year old
					// Years
					if($interval == 1) {
						$time_message = $interval->y . " year ago";             // Post was 1 year ago
					} else {
						$time_message = $interval->y . " years ago";            // Post was more than 1 year ago
					}
				} else if ($interval-> m  >= 1) {                               // If post equal to or longer than 1 month
					// Months (days)
					if($interval->d == 0) {
						$days = " ago";                                         // Post was one month and less than one day ago
					} else if ($interval->d == 1) {
						$days = $interval->d . " day ago";                      // Post was one month and one day ago
					} else {
						$days = $interval->d . " days ago";                     // Post was one month and more than one day ago
					}
					// Months
					if($interval->m = 1) {                                      // If post was one month ago
						$time_message = $interval->m . " month" . $days;
					} else {
						$time_message = $interval->m . " months" . $days;       // If post was more than one month ago
					}
				}   else if ($interval->d >= 1) {
					// Days
					if ($interval->d == 1) {
						$time_message = "Yesterday";                            // Post is one day old
					} else {
						$time_message = $interval->d . " days ago";             // Post was more than one day ago
					}
				}   else if ($interval->h >= 1) {
					// Hours
					if ($interval->h == 1) {
						$time_message = $interval->h . " hour ago";             // Post was one hour ago
					} else {
						$time_message = $interval->h . " hours ago";            // Post was more than one hour ago
					}
				}   else if ($interval->i >= 1) {
					// Minutes
					if ($interval->i == 1) {
						$time_message = $interval->i . " minute ago";           // Post was one minute ago
					} else {
						$time_message = $interval->i . " minutes ago";          // Post was more than one minute ago
					}
				}   else if ($interval->s >= 1) {
					// Seconds
					if ($interval->s < 30) {
						$time_message = "Just now";                            // Post was one second ago
					} else {
						$time_message = $interval->s . " seconds ago";          // Post was more than one second ago
					}
				}  

				$user_obj = new User($con, $posted_by);

	?>

				<div class="comment_section">
	
					<!-- When clicking comment post username opens windows to profile -->
					<a href="<?php echo $posted_by; ?>" target="_parent"><img src="<?php echo $user_obj->getProfilePic(); ?>" title="<?php echo $posted_by; ?>" style="float:left;" height="30"></a>
					<a href="<?php echo $posted_by; ?>" target="_parent"><b><?php echo $user_obj->getFirstAndLastName(); ?></b></a>
					&nbsp;&nbsp;&nbsp;&nbsp; <?php echo $time_message . "<br>" . $comment_body; ?>
					<hr>

				</div>

	<?php

			}	// End of WHILE $comment = mysqli_fetch_array($get_comments)

		} else {															// End of IF STATEMENT $count != 0

			echo "<center><br><br> No Comments to Show <br><br></center>";

		}	
	
	?>

	

</body>

</html>