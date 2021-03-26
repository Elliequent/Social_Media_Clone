<?php

// When logout button is pressed

session_start();                                            // Begins a new session (deleting old data)
session_destroy();                                          // Ends new session

header("Location: ../../register.php");                     // Redirects to register page to relogin

?>