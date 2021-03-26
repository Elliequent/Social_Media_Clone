<?php

class Post {

    // Class Attributes
    private $user_obj;
    private $con;

    public function __construct($con, $user) {
        // Creates constructor based upon the values held within the database
        $this->con = $con;
        $this->user_obj = new User($con, $user); 
    }

    public function submitPost($body, $user_to, $imageName) {                               // Processes user entry into post

        $body = strip_tags($body);                                              // Removes HTML tags - security
        $body = mysqli_real_escape_string($this->con, $body);                   // Removes problematic SQL grammer from post - security
        if(strpos($body, '\r\n', '\r', '\n')) {                                 // If post contains returns
            $body = str_replace(array('\r\n', '\r', '\n'), '<br />', $body);    // Allows line breaks (returns) in posts - converts \r\n to \n
        }                   
        $check_empty = preg_replace('/\s+/', '', $body);                        // Deletes all spaces - if blank

        if($check_empty != "") {   // If user post is not blank

            // Adding embedded Youtube Links - This search through all posts and looks for youtube links
            $body_array = preg_split("/\s+/", $body);

            foreach($body_array as $key => $value) {

                if(strpos($value, "www.youtube.com/watch?v=") !== false) {

                    // Sets youtube link into an embedded iframe
                    $link = preg_split("!&!", $value);
					$value = preg_replace("!watch\?v=!", "embed/", $link[0]);
					$value = "<br><iframe width=\'600\' height=\'400\' src=\'" . $value ."\'></iframe><br>";
					$body_array[$key] = $value;

                }

            }

            // Adding youtube embedded links to body
            $body = implode(" ", $body_array);

            // Current date and time
            $date_added = date("Y-m-d H:i:s");
            // Getting username
            $added_by = $this->user_obj->getUsername();

            // If user is on own profile, user_to us NONE
            if($user_to == $added_by) {
                $user_to = "none";
            }

            // Insert post to database
            $query = mysqli_query($this->con, "INSERT INTO posts VALUES('', '$body', '$added_by', '$user_to', '$date_added', 'no', 'no', '0', '$imageName')");
            // Returns the ID of the post just submitted
            $returned_id = mysqli_insert_id($this->con);
            
            // Insert notifcation
            if($user_to != 'none') {        // Notification created if user posts something @ someone

                $notification = new Notification($this->con, $added_by);
                $notification->insertNotification($returned_id, $user_to, "profile_post");

            }

            // Update post count for user
            $num_posts = $this->user_obj->getNumPosts();
            $num_posts++;
            $update_query = mysqli_query($this->con, "UPDATE users SET num_posts = '$num_posts' WHERE username = '$added_by'");


            // Creating the trending tab - these words will not appear in the trending tab
            $stopWords = "a about above across after again against all almost alone along already
			 also although always among am an and another any anybody anyone anything anywhere are 
			 area areas around as ask asked asking asks at away b back backed backing backs be became
			 because become becomes been before began behind being beings best better between big 
			 both but by c came can cannot case cases certain certainly clear clearly come could
			 d did differ different differently do does done down down downed downing downs during
			 e each early either end ended ending ends enough even evenly ever every everybody
			 everyone everything everywhere f face faces fact facts far felt few find finds first
			 for four from full fully further furthered furthering furthers g gave general generally
			 get gets give given gives go going good goods got great greater greatest group grouped
			 grouping groups h had has have having he her here herself high high high higher
		     highest him himself his how however i im if important in interest interested interesting
			 interests into is it its itself j just k keep keeps kind knew know known knows
			 large largely last later latest least less let lets like likely long longer
			 longest m made make making man many may me member members men might more most
			 mostly mr mrs much must my myself n necessary need needed needing needs never
			 new new newer newest next no nobody non noone not nothing now nowhere number
			 numbers o of off often old older oldest on once one only open opened opening
			 opens or order ordered ordering orders other others our out over p part parted
			 parting parts per perhaps place places point pointed pointing points possible
			 present presented presenting presents problem problems put puts q quite r
			 rather really right right room rooms s said same saw say says second seconds
			 see seem seemed seeming seems sees several shall she should show showed
			 showing shows side sides since small smaller smallest so some somebody
			 someone something somewhere state states still still such sure t take
			 taken than that the their them then there therefore these they thing
			 things think thinks this those though thought thoughts three through
	         thus to today together too took toward turn turned turning turns two
			 u under until up upon us use used uses v very w want wanted wanting
			 wants was way ways we well wells went were what when where whether
			 which while who whole whose why will with within without work
			 worked working works would x y year years yet you young younger
			 youngest your yours z lol haha omg hey ill iframe wonder else like 
             hate sleepy reason for some little yes bye choose testing test";

             //Convert stop words into array - split at white space
			$stopWords = preg_split("/[\s,]+/", $stopWords);

			//Remove all punctionation
			$no_punctuation = preg_replace("/[^a-zA-Z 0-9]+/", "", $body);

			//Predict whether user is posting a url. If so, do not check for trending words
			if(strpos($no_punctuation, "height") == false && strpos($no_punctuation, "width") == false
				&& strpos($no_punctuation, "http") == false && strpos($no_punctuation, "youtube") == false){

				//Convert users post (with punctuation removed) into array - split at white space
				$keywords = preg_split("/[\s,]+/", $no_punctuation);

				foreach($stopWords as $value) {

					foreach($keywords as $key => $value2){

						if(strtolower($value) == strtolower($value2))
                            $keywords[$key] = "";
                            
                    }
                    
				}

				foreach ($keywords as $value) {

                    $this->calculateTrend(ucfirst($value));
                    
				}

            }


        } // End of $check_empty != "" IF STATEMENT

    }   // End of function submitPost

