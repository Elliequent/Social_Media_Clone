<?php

// Page displays results from ajax_search.php - user enters q in search bar on navbar

include("Includes/header.php");

// Stores user query
if(isset($_GET['q'])) {

    $query = $_GET['q'];

}   else    {

    $query = "";

}

// Stores query type - username, last, first name, etc
if(isset($_GET['type'])) {

    $type = $_GET['type'];

}   else    {

    $type = "name";

}

?>

<div class="main_column column" id='main_column'>

    <?php

        if($query == "") { 

            // If query empty
            echo "You must enter something in the search box";

        }   else    {

            // Copy / pasted from ajax_search.php

            // Search check - if person searching uses "_" they are likely looking for a username
            if($type == "username") {

                $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE username LIKE '$query%' AND user_closed = 'no'");

            // Search check - if person searching uses two words, assume they are first and last names
            }   else    {

                $names = explode(" ", $query);
            
                if(count($names) == 3) {

                    // User is looking for first, middle and second name
                    $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[2]%' ) AND user_closed = 'no'");
    
                }   else if (count($names) == 2)  {

                    // User is looking for first and second names
                    $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[1]%' ) AND user_closed = 'no'");
    
                }   else    {

                    // User is looking for first or second name
                    $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' OR last_name LIKE '$names[0]%' ) AND user_closed = 'no'");
    
                }
    
                // Check if results were found
                if(mysqli_num_rows($usersReturnedQuery) == 0) {

                    echo "We can't find anyone with a " . $type . " like " . $query;

                }   else    {

                    echo mysqli_num_rows($usersReturnedQuery) . " results found: <br> <br>";

                    echo "<hr id='search_hr'> ";

                }

                while ($row = mysqli_fetch_array($usersReturnedQuery)) {

                    $user_obj = new User($con, $user['username']);

                    $button = "";
                    $mutual_friends = "";

                    if ($user['username'] != $row['username']) {

                        // Generate button depending on friendship status
                        if ($user_obj->isFriend($row['username'])) {

                            // if user is already friends with this person
                            $button = " <input type='submit' name='" . $row['username'] . "' class='danger' value='Remove Friend'> ";

                        }   else if ($user_obj->didReceiveRequest($row['username']))    {

                            // If user has recieved a friend request from this person
                            $button = " <input type='submit' name='" . $row['username'] . "' class='warning' value='Respond to Request'> ";

                        }   else if ($user_obj->didSendRequest($row['username'])) {

                            // If the user has sent a friend request to this person
                            $button = " <input type='submit' class='default' value='Request sent'> ";

                        }   else    {

                            // If the user is not a friend or has sent or recieved no friend requests to this person
                            $button = " <input type='submit' name='" . $row['username'] . "' class='success' value='Add Friend'> ";

                        }

                        $mutual_friends = $user_obj->getMutualFriends($row['username']) . " friends in common";

                        // Button forms
                        if (isset($_POST[$row['username']])) {

                            /* Each button created in the search results adds the username as a name / id
                            to call each individual the $_POST looks for usernames called  */

                            if ($user_obj->isFriend($row['username'])) {

                                // If user is friend with person
                                $user_obj->removeFriend($row['username']);
                                // Refreshes the window when pressing button to update new button status
                                header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

                            }   else if ($user_obj->didReceiveRequest($row['username'])) {

                                // If user has a friend request from person
                                header("Location: requests.php");

                            }   else if ($user_obj->didSendRequest($row['username'])) {

                                // if user sent a friend request

                            }   else    {

                                // If user is not currently friends or has not sent or recieved a request from this person
                                $user_obj->sendRequest($row['username']);
                                header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

                            }


                        }


                    }   // End of if ($user['username'] != $row['username'])

                    echo "  <div class='search_result'>
                                <div class='searchPageFriendButton'>
                                    <form action='' method='POST'>

                                        " . $button . "
                                        <br>

                                    </form>
                                </div>

                                <div class='result_profile_pic'>

                                    <a href=' " . $row['username'] . " '> <img src=' " . $row['profile_pic'] . " ' style='height: 100px;'> </a>

                                </div>

                                <a href=' " . $row['username'] . " '> " . $row['first_name'] . " " . $row['last_name'] . " 
                                    <p id='grey'> " . $row['username'] . " </p>
                                </a>
                                <br>
                                " . $mutual_friends . " <br>

                            </div>
                            <hr id='search_hr'>";

                }   // End of while ($row === mysqli_fetch_array($usersReturnedQuery))

            }   // Of Else ($type == "username")
            
        }   // End of else ($query == "")

    ?>

</div>