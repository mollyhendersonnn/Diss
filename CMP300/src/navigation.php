//includes
<?php
include("connection.php");
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

<div class="navigation">
    <ul>
        <li><a href="dashboard.php">Home</a></li>
        <li><a href="calendar.php">Calendar</a></li>
        <li>
        <?php
        // Check if the user is logged in
        if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
            // Show options for Role ID 1
            if ($_SESSION["roleId"] == 1) {
                echo '<li><a href="createUser.php">Create User</a></li>';
                echo '<li><a href="audit.php">Audit</a></li>';
                echo '<li><a href="archive.php">Archive</a></li>';
            }
            // Show options for Role ID 2
            elseif ($_SESSION["roleId"] == 2) {
                echo '<li><a href="archive.php">Archive</a></li>';
            }
            // Logout option for all logged-in users
            echo '<li><a href="logout.php">Logout</a></li>';
        } else {
            // Show login option for guests
            echo '<li><a href="login.php">Login</a></li>';
        }
        ?>
        </li>
    </ul>
</div>
</body>

</html>

