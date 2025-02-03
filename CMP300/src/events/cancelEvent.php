<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("../connection.php");
include_once("../navigation.php");  

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["success" => false, "message" => "You must be logged in to cancel an event."]);
    exit;
}

// Check if eventID is provided
if (!isset($_GET['eventID']) || empty($_GET['eventID'])) {
    echo json_encode(["success" => false, "message" => "No event ID provided."]);
    exit;
}

$eventID = intval($_GET['eventID']); // Ensure eventID is an integer
$thisUserID = $_SESSION["userID"];

// Fetch the event details
$query = "SELECT eventID, groupID, userID, eventTitle, eventType, eventDescription, eventStart, eventEnd, numAttendees FROM tbl_events WHERE eventID = ?";
if ($stmt = mysqli_prepare($link, $query)) {
    mysqli_stmt_bind_param($stmt, "i", $eventID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $eventID, $groupID, $userID, $eventTitle, $eventType, $eventDescription, $eventStart, $eventEnd, $numAttendees);


    if (!mysqli_stmt_fetch($stmt)) {
        echo json_encode(["success" => false, "message" => "Event not found."]);
        mysqli_stmt_close($stmt);
        exit;
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["success" => false, "message" => "Failed to fetch event details."]);
    exit;
}

// Archive the event
$query = "INSERT INTO tbl_archive (eventID, stateID, groupID, userID, eventTitle, eventType, eventStart, eventEnd, numAttendees, archiveReason) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
if ($stmt = mysqli_prepare($link, $query)) {
    $stateID = 2; 
    $archiveReason = 2; 

    mysqli_stmt_bind_param($stmt, "iiiissssii", $eventID, $stateID, $groupID, $userID, $eventTitle, $eventType, $eventStart, $eventEnd, $numAttendees, $archiveReason);

    if (mysqli_stmt_execute($stmt)) {
    //    Delete the event from tbl_events
        $deleteQuery = "DELETE FROM tbl_events WHERE eventID = ?";
        if ($deleteStmt = mysqli_prepare($link, $deleteQuery)) {
            mysqli_stmt_bind_param($deleteStmt, "i", $eventID);
            if (mysqli_stmt_execute($deleteStmt)) {
                $_SESSION['success_message'] = "Event Archived and Deleted Successfully!";
                header("Location: ../dashboard.php");
                exit();
            //   echo json_encode(["success" => true, "message" => "Event archived and deleted successfully."]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to delete the event."]);
            }
            mysqli_stmt_close($deleteStmt);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to prepare delete query."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Failed to archive the event."]);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["success" => false, "message" => "Failed to prepare archive query."]);
}

?>
