<?php

// Copied below from ajax_load_posts.php

// Needed for loading posts and infinite scrolling method within the profile section

include("../../Config/config.php");
include("../Classes/User.php");
include("../Classes/Post.php");

$limit = 10;                                                // Number of posts called at a time
$posts = new Post($con, $_REQUEST['userLoggedIn']);         // Creating instance of Post object
$posts->loadProfilePosts($_REQUEST, $limit);                // Each call to AJAX brings 10 posts from user database

?>