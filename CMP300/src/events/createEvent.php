<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("../connection.php");
include_once("../navigation.php");

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo '<p class="fail-message">You must be logged in to create an event.</p>';
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

    // Initialize variables for file content and MIME type
    $imageContent = null;
    $mimeType = null;

    // Handling file upload
    if (isset($_FILES["eventFile"]) && $_FILES["eventFile"]["error"] == 0) {
        $fileTmpPath = $_FILES["eventFile"]["tmp_name"];
        $imageContent = file_get_contents($fileTmpPath);
        $mimeType = mime_content_type($fileTmpPath); // Detect the file type
    }

    // Validate date and time
    if (strtotime($eventEnd) <= strtotime($eventStart)) {
        echo '<p class="alert alert-danger">End Date or Time in the past</p>';
    } else {
        // Prepare the SQL query
        $query = "INSERT INTO tbl_events (eventFile, fileType, userID, groupID, stateID, eventTitle, eventType, eventDescription, eventStart, eventEnd) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $query)) {
            mysqli_stmt_bind_param($stmt, "ssiiisssss", $imageContent, $mimeType, $userID, $groupID, $stateID, $eventTitle, $eventType, $eventDescription, $eventStart, $eventEnd);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Event created successfully!";
                header("Location: ../dashboard.php");
                exit();
            } else {
                echo "<p>Error executing query: " . mysqli_error($link) . "</p>";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Create Event</h2>
        <form action="createEvent.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="eventTitle" class="form-label">Event Title</label>
                <input type="text" class="form-control w-50" id="eventTitle" name="eventTitle" required>
            </div>
            <div class="mb-3">
                <label for="eventType" class="form-label">Event Type</label>
                <select class="form-control w-50" id="eventType" name="eventType" required>
                    <option value="" selected disabled>Select an Event Type</option>
                    <option value="Lunch and Learns">Lunch & Learns</option>
                    <option value="Town Halls">Town Halls</option>
                    <option value="Party">Party</option>
                    <option value="Newsletter">Newsletter</option>
                    <option value="All Hands">All hands</option>
                    <option value="Brownbag sessions">Brownbag sessions</option>
                    <option value="Podcast">Podcast</option>
                    <option value="Webcast">Webcast</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="eventDescription" class="form-label">Event Description</label>
                <textarea class="form-control w-50" id="eventDescription" name="eventDescription" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="eventStart" class="form-label">Start Date and Time</label>
                <input type="datetime-local" class="form-control w-auto" id="eventStart" name="eventStart" required>
            </div>
            <div class="mb-3">
                <label for="eventEnd" class="form-label">End Date and Time</label>
                <input type="datetime-local" class="form-control w-auto" id="eventEnd" name="eventEnd" required>
            </div>
            <div class="mb-3">
                <label for="eventFile" class="form-label">Event File</label>
                <input type="file" class="form-control w-50" id="eventFile" name="eventFile">
            </div>
            <button type="submit" class="btn btn-primary">Create Event</button>
        </form>
    </div>
</body>
</html>
