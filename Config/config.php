<?php  

ob_start();                                                     // Starts output buffering - improves php efficiency

session_start();                                                // Store session date

$timezone = date_default_timezone_set("Europe/London");         // Set timezone to Europe - London GMT

$con = mysqli_connect("localhost", "root", "", "social");  		// IP Address, username, password and database table

if(mysqli_connect_errno())	                                    // If connection to mySQL server does not work display an error message
{
	echo "Failed to connect: " . mysqli_connect_errno();
}

?>