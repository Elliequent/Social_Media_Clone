<?php  

require '../../Config/config.php';
include("../Classes/User.php");
include("../Classes/Post.php");
include("../Classes/Notification.php");


if(isset($_POST['post_body'])) {

    $user = $_POST['user_from'];
    $body = $_POST['post_body'];
    $friend = $_POST['user_to'];

	$post = new Post($con, $user);
	$post->submitPost($body, $friend, '');
}
	
?>