<?php
//Initialise the session
session_start();

//unset session variables
$_SESSION = array();

//Destroy the session
session_destroy();

//Redirect to login page
header("location: login.php");
exit
    ?>