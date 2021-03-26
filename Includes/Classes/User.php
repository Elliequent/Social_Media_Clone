<?php

class User {

    // Class Attributes
    private $user;
    private $con;

    public function __construct($con, $user) {
        // Creates constructor based upon the values held within the database
        $this->con = $con;
        $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$user'");
		$this->user = mysqli_fetch_array($user_details_query);
    }


    public function getUsername() {
        // Returns username
        return @$this->user['username'];
    }   // End of getUsername function


    public function getNumPosts() {
        // Returns number of posts of selected user
        $username = $this->user['username'];
		$query = mysqli_query($this->con, "SELECT num_posts FROM users WHERE username='$username'");
		$row = mysqli_fetch_array($query);
		return $row['num_posts'];
    }   // End of getNumPosts function


    public function getFirstAndLastName() {
        // Returns users first name and last name with a space between
        $username = $this->user['username'];
		$query = mysqli_query($this->con, "SELECT first_name, last_name FROM users WHERE username='$username'");
		$row = mysqli_fetch_array($query);
		return $row['first_name'] . " " . $row['last_name'];
    }   // End of getFirstAndLastName function


    public function isClosed() { 
        // Checks if user being posted to account is open (false) or closed (true)
        $username = $this->user['username'];
		$query = mysqli_query($this->con, "SELECT user_closed FROM users WHERE username = '$username'");
		$row = mysqli_fetch_array($query);

        if($row['user_closed'] == 'yes') {                  
            return true;                                        // If user account closed - true
        } else {
            return false;                                       // If user account open - false
        }
    }   // End of isClosed function


    public function isFriend($username_to_check) {
		$usernameComma = "," . $username_to_check . ",";        // Friends stored in database iwth username,username

		if((strstr($this->user['friend_array'], $usernameComma) || $username_to_check == $this->user['username'])) {
			return true;                                        // If friend is found in users database friend array
		}
		else {
			return false;                                       // If friend not found in users database friend array
		}
	}   // End of isFriend function


    public function getProfilePic() {
        // Returns users profile picture
        $username = $this->user['username'];
		$query = mysqli_query($this->con, "SELECT profile_pic FROM users WHERE username='$username'");
		$row = mysqli_fetch_array($query);
        return $row['profile_pic'];
    }   // End of getProfilePic function


    public function getFriendArray() {
        // Returns users friend array
        $username = $this->user['username'];
		$query = mysqli_query($this->con, "SELECT friend_array FROM users WHERE username='$username'");
		$row = mysqli_fetch_array($query);
        return $row['friend_array'];
    }   // End of getFriendArray function


    public function didReceiveRequest($user_from) {
        // Changes friend request button if the user has received a friend request from the account
        $user_to = $this->user['username'];
        $check_request_query = mysqli_query($this->con, "SELECT * FROM friend_requests WHERE user_to = '$user_to' AND user_from = '$user_from'");

        if(mysqli_num_rows($check_request_query) > 0) {
            return true;                                            // Person has requested to be friends with user
        } else {
            return false;                                           // Person has not requested to be friends with user
        }
    }   // End of didReceiveRequest function


    public function didSendRequest($user_to) {
        // Changes friend request button if the user has received a friend request from the account
        $user_from = $this->user['username'];
        $check_request_query = mysqli_query($this->con, "SELECT * FROM friend_requests WHERE user_to = '$user_to' AND user_from = '$user_from'");

        if(mysqli_num_rows($check_request_query) > 0) {
            return true;                                            // Person has requested to be friends with user
        } else {
            return false;                                           // Person has not requested to be friends with user
        }
    }   // End of didSendRequest function


    public function removeFriend($user_to_remove) {
        $logged_in_user = $this->user['username'];

        $query = mysqli_query($this->con, "SELECT friend_array FROM users WHERE username = '$logged_in_user'");
        $row = mysqli_fetch_array($query);
        $friend_array_username = $row['friend_array'];                          // Users friend array

        $friend_query = mysqli_query($this->con, "SELECT friend_array FROM users WHERE username = '$user_to_remove'");
        $friend_row = mysqli_fetch_array($friend_query);
        $friend_array_friend = $friend_row['friend_array'];                     // Friends friend array

        // Finds friend "username," and replaces it with blank string - removes friend from users friend array
        $new_friend_array = str_replace($user_to_remove . ",", "", $this->user['friend_array']);
		$remove_friend = mysqli_query($this->con, "UPDATE users SET friend_array = '$new_friend_array' WHERE username = '$logged_in_user'");

        // Finds friend "username," and replaces it with blank string - removes friend from user_to friend array
        $new_friend_array = str_replace($this->user['username'] . ",", "", $friend_array_friend);
		$remove_friend = mysqli_query($this->con, "UPDATE users SET friend_array = '$new_friend_array' WHERE username = '$user_to_remove'");
    }   // End of removeFriend function


    public function sendRequest($user_to) {
        $user_from = $this->user['username'];
        $query = mysqli_query($this->con, "INSERT INTO friend_requests VALUES ('', '$user_to', '$user_from')");
    }   // End of sendRequest function


    public function getMutualFriends($user_to_check) {
        // Calculates the number of mutual friends when chekcing another users profile

        $mutualFriends = 0;
        $user_array = $this->user['friend_array'];                          // Gets current users friend array
        $user_array_explode = explode(",", $user_array);                    // Splits array where character is long

        $query = mysqli_query($this->con, "SELECT friend_array FROM users WHERE username = '$user_to_check'");
        $row = mysqli_fetch_array($query);
        @$user_to_check_array = $row['friend_array'];                        // Gets users being checked friend array
        $user_to_check_array_explode = explode(",", $user_to_check_array);

        foreach($user_to_check_array_explode as $i) {                       // Compares both friends array against each other looking for matches

            foreach($user_to_check_array_explode as $j) {

                if($i == $j && $i != "") {                                  // If user has the same friend as friend +1 (Expect where blank)
                    $mutualFriends++;
                }

            }

        }

        return $mutualFriends;

    }   // End of getMutualFriends function


    public function getNumberOfFriendRequests() {
         // Returns number of friend requests of selected user

         $username = $this->user['username'];
         $query = mysqli_query($this->con, "SELECT * FROM friend_requests WHERE user_to = '$username'");
         return mysqli_num_rows($query);

    }   // End of getNumberOfFriendRequests function

}   // End of class

?>