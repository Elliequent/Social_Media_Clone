<?php

include("Includes/header.php");

if(isset($_GET['id'])) {

    $id = $_GET['id'];

}   else    {

    $id = 0;

}

?>

<div class="user_details column">

<!-- User profile picture -->
<a href="<?php echo $userLoggedIn; ?>"> <img src="<?php echo $user['profile_pic'] ?>" alt="Profile Picture"> </a>


<div class="user_details_data">
    <!-- User first and last name -->
    <a href="<?php echo $userLoggedIn; ?>">
    <?php
    echo $user['first_name'] . " " . $user['last_name'];
    ?>
    </a>

    <br>
    
    <!-- User profile data (likes and posts) -->
    <?php 
    echo "Posts: " . $user['num_posts'] . "<br>"; 
    echo "Likes: " . $user['num_likes'];
    ?>
</div>

</div>

<div class="main_column column" id="main_column">
    <div class="posts_area">

    <?php

    $post = new Post($con, $userLoggedIn);
    $post->getSinglePost($id);

    ?>

    </div>
</div>