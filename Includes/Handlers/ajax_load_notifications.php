<?php

include("../../Config/config.php");
include("../Classes/User.php");
include("../Classes/Notification.php");

$limit = 5;

$notification = new Notification($con, $_REQUEST['userLoggedIn']);

echo $notification->getNotifications($_REQUEST, $limit);

?>