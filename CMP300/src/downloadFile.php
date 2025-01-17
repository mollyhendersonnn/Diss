<?php
// Start session
session_start();

// Include the connection file
include_once("connection.php");

// Ensure error reporting is enabled for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if eventID is passed
if (isset($_GET['eventID'])) {
    $eventID = $_GET['eventID'];

    // Fetch the event file from the database
    $query = "SELECT eventFile, eventFileName FROM tbl_events WHERE eventID = ?";
    if ($stmt = mysqli_prepare($link, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $eventID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        mysqli_stmt_bind_result($stmt, $fileContent, $fileName);

        if (mysqli_stmt_fetch($stmt)) {
            // Set the headers for file download
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Content-Length: ' . strlen($fileContent));

            // Output the file content
            echo $fileContent;
        } else {
            echo "File not found.";
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "Error retrieving file.";
    }
} else {
    echo "No event specified.";
}

mysqli_close($link);
?>