    public function loadPostsFriends($data, $limit) {

        $page = $data['page'];                                              // The 10 posts loaded from AJAX
		$userLoggedIn = $this->user_obj->getUsername();                     // Username of logged in person

		if($page == 1)                                                      // Page starts at 1
			$start = 0; 
		else 
			$start = ($page - 1) * $limit;                                  // Boolean function if page = 0 then start is high                              
                                                                            // Needed for if($num_iterations++ < $start)

        $str = "";                                                          // Return string (Posts)
        
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted ='no' ORDER BY id DESC");

        
		if(mysqli_num_rows($data_query) > 0) {                                  // If database search results has data

			$num_iterations = 0;                                                // Number of times 10 posts are loaded
			$count = 1;                                                         // Post counter

            while($row = mysqli_fetch_array($data_query)) {                     // Populating post variables
                $id = $row['id'];
                $body = $row['body'];
                $added_by = $row['added_by'];
                $date_time = $row['date_added'];
                $imagePath = $row['image'];

                // Preparing user_to string for posts
                if($row['user_to'] == 'none') {
                    $user_to = "";                                              // If post is not sent ot anyone
                } else {
                    $user_to_obj = new User($this->con, $row['user_to']);             // ICreate instance of user object
                    $user_to_name = $user_to_obj->getFirstAndLastName();        // Gets targetted users name for posts to that person
                    $user_to = "to <a href='" . $row['user_to'] . "'>" . $user_to_name . "</a>";
                }

                // Posted to account - check if closed (prevents posts to closed accounts)
                $added_by_obj = new User($this->con, $added_by);                // Create instance of user object
                if($added_by_obj->isClosed()) {                                 // If user posted to is set to closed
                    continue;
                }

                $user_logged_obj = new User($this->con, $userLoggedIn);         // Creating user object for loading friends posts
                if($user_logged_obj->isFriend($added_by)) {                     // Only Display posts from friends

                    
                    if($num_iterations++ < $start)                              // If start higher than num_iterations then false
                            continue;                                           // boolean function above limits posts if reached max
                        
                        if($count > $limit) {                                   //Once 10 posts have been loaded, break
                            break;
                        }
                        else {
                            $count++;
                        }

                    if($userLoggedIn == $added_by) {                              // If logged on user created the post
                        $delete_button = "<button class='delete_button' id='post$id'><i class='far fa-trash-alt'></i></button>";
                    }   else {
                        $delete_button = "";
                    }

                    $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username = '$added_by'");
                    $user_row = mysqli_fetch_array($user_details_query);            // Collect selected users details
                    $first_name = $user_row['first_name'];
                    $last_name = $user_row['last_name'];
                    $profile_pic = $user_row['profile_pic'];

        ?>  

                    <!-- Closing php tags here for HTML comments block -->

                    <script>
                        function toggle<?php echo $id; ?>() {

                            var target = $(event.target);
                            if(!target.is("a")) {

                                // Toogle for comments to posts
                                var element = document.getElementById("toggleComment<?php echo $id; ?>");

                                if(element.style.display == "block") {                      // Sets the toggle of the comment display
                                    element.style.display = "none";
                                } else {
                                    element.style.display = "block";
                                }

                            }

                        }
                    
                    </script>

        <?php

                    $comment_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id = '$id'");
                    $comments_check_num = mysqli_num_rows($comment_check);

                    // Timeframe - When post was posted from now
                    $date_time_now = date("Y-m-d H:i:s");
                    $start_date = new DateTime($date_time);                         // Time of post
                    $end_date = new DateTime($date_time_now);                       // Current time
                    $interval = $start_date->diff($end_date);                       // Length of time between post and current time
                    $time_message = "";

                    // Timeframe cascade - starts at over a year ago and ends at 1 second ago depending on $interval
                    if($interval->y >= 1) {                                         // If post is equal to or longer than a year old
                        // Years
                        if($interval->y == 1) {
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

                    // Image processing
                    if ($imagePath != "") {

                        $imageDiv = "   <div class='postedImage'>
                                            <img src='$imagePath'>
                                        </div>";

                    }   else    {

                        $imageDiv = "";

                    }

                    $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
                                <div class='post_profile_pic'>
                                    <img src='$profile_pic' width=50>
                                </div>
                                <div class='posted_by' style='color:#ACACAC;'>
                                    <a href='$added_by'> $first_name $last_name </a> $user_to &nbsp;&nbsp;&nbsp;&nbsp; $time_message
                                    $delete_button
                                </div>
                                <div id='post_body' class='post_body'>
                                    $body
                                    <br>
                                    <br>
                                    $imageDiv
                                    <br>
                                    <br>
                                </div>

                                <div class='newsfeedPostOptions'>

                                    Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
                                    <iframe src='like.php?post_id=$id' scrolling='no'></iframe>

                                </div>

                            </div>

                            <div class='post_comment' id='toggleComment$id' style='display: none;'>
                                <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
                            </div>

                            <hr>";    // Post output

                } // End of IF STATEMENT (user_logged_obj->isFriend($added_by)

                ?>

                    <script>

                    $(document).ready(function() {                                  // Delete button JavaScript

                        $('#post<?php echo $id; ?>').on('click', function() {       // On click on delete button
                            
                            // Uses bootbox to check if the user wishes to delete the post
                            bootbox.confirm("Are you sure you want to delete this post?", function(result) {
                                
                                // Post is deleted in delete_post.php (Also sends users choice (Yes or No))
                                $.post("Includes/Form_Handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

                                if(result)
                                    location.reload();                              // Page reloaded

                            });
                        });


                    });

                    </script>

                <?php


            }   // End of WHILE ($row = mysqli_fetch_array($data)

            if($count > $limit)     // Adds hidden string at the bottom of each 10 posts stating the number of post iterations 
				$str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
							<input type='hidden' class='noMorePosts' value='false'>";
			else                    // When no more posts are available this message is displayed - NoMorePosts = true
                $str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align: centre;'> No more posts to show! </p>";
                
        }   // End of IF (mysqli_num_rows($data_query) > 0)

        echo $str;      // Outputs each post entered through while loop above - Creates main timeline

    }   // End of function loadPostsFriends()


    public function calculateTrend($term) {

        if($term != '') {
			$query = mysqli_query($this->con, "SELECT * FROM trends WHERE title = '$term'");

			if(mysqli_num_rows($query) == 0)
				$insert_query = mysqli_query($this->con, "INSERT INTO trends (title, hits) VALUES ('$term', '1')");
			else 
				$insert_query = mysqli_query($this->con, "UPDATE trends SET hits = hits + 1 WHERE title = '$term' ");
		}

    }   // End of function calculateTrend

    public function loadProfilePosts($data, $limit) {

        $page = $data['page'];                                              // The 10 posts loaded from AJAX
        $profileUser = $data['profileUsername'];
		$userLoggedIn = $this->user_obj->getUsername();                     // Username of logged in person

		if($page == 1)                                                      // Page starts at 1
			$start = 0; 
		else 
			$start = ($page - 1) * $limit;                                  // Boolean function if page = 0 then start is high                              
                                                                            // Needed for if($num_iterations++ < $start)

        $str = "";                                                          // Return string (Posts)
        
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted ='no' AND ((added_by = '$profileUser' AND user_to = 'none') OR user_to = '$profileUser') ORDER BY id DESC");

        
		if(mysqli_num_rows($data_query) > 0) {                                  // If database search results has data

			$num_iterations = 0;                                                // Number of times 10 posts are loaded
			$count = 1;                                                         // Post counter

            while($row = mysqli_fetch_array($data_query)) {                     // Populating post variables
                $id = $row['id'];
                $body = $row['body'];
                $added_by = $row['added_by'];
                $date_time = $row['date_added'];
                    
                    if($num_iterations++ < $start)                              // If start higher than num_iterations then false
                            continue;                                           // boolean function above limits posts if reached max
                        
                        if($count > $limit) {                                   //Once 10 posts have been loaded, break
                            break;
                        }
                        else {
                            $count++;
                        }

                    if($userLoggedIn == $added_by) {                              // If logged on user created the post
                        $delete_button = "<button class='delete_button' id='post$id'><i class='far fa-trash-alt'></i></button>";
                    }   else {
                        $delete_button = "";
                    }

                    $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username = '$added_by'");
                    $user_row = mysqli_fetch_array($user_details_query);            // Collect selected users details
                    $first_name = $user_row['first_name'];
                    $last_name = $user_row['last_name'];
                    $profile_pic = $user_row['profile_pic'];

        ?>  

                    <!-- Closing php tags here for HTML comments block -->

                    <script>
                        function toggle<?php echo $id; ?>() {

                            var target = $(event.target);
                            if(!target.is("a")) {

                                // Toogle for comments to posts
                                var element = document.getElementById("toggleComment<?php echo $id; ?>");

                                if(element.style.display == "block") {                      // Sets the toggle of the comment display
                                    element.style.display = "none";
                                } else {
                                    element.style.display = "block";
                                }

                            }

                        }
                    
                    </script>

        <?php

                    $comment_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id = '$id'");
                    $comments_check_num = mysqli_num_rows($comment_check);

                    // Timeframe - When post was posted from now
                    $date_time_now = date("Y-m-d H:i:s");
                    $start_date = new DateTime($date_time);                         // Time of post
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

                    $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
                                <div class='post_profile_pic'>
                                    <img src='$profile_pic' width=50>
                                </div>
                                <div class='posted_by' style='color:#ACACAC;'>
                                    <a href='$added_by'> $first_name $last_name </a> &nbsp;&nbsp;&nbsp;&nbsp; $time_message
                                    $delete_button
                                </div>
                                <div id='post_body' class='post_body'>
                                    $body
                                    <br>
                                    <br>
                                    <br>
                                </div>

                                <div class='newsfeedPostOptions'>

                                    Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
                                    <iframe src='like.php?post_id=$id' scrolling='no'></iframe>

                                </div>

                            </div>

                            <div class='post_comment' id='toggleComment$id' style='display: none;'>
                                <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
                            </div>

                            <hr>";    // Post output

                ?>

                    <script>

                    $(document).ready(function() {                                  // Delete button JavaScript

                        $('#post<?php echo $id; ?>').on('click', function() {       // On click on delete button
                            
                            // Uses bootbox to check if the user wishes to delete the post
                            bootbox.confirm("Are you sure you want to delete this post?", function(result) {
                                
                                // Post is deleted in delete_post.php (Also sends users choice (Yes or No))
                                $.post("Includes/Form_Handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

                                if(result)
                                    location.reload();                              // Page reloaded

                            });
                        });


                    });

                    </script>

                <?php


            }   // End of WHILE ($row = mysqli_fetch_array($data)

            if($count > $limit)     // Adds hidden string at the bottom of each 10 posts stating the number of post iterations 
				$str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
							<input type='hidden' class='noMorePosts' value='false'>";
			else                    // When no more posts are available this message is displayed - NoMorePosts = true
                $str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align: centre;'> No more posts to show! </p>";
                
        }   // End of IF (mysqli_num_rows($data_query) > 0)

        echo $str;      // Outputs each post entered through while loop above - Creates main timeline

    }   // End of function loadPostsFriends()


    public function getSinglePost($post_id) {

		$userLoggedIn = $this->user_obj->getUsername();                     // Username of logged in person

        $opened_query = mysqli_query($this->con, "UPDATE notifications SET opened = 'yes' WHERE user_to = '$userLoggedIn' AND link LIKE '%=$post_id'");

        $str = "";                                                          // Return string (Posts)
        
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted ='no' AND id = '$post_id'");

        
		if(mysqli_num_rows($data_query) > 0) {                                  // If database search results has data

            $row = mysqli_fetch_array($data_query);                         // Populating post variables
            $id = $row['id'];
            $body = $row['body'];
            $added_by = $row['added_by'];
            $date_time = $row['date_added'];

            // Preparing user_to string for posts
            if($row['user_to'] == 'none') {
                $user_to = "";                                              // If post is not sent ot anyone
            } else {
                $user_to_obj = new User($this->con, $row['user_to']);             // ICreate instance of user object
                $user_to_name = $user_to_obj->getFirstAndLastName();        // Gets targetted users name for posts to that person
                $user_to = "to <a href='" . $row['user_to'] . "'>" . $user_to_name . "</a>";
            }

            // Posted to account - check if closed (prevents posts to closed accounts)
            $added_by_obj = new User($this->con, $added_by);                // Create instance of user object
            if($added_by_obj->isClosed()) {                                 // If user posted to is set to closed
                return;
            }

            $user_logged_obj = new User($this->con, $userLoggedIn);         // Creating user object for loading friends posts
            if($user_logged_obj->isFriend($added_by)) {                     // Only Display posts from friends

                if($userLoggedIn == $added_by) {                              // If logged on user created the post
                    $delete_button = "<button class='delete_button' id='post$id'><i class='far fa-trash-alt'></i></button>";
                }   else {
                    $delete_button = "";
                }

                $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username = '$added_by'");
                $user_row = mysqli_fetch_array($user_details_query);            // Collect selected users details
                $first_name = $user_row['first_name'];
                $last_name = $user_row['last_name'];
                $profile_pic = $user_row['profile_pic'];

        ?>  

                    <!-- Closing php tags here for HTML comments block -->

                    <script>
                        function toggle<?php echo $id; ?>() {

                            var target = $(event.target);
                            if(!target.is("a")) {

                                // Toogle for comments to posts
                                var element = document.getElementById("toggleComment<?php echo $id; ?>");

                                if(element.style.display == "block") {                      // Sets the toggle of the comment display
                                    element.style.display = "none";
                                } else {
                                    element.style.display = "block";
                                }

                            }

                        }
                    
                    </script>

        <?php

                    $comment_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id = '$id'");
                    $comments_check_num = mysqli_num_rows($comment_check);

                    // Timeframe - When post was posted from now
                    $date_time_now = date("Y-m-d H:i:s");
                    $start_date = new DateTime($date_time);                         // Time of post
                    $end_date = new DateTime($date_time_now);                       // Current time
                    $interval = $start_date->diff($end_date);                       // Length of time between post and current time
                    $time_message = "";

                    // Timeframe cascade - starts at over a year ago and ends at 1 second ago depending on $interval
                    if($interval->y >= 1) {                                         // If post is equal to or longer than a year old
                        // Years
                        if($interval->y == 1) {
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

                    $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
                                <div class='post_profile_pic'>
                                    <img src='$profile_pic' width=50>
                                </div>
                                <div class='posted_by' style='color:#ACACAC;'>
                                    <a href='$added_by'> $first_name $last_name </a> $user_to &nbsp;&nbsp;&nbsp;&nbsp; $time_message
                                    $delete_button
                                </div>
                                <div id='post_body' class='post_body'>
                                    $body
                                    <br>
                                    <br>
                                    <br>
                                </div>

                                <div class='newsfeedPostOptions'>

                                    Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
                                    <iframe src='like.php?post_id=$id' scrolling='no'></iframe>

                                </div>

                            </div>

                            <div class='post_comment' id='toggleComment$id' style='display: none;'>
                                <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
                            </div>

                            <hr>";    // Post output

                    ?>

                        <script>

                        $(document).ready(function() {                                  // Delete button JavaScript

                            $('#post<?php echo $id; ?>').on('click', function() {       // On click on delete button
                                
                                // Uses bootbox to check if the user wishes to delete the post
                                bootbox.confirm("Are you sure you want to delete this post?", function(result) {
                                    
                                    // Post is deleted in delete_post.php (Also sends users choice (Yes or No))
                                    $.post("Includes/Form_Handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

                                    if(result)
                                        location.reload();                              // Page reloaded

                                });
                            });


                        });

                        </script>

                    <?php

            }   else    {

                echo "<p>Post is not visible as you are not friends with user</p>";
                return;

            }

        }   else    {

            echo "<p>No post found</p>";
            return;

        }

        echo $str;      // Outputs each post entered through while loop above - Creates main timeline

    }   // End of function getSinglePost


}   // End of class

?>