<?php

    require '../../Config/config.php';

    if(isset($_GET['post_id'])) {           // Post id to be deleted
        $post_id = $_GET['post_id'];
    }

    if(isset($_POST['result'])) {           // User choice delete (Yes/No)
        if($_POST['result'] == 'true') {
            $query = mysqli_query($con, "UPDATE posts SET deleted = 'yes' WHERE id = '$post_id'");
        }
    }

?>