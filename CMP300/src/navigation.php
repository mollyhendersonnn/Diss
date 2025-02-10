<?php
//start the session if there isnt one detected
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("connection.php");
?>

<!-- banner -->
<div class="header-banner">
    <img src="/mollyhenderson/CMP300/src/css/images/Acc_Logo_All_Black_RGB.png" alt="Accenture Logo" class="logo">
</div>

<!-- navigation bar -->
<div class="navigation">
    <ul>
        <li><a href="/mollyhenderson/CMP300/src/dashboard.php">HOME</a></li>
        <li><a href="/mollyhenderson/CMP300/src/calendar.php">CALENDAR</a></li>
        <?php
        //check if the user is logged in and session variables exist
        if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && isset($_SESSION["roleID"])) {
            //show options for admin
            if ($_SESSION["roleID"] == 1) {
                echo '<li><a href="/mollyhenderson/CMP300/src/users/users.php">USER</a></li>';
              //  echo '<li><a href="/mollyhenderson/CMP300/src/audit/audit.php">AUDIT</a></li>';
                echo '<li><a href="/mollyhenderson/CMP300/src/archive/archive.php">HISTORICAL EVENTS</a></li>'; }
            //show options for employee
            elseif ($_SESSION["roleID"] == 2) {
                echo '<li><a href="/mollyhenderson/CMP300/src/archive/archive.php">HISTORICAL EVENTS</a></li>'; }
            // Logout option for all logged-in users
            echo '<li><a href="/mollyhenderson/CMP300/src/core/logout.php">LOGOUT</a></li>'; } else {
            //show login option for guests
            echo '<li><a href="/mollyhenderson/CMP300/src/core/login.php">LOGIN</a></li>'; }
        ?>
    </ul>
    <button class="btn btn-secondary mb-3" style="right: 30px;" onclick="history.back()">
    <img src="/mollyhenderson/CMP300/src/css/images/Acc_Back_Icon_Black_RGB.png" alt="Back button" style="height: 20px; width: 20px;"/>
</button>
</div> 