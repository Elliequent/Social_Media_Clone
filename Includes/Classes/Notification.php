<?php

class Notification {

    // Class Attributes
    private $user_obj;
    private $con;

    public function __construct($con, $user) {
        // Creates constructor based upon the values held within the database

        $this->con = $con;
        $this->user_obj = new User($con, $user); 
    }


    public function getUnreadNumber() {
        // Finds number of unread messages the user has for notification display

        $userLoggedIn = $this->user_obj->getUsername();
        $query = mysqli_query($this->con, "SELECT * FROM notifications WHERE user_to = '$userLoggedIn' AND viewed = 'no'");

        return mysqli_num_rows($query);

    }   // End of getUnreadNumber function


    public function insertNotification($post_id, $user_to, $type) {
        // Adds notification to database

        $userLoggedIn = $this->user_obj->getUsername();
		$userLoggedInName = $this->user_obj->getFirstAndLastName();

        $date_time = date("Y-m-d H:i:s");

        switch($type) {

            case "comment":
                $message = $userLoggedInName . " commented on your post";
                break;
            
            case "like":
                $message = $userLoggedInName . " liked your post";
                break;

            case "profile_post":
                $message = $userLoggedInName . " posted on your profile";
                break;

            case "comment_non_owner":
                $message = $userLoggedInName . " commented on a post you commented on";
                break;  
                
            case "profile_comment":
                $message = $userLoggedInName . " commented on your profile post";
                break; 

        }

        $link = "post.php?id=" . $post_id;

        $insert_query = mysqli_query($this->con, "INSERT INTO notifications VALUES ('', '$user_to', '$userLoggedIn', '$message', '$link', '$date_time', 'no', 'no')");


    }   // End of insertNotification function


    public function getNotifications($data, $limit) {
        // Gets a list of notifications from user based interactions

        $page = $data['page'];
        $userLoggedIn = $this->user_obj->getUsername();
        $userLoggedIn = strtolower($userLoggedIn);
        $return_string = "";

        if($page == 1) {

            $start = 0;

        }   else    {

            $start = ($page - 1) * $limit;

        }

        $set_viewed_query = mysqli_query($this->con, "UPDATE notifications SET viewed = 'yes' WHERE user_to = '$userLoggedIn'");
        $query = mysqli_query($this->con, "SELECT * FROM notifications WHERE user_to = '$userLoggedIn' ORDER BY id DESC");

        if(mysqli_num_rows($query) == 0) {

            echo "You have no notifications";
            return;

        }

        $num_iternations = 0;       // Number of messages checked
        $count = 1;                 // Number of messages posted

        while($row = mysqli_fetch_array($query)) {

            if($num_iternations++ < $start) {

                continue;

            }

            if($count > $limit) {

                break;

            }   else    {

                $count++;

            }

            // Notification of which user created the notification
            $user_from = $row['user_from'];
            $user_data_query = mysqli_query($this->con, "SELECT * FROM users WHERE username = '$user_from'");
            $user_data = mysqli_fetch_array($user_data_query);

            // Timeframe - When post was posted from now
            $date_time_now = date("Y-m-d H:i:s");
            $start_date = new DateTime($row['datetime']);                   // Time of post
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

            $opened = $row['opened'];
            $style = (isset($row['opened']) && $row['opened'] == 'no' ) ? "background-color: #DDEDFF;" : "";

            $return_string .= " <a href='" . $row['link'] . "'> 
                                    <div class='resultDisplay resultDisplayNotification' style='" . $style . "'> 
                                        <div class='notificationsProfilePic'>
                                            <img src='" . $user_data['profile_pic'] . "'>
                                        </div>
                                        <p class='timestampSmaller' id='grey'> " . $time_message . " </p> " . $row['message'] . "
                                    </div>
                                </a>";

        }

        // If posts were loaded
        if($count > $limit) {

            $return_string .= "<input type='hidden' class='nextPageDropdownData' value='" . ($page + 1) . "'> <input type='hidden' class='noMoreDropdownData' value='false'>";

        }   else    {

            $return_string .= "<input type='hidden' class='noMoreDropdownData' value='true'> <p style='text-align: center;'> No more notifications </p> ";

        }


        return $return_string;

    }   // End of getNotifications function


}   // End of class

?>