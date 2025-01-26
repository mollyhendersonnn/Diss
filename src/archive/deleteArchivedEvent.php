<?php

// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("../connection.php");
//include_once("../navigation.php");  

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["success" => false, "message" => "You must be logged in to delete an archived event."]);
    exit;
}

// Check if eventID is provided
if (!isset($_GET['eventID']) || empty($_GET['eventID'])) {
    echo json_encode(["success" => false, "message" => "No event ID provided."]);
    exit;
}

$eventID = intval($_GET['eventID']); // Ensure eventID is an integer

// Delete the event from tbl_archive
$query = "DELETE FROM tbl_archive WHERE eventID = ?";
if ($stmt = mysqli_prepare($link, $query)) {
    mysqli_stmt_bind_param($stmt, "i", $eventID);

    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo json_encode(["success" => true, "message" => "Archived event deleted successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Event not found or already deleted."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete the archived event."]);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["success" => false, "message" => "Failed to prepare delete query."]);
}

?>
