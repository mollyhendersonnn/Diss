<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("connection.php");

// Keep just one debug line to help us monitor session state
if (!function_exists('writeLog')) {
    function writeLog($message) {
        $logFile = __DIR__ . '/debug.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    }
}
writeLog("Navigation - Session data: " . print_r($_SESSION, true));
?>

<div class="navigation">
    <ul>
        <li><a href="dashboard.php">Home</a></li>
        <li><a href="calendar.php">Calendar</a></li>
        <?php
        // Check if the user is logged in and session variables exist
        if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && isset($_SESSION["roleID"])) {
            // Show options for Role ID 1
            if ($_SESSION["roleID"] == 1) {
                echo '<li><a href="createUser.php">Create User</a></li>';
                echo '<li><a href="audit.php">Audit</a></li>';
                echo '<li><a href="archive.php">Archive</a></li>';
            }
            // Show options for Role ID 2
            elseif ($_SESSION["roleID"] == 2) {
                echo '<li><a href="archive.php">Archive</a></li>';
            }
            // Logout option for all logged-in users
            echo '<li><a href="logout.php">Logout</a></li>';
        } else {
            // Show login option for guests
            echo '<li><a href="login.php">Login</a></li>';
        }
        ?>
    </ul>
</div> 