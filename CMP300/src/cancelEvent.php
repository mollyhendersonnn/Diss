

<?php


// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include_once("connection.php");
include_once("navigation.php");

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["success" => false, "message" => "You must be logged in to cancel an event."]);
    exit;
}

// Check if eventID is provided
if (!isset($_GET['eventID'])) {
    echo json_encode(["success" => false, "message" => "No event ID provided."]);
    exit;
}

$eventID = $_GET['eventID'];
$userID = $_SESSION["userID"];

// Fetch the event details
$query = "SELECT * FROM tbl_events WHERE eventID = ?";
if ($stmt = mysqli_prepare($link, $query)) {
    mysqli_stmt_bind_param($stmt, "i", $eventID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $eventID, $eventTitle, $eventType, $eventDescription, $eventStart, $eventEnd, $userID, $groupID);
    mysqli_stmt_fetch($stmt);
    $event = [
        'eventID' => $eventID,
        'eventTitle' => $eventTitle,
        'eventType' => $eventType,
        'eventDescription' => $eventDescription,
        'eventStart' => $eventStart,
        'eventEnd' => $eventEnd,
        'userID' => $userID,
        'groupID' => $groupID,
        'archiveReason' => $archiveReason = 2
    ];
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["success" => false, "message" => "Failed to fetch event details."]);
    exit;
}


// Insert the event into tbl_archive
$query = "INSERT INTO tbl_archive (eventID, eventTitle, eventType, eventDescription, eventStart, eventEnd, userID, groupID, archiveReason) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
if ($stmt = mysqli_prepare($link, $query)) {
    mysqli_stmt_bind_param($stmt, "isssssiii", $event['eventID'], $event['eventTitle'], $event['eventType'], $event['eventDescription'], $event['eventStart'], $event['eventEnd'], $event['userID'], $event['groupID'], $event['archiveReason']);
    if (mysqli_stmt_execute($stmt)) {
        // Delete the archived events from tbl_events
        $deleteQuery = "DELETE FROM tbl_events WHERE eventEnd < ? AND stateID = 1";
    
        if ($stmt = mysqli_prepare($link, $deleteQuery)) {
            mysqli_stmt_bind_param($stmt, "s", $currentDate);
            if (!mysqli_stmt_execute($stmt)) {
                echo "Error deleting events: " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "Error preparing delete query: " . mysqli_error($link);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Failed to archive event."]);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["success" => false, "message" => "Database query preparation failed."]);
}
?>