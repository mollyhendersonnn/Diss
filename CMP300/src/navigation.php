<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("connection.php");

?>

<div class="header-banner">
    <img src="css/images/Acc_Logo_All_White_RGB.png" alt="Logo" class="logo">
    <button class="btn btn-secondary mb-3" onclick="history.back()">Back</button>
</div>


<div class="navigation">
    <ul>
        <li><a href="dashboard.php">HOME</a></li>
        <li><a href="calendar.php">CALENDAR</a></li>
        <?php
        // Check if the user is logged in and session variables exist
        if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && isset($_SESSION["roleID"])) {
            // Show options for Role ID 1
            if ($_SESSION["roleID"] == 1) {
                echo '<li><a href="createUser.php">USER</a></li>';
                echo '<li><a href="audit.php">AUDIT</a></li>';
                echo '<li><a href="archive.php">HISTORICAL EVENT</a></li>';
            }
            // Show options for Role ID 2
            elseif ($_SESSION["roleID"] == 2) {
                echo '<li><a href="archive.php">HISTORICAL EVENTS</a></li>';
            }
            // Logout option for all logged-in users
            echo '<li><a href="logout.php">LOGOUT</a></li>';
        } else {
            // Show login option for guests
            echo '<li><a href="login.php">LOGIN</a></li>';
        }
        ?>
    </ul>
</div> 