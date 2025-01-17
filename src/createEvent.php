<?php
session_start();
include_once("connection.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        echo json_encode(["success" => false, "message" => "You must be logged in to create an event."]);
        exit;
    }

    $eventTitle = htmlspecialchars(trim($_POST["eventTitle"]));
    $eventType = $_POST["eventType"];
    $eventDescription = htmlspecialchars(trim($_POST["eventDescription"]));
    $eventStart = $_POST["startDateTime"];  // Fix name mismatch here
    $eventEnd = $_POST["endDateTime"];  // Fix name mismatch here
    $userID = $_SESSION["userID"];
    $groupID = $_SESSION["groupID"];

    $currentDate = date("Y-m-d H:i:s");
    $stateID = ($eventStart > $currentDate) ? 1 : 2;

    // Handling file upload
    $imageContent = null;
    if (isset($_FILES["eventFile"]) && $_FILES["eventFile"]["error"] == 0) {
        $imageContent = file_get_contents($_FILES["eventFile"]["tmp_name"]);
    }

    $query = "INSERT INTO tbl_events (eventFile, userID, groupID, stateID, eventTitle, eventType, eventDescription, eventStart, eventEnd) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = mysqli_prepare($link, $query)) {
        // Bind parameters: "siissssss" means string, int, string, etc.
        mysqli_stmt_bind_param($stmt, "siissssss", $imageContent, $userID, $groupID, $stateID, $eventTitle, $eventType, $eventDescription, $eventStart, $eventEnd);

        if (mysqli_stmt_execute($stmt)) {
            // Assuming you have a function `auditAction` to log user actions
            auditAction("Created event: $eventTitle");
            echo json_encode(["success" => true, "message" => "Event created successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to create event."]);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(["success" => false, "message" => "Database query preparation failed."]);
    }
}
?>
