<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include_once("connection.php");
include_once("navigation.php");
?>


<!DOCTYPE html>
<html lang="en" xmlns:mso="urn:schemas-microsoft-com:office:office"
    xmlns:msdt="uuid:C2F41010-65B3-11d1-A29F-00AA00C14882">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template 2</title>
    <link rel="stylesheet" href="css/styles.css">


</head>

<body>

<div class="dashboard">
    <ul>
        <li>

        </li>
    </ul>
</div>
</body>

</html>





when event is clciked on to bring up details, in this ppl can edit and delete --
