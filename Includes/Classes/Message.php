<?php

class Message {

    // Class Attributes
    private $user_obj;
    private $con;

    public function __construct($con, $user) {
        // Creates constructor based upon the values held within the database

        $this->con = $con;
        $this->user_obj = new User($con, $user); 
    }


    public function getMostRecentUser() {
        // Messaging system - Finds most recent person talked to on messaging and contiunes conversation
        // If no previous person found then sets the messaging system to "new"

        $userLoggedIn = $this->user_obj->getUsername();

        $query = mysqli_query($this->con, "SELECT user_to, user_from FROM messages WHERE user_to = '$userLoggedIn' OR user_from = '$userLoggedIn' ORDER BY id DESC LIMIT 1");
        
        if(mysqli_num_rows($query) == 0) {                              // If no recent conversations have occured
            return false;
        }

        $row = mysqli_fetch_array($query);
        $user_to = $row['user_to'];
        $user_from = $row['user_from'];

        $user_to = strtolower($user_to);                                // Error fixing - sometimes usernames become capitalised
        $user_from = strtolower($user_from);

        if($user_to != $userLoggedIn) {
            return $user_to;                                            // If user_to is not the current user
        }   else    {
            return $user_from;                                          // If user_to is the current user
        }
    }   // End of getMostRecentUser function


    public function sendMessage($user_to, $body, $date) {
        // Adds user message to database

        if($body != "") {

            $userLoggedIn = $this->user_obj->getUsername();
            $userLoggedIn = strtolower($userLoggedIn);
            $user_to = strtolower($user_to);

            $query = mysqli_query($this->con, "INSERT INTO messages VALUES ('', '$user_to', '$userLoggedIn', '$body', '$date', 'no', 'no', 'no')");

        }


    }   // End of sendMessage function


    public function getMessages($other_user) {
        // Collects messages between users and updates the "message read" function

        $userLoggedIn = $this->user_obj->getUsername();

        $data = "";

        // If message is being read then the message is marked as being read
        $query = mysqli_query($this->con, "UPDATE messages SET opened = 'yes' WHERE user_to = '$userLoggedIn' AND user_from = '$other_user'");

        // Retrieves every message between the two users have the conversation
        $get_messages_query = mysqli_query($this->con, "SELECT * FROM messages WHERE (user_to = '$userLoggedIn' AND user_from = '$other_user') OR (user_from = '$userLoggedIn' AND user_to = '$other_user')");

        while($row = mysqli_fetch_array($get_messages_query)) {

            $user_to = $row['user_to'];
            $user_from = $row['user_from'];
            $body = $row['body'];

            $user_to = strtolower($user_to);                                // Error fixing - sometimes usernames become capitalised
            $user_from = strtolower($user_from);

            $div_top = ($user_to == $userLoggedIn) ? "<div class='message' id = 'green'>" : "<div class='message' id = 'blue'>";

            $data = $data . $div_top . $body . "</div><br><br>";

        }

        return $data;

    }   // End f getMessages function


