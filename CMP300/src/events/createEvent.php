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
    //$eventType = "Event Type goes here";
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
    if (strtotime(datetime: $eventEnd) <= strtotime($eventStart)) {
        echo '<p class="alert alert-danger">End Date or Time in Past</p>';
    } else {
        $query = "INSERT INTO tbl_events (eventFile, userID, groupID, stateID, eventTitle, eventType, eventDescription, eventStart, eventEnd) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = mysqli_prepare($link, $query)) {
        // Bind parameters and execute the query
        mysqli_stmt_bind_param($stmt, "siissssss", $imageContent, $userID, $groupID, $stateID, $eventTitle, $eventType, $eventDescription, $eventStart, $eventEnd);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Event Updated Successfully!";
            header(header: "Location: ../dashboard.php");
            exit();
            // echo '<p class="success-message">Event created successfully.</p>"';
         } else {
            echo "<p>Error executing query: " . mysqli_error($link) . "</p>";  // Show SQL error
         }
         mysqli_stmt_close($stmt);
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $selected = $_POST['dropdown'];
   // echo "You selected: " . htmlspecialchars($selected);
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
        <br>
            <form action="createEvent.php" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="eventTitle" class="form-label">Event Title</label>
                    <input type="text" class="form-control w-50" id="eventTitle" name="eventTitle" required>
                </div>
                <div class="mb-3">
                    <label for="eventType" class="form-label">Event Type</label>
                    <select type="text" class="form-control w-50" id="eventType" value="" name="eventType" required="">
                    <option value="" selected="" disabled="">Select an Event Type</option>
                    <option value="Lunch and Learns">Lunch & Learns</option>
                    <option value="Town Halls">Town Halls</option>
                    <option value="Holiday Party">Holiday Party</option>
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