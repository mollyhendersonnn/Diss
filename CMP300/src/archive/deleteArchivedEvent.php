<?php
//start the session if there isnt one detected
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("../connection.php");
include_once("../clean.php");

//is user logged in in the session
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["success" => false, "message" => "You must be logged in to delete an archived event."]);
    exit;
}

//is eventID in URL
if (!isset($_GET['eventID']) || empty($_GET['eventID'])) {
    echo json_encode(["success" => false, "message" => "No event ID provided."]);
    exit;
}

//makes sure eventID is an int
$eventID = intval($_GET['eventID']);

//deletes from the table based on eventID
$query = "DELETE FROM tbl_archive WHERE eventID = ?";
if ($stmt = mysqli_prepare($link, $query)) {
    mysqli_stmt_bind_param($stmt, "i", $eventID);

    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            $_SESSION['success_message'] = "Event Deleted Successfully!";
            header("Location: archive.php");
            exit();
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