    public function getLatestMessages($userLoggedIn, $user2) {
        // Gets latest message from database and displays user_to, body and time and date cascade

        $details_array = array();

        $query = mysqli_query($this->con, "SELECT body, user_to, date FROM messages WHERE (user_to = '$userLoggedIn' AND user_from = '$user2') OR
                                                                                    (user_to = '$user2' AND user_from = '$userLoggedIn') ORDER BY id DESC LIMIT 1");

        $row = mysqli_fetch_array($query);

        $sent_by = ($row['user_to'] == $userLoggedIn) ? "They said: " : "You said: ";

        // Timeframe - When post was posted from now
        $date_time_now = date("Y-m-d H:i:s");
        $start_date = new DateTime($row['date']);                       // Time of post
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

        array_push($details_array, $sent_by);
        array_push($details_array, $row['body']);
        array_push($details_array, $time_message);

        return $details_array;

    }   // End of getLatestMessages function


    public function getConvos() {
        // Gets a list of conversations the user has had with other users

        $userLoggedIn = $this->user_obj->getUsername();
        $userLoggedIn = strtolower($userLoggedIn);

        $return_string = "";
        $convos = array();

        $query = mysqli_query($this->con, "SELECT user_to, user_from FROM messages WHERE user_to = '$userLoggedIn' OR user_from = '$userLoggedIn' ORDER BY id DESC");

        while($row = mysqli_fetch_array($query)) {                              // For each user the loggedinUser has messaged
            
            $user_to_push = ($row['user_to'] != $userLoggedIn) ? $row['user_to'] : $row['user_from'];

            if(!in_array($user_to_push, $convos)) {                             // If user checked is not already in the array

                array_push($convos, $user_to_push);                             // Add user being checked to array

            }

        }

        foreach($convos as $username) {

            $user_found_obj = new User($this->con, $username);
            $latest_message_details = $this->getLatestMessages($userLoggedIn, $username);

            // To display small message bites - if message is over 12 characters add a "..." to display message is longer
            $dots = (strlen($latest_message_details[1]) >= 12) ? "..." : "";
            $split = str_split($latest_message_details[1], 12);
            $split = $split[0] . $dots;

            $return_string .= "<a href='messages.php?u=$username'> 
                                    <div class='user_found_messages'>
                                        <img src='" . $user_found_obj->getProfilePic() . "' style='border-radius: 5px; margin-right: 5px;'>
                                        " . $user_found_obj->getFirstAndLastName() . " <br>
                                        <span class='timestamp_smaller' id='grey'>" . $latest_message_details[2] . " </span> <br> <br>
                                        <p id='grey' style='margin: 0;'> " . $latest_message_details[0] . $split . " </p> 
                                    </div>
                               </a>";

        }

        return $return_string;

    }   // End of getConvos function


    public function getConvosDropdown($data, $limit) {
        // Gets a list of conversations the user has had with other users and populates the dropdown menu on navbar

        $page = $data['page'];
        $userLoggedIn = $this->user_obj->getUsername();
        $userLoggedIn = strtolower($userLoggedIn);
        $return_string = "";
        $convos = array();

        if($page == 1) {

            $start = 0;

        }   else    {

            $start = ($page - 1) * $limit;

        }

        $set_viewed_query = mysqli_query($this->con, "UPDATE messages SET viewed = 'yes' WHERE user_to = '$userLoggedIn'");
        $query = mysqli_query($this->con, "SELECT user_to, user_from FROM messages WHERE user_to = '$userLoggedIn' OR user_from = '$userLoggedIn' ORDER BY id DESC");

        while($row = mysqli_fetch_array($query)) {                              // For each user the loggedinUser has messaged
            
            $user_to_push = ($row['user_to'] != $userLoggedIn) ? $row['user_to'] : $row['user_from'];

            if(!in_array($user_to_push, $convos)) {                             // If user checked is not already in the array

                array_push($convos, $user_to_push);                             // Add user being checked to array

            }

        }

        $num_iternations = 0;       // Number of messages checked
        $count = 1;                 // Number of messages posted

        foreach($convos as $username) {

            if($num_iternations++ < $start) {

                continue;

            }

            if($count > $limit) {

                break;

            }   else    {

                $count++;

            }

            $is_unread_query = mysqli_query($this->con, "SELECT opened FROM messages WHERE user_to = '$userLoggedIn' AND user_from = '$username' ORDER BY id DESC");
            $row = mysqli_fetch_array($is_unread_query);
            $style = (isset($row['opened']) && $row['opened'] == 'no' ) ? "background-color: #DDEDFF;" : "";


            $user_found_obj = new User($this->con, $username);
            $latest_message_details = $this->getLatestMessages($userLoggedIn, $username);

            // To display small message bites - if message is over 12 characters add a "..." to display message is longer
            $dots = (strlen($latest_message_details[1]) >= 12) ? "..." : "";
            $split = str_split($latest_message_details[1], 12);
            $split = $split[0] . $dots;

            $return_string .= " <a href='messages.php?u=$username'> 
                                    <div class='user_found_messages' style='" . $style . "'>
                                        <img src='" . $user_found_obj->getProfilePic() . "' style='border-radius: 25px; margin-right: 5px;'>
                                        " . $user_found_obj->getFirstAndLastName() . " <br>
                                        <span class='timestamp_smaller' id='grey'>" . $latest_message_details[2] . " </span> <br> <br>
                                        <p id='grey' style='margin: 0;'> " . $latest_message_details[0] . $split . " </p> 
                                    </div>
                                </a>";

        }

        // If posts were loaded
        if($count > $limit) {

            $return_string .= "<input type='hidden' class='nextPageDropdownData' value='" . ($page + 1) . "'> <input type='hidden' class='noMoreDropdownData' value='false'>";

        }   else    {

            $return_string .= "<input type='hidden' class='noMoreDropdownData' value='true'> <p style='text-align: center;'> No more messages to load </p> ";

        }


        return $return_string;

    }   // End of getConvosDropdown function


    public function getUnreadNumber() {
        // Finds number of unread messages the user has for notification display

        $userLoggedIn = $this->user_obj->getUsername();
        $query = mysqli_query($this->con, "SELECT * FROM messages WHERE user_to = '$userLoggedIn' AND viewed = 'no'");

        return mysqli_num_rows($query);

    }   // End of getUnreadNumber function

    
}   // End of class

?>