<?php
//start the session if there isnt one detected
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("../connection.php");
include_once("../clean.php"); 

//check for eventID
if (isset($_GET['eventID'])) {
    $eventID = $_GET['eventID'];

    //get the event file and mime type from the database
    $query = "SELECT eventFile, fileType FROM tbl_events WHERE eventID = ?";
    if ($stmt = mysqli_prepare($link, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $eventID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        mysqli_stmt_bind_result($stmt, $fileContent, $fileType);

        if (mysqli_stmt_fetch($stmt)) {
            //if no mime type found default to binary
            $fileType = $fileType ?: 'application/octet-stream';

            //set the headers for file download
            header("Content-Type: $fileType");
            header("Content-Disposition: attachment; filename=\"downloaded_file.png\"");
            header('Content-Length: ' . strlen($fileContent));

            //dowload the file
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
