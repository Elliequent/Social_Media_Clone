<?php

include("../../Config/config.php");
include("../Classes/User.php");
include("../Classes/Message.php");

$limit = 5;

$message = new Message($con, $_REQUEST['userLoggedIn']);

echo $message->getConvosDropdown($_REQUEST, $limit);

?>