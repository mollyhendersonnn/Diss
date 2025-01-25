<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
include_once("connection.php");
include_once("navigation.php");  // Make sure this file contains your navigation bar
//include("audit.php");


// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo "<p>You must be logged in to create an event.</p>";
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $eventTitle = htmlspecialchars(trim($_POST["eventTitle"]));
    $eventType = $_POST["eventType"];
    $eventDescription = htmlspecialchars(trim($_POST["eventDescription"]));
    $eventStart = $_POST["eventStart"];
    $eventEnd = $_POST["eventEnd"];
    $userID = $_SESSION["userID"];
    $groupID = $_SESSION["groupID"];

    $currentDate = date("Y-m-d H:i:s");
    $stateID = ($eventStart > $currentDate) ? 1 : 2;

    // Handling file upload
    $imageContent = null;
    if (isset($_FILES["eventFile"]) && $_FILES["eventFile"]["error"] == 0) {
        $imageContent = file_get_contents($_FILES["eventFile"]["tmp_name"]);
    }

    // Prepare the SQL query
    $query = "INSERT INTO tbl_events (eventFile, userID, groupID, stateID, eventTitle, eventType, eventDescription, eventStart, eventEnd) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = mysqli_prepare($link, $query)) {
        // Bind parameters and execute the query
        mysqli_stmt_bind_param($stmt, "siissssss", $imageContent, $userID, $groupID, $stateID, $eventTitle, $eventType, $eventDescription, $eventStart, $eventEnd);

        if (mysqli_stmt_execute($stmt)) {
            // Audit action and success response
            //auditAction("Created event: $eventTitle");
            echo "<p>Event created successfully.</p>";
        } else {
            echo "<p>Error executing query: " . mysqli_error($link) . "</p>";  // Show SQL error
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "<p>Database query preparation failed: " . mysqli_error($link) . "</p>"; // Show database connection error
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet"> 
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
     <div class="container mt-5">
        <h2>Create Event</h2>
            <form action="createEvent.php" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="eventTitle" class="form-label">Event Title</label>
                    <input type="text" class="form-control" id="eventTitle" name="eventTitle" required>
                </div>
                <div class="mb-3">
                    <label for="eventType" class="form-label">Event Type</label>
                    <input type="text" class="form-control" id="eventType" name="eventType" required>
                </div>
                <div class="mb-3">
                    <label for="eventDescription" class="form-label">Event Description</label>
                    <textarea class="form-control" id="eventDescription" name="eventDescription" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="eventStart" class="form-label">Start Date and Time</label>
                    <input type="datetime-local" class="form-control" id="eventStart" name="eventStart" required>
                </div>
                <div class="mb-3">
                    <label for="eventEnd" class="form-label">End Date and Time</label>
                    <input type="datetime-local" class="form-control" id="eventEnd" name="eventEnd" required>
                </div>
                <div class="mb-3">
                    <label for="eventFile" class="form-label">Event File</label>
                    <input type="file" class="form-control" id="eventFile" name="eventFile">
                </div>
                <button type="submit" class="btn btn-primary">Create Event</button>
            </form>
        
    </div>
</body>
</html>
