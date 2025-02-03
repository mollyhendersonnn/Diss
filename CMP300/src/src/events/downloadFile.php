<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("../connection.php");

// Ensure error reporting is enabled for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if eventID is passed
if (isset($_GET['eventID'])) {
    $eventID = $_GET['eventID'];

    // Fetch the event file and MIME type from the database
    $query = "SELECT eventFile, fileType FROM tbl_events WHERE eventID = ?";
    if ($stmt = mysqli_prepare($link, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $eventID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        mysqli_stmt_bind_result($stmt, $fileContent, $fileType);

        if (mysqli_stmt_fetch($stmt)) {
            // Default to a binary file if no MIME type is provided
            $fileType = $fileType ?: 'application/octet-stream';

            // Set the headers for file download
            header("Content-Type: $fileType");
            header("Content-Disposition: attachment; filename=\"downloaded_file.png\""); // Adjust the filename if needed
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
