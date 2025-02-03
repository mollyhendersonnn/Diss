<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("../connection.php");
include_once("../navigation.php");

//Check the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["success" => false, "message" => "Please log-in to update events."]);
    exit;
}

//Check eventID is there
if (!isset($_GET['eventID'])) {
    echo json_encode(["success" => false, "message" => "Error getting the EventID."]);
    exit;
}

//Store the eventID as a variable
$eventID = $_GET['eventID'];

//Get all the event details from the tbl_events table
$query = "SELECT * FROM tbl_events WHERE eventID = ?";
if ($stmt = mysqli_prepare($link, $query)) {
    mysqli_stmt_bind_param($stmt, "i", $eventID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $event = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["success" => false, "message" => "Failed to get all the events."]);
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $eventTitle = htmlspecialchars(trim($_POST["eventTitle"]));
    $eventType = $_POST["eventType"];
    $eventDescription = htmlspecialchars(trim($_POST["eventDescription"]));
    $eventStart = $_POST["eventStart"];
    $eventEnd = $_POST["eventEnd"];

    // Variables for file content and MIME type
    $imageContent = null;
    $mimeType = null;

    // Check if a new file was uploaded
    if (isset($_FILES["eventFile"]) && $_FILES["eventFile"]["error"] == 0) {
        $fileTmpPath = $_FILES["eventFile"]["tmp_name"];
        $imageContent = file_get_contents($fileTmpPath);
        $mimeType = mime_content_type($fileTmpPath); // Detect the file type
    } else {
        // If no new file is uploaded, retain the existing file and MIME type
        $imageContent = $event['eventFile'];
        $mimeType = $event['fileType'];
    }

    // Validate date and time
    if (strtotime($eventEnd) <= strtotime($eventStart)) {
        echo '<p class="alert alert-danger">End Date or Time in the past</p>';
    } else {
        // Update the event details in the database
        $query = "UPDATE tbl_events SET eventFile = ?, fileType = ?, eventTitle = ?, eventType = ?, eventDescription = ?, eventStart = ?, eventEnd = ? WHERE eventID = ?";
        if ($stmt = mysqli_prepare($link, $query)) {
            mysqli_stmt_bind_param($stmt, "sssssssi", $imageContent, $mimeType, $eventTitle, $eventType, $eventDescription, $eventStart, $eventEnd, $eventID);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Event updated successfully!";
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
    <title>Update Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Update Event</h2>
        <form action="updateEvent.php?eventID=<?php echo $eventID; ?>" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="eventTitle" class="form-label">Event Title</label>
                <input type="text" class="form-control w-50" id="eventTitle" name="eventTitle" value="<?php echo htmlspecialchars($event['eventTitle']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="eventType" class="form-label">Event Type</label>
                <select class="form-control w-50" id="eventType" name="eventType" required>
                    <option value="" disabled>Select an Event Type</option>
                    <option value="Lunch and Learns" <?php echo $event['eventType'] == "Lunch and Learns" ? "selected" : ""; ?>>Lunch & Learns</option>
                    <option value="Town Halls" <?php echo $event['eventType'] == "Town Halls" ? "selected" : ""; ?>>Town Halls</option>
                    <option value="Party" <?php echo $event['eventType'] == "Party" ? "selected" : ""; ?>>Party</option>
                    <option value="Newsletter" <?php echo $event['eventType'] == "Newsletter" ? "selected" : ""; ?>>Newsletter</option>
                    <option value="All Hands" <?php echo $event['eventType'] == "All Hands" ? "selected" : ""; ?>>All Hands</option>
                    <option value="Brownbag sessions" <?php echo $event['eventType'] == "Brownbag sessions" ? "selected" : ""; ?>>Brownbag sessions</option>
                    <option value="Podcast" <?php echo $event['eventType'] == "Podcast" ? "selected" : ""; ?>>Podcast</option>
                    <option value="Webcast" <?php echo $event['eventType'] == "Webcast" ? "selected" : ""; ?>>Webcast</option>
                    <option value="Other" <?php echo $event['eventType'] == "Other" ? "selected" : ""; ?>>Other</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="eventDescription" class="form-label">Event Description</label>
                <textarea class="form-control w-50" id="eventDescription" name="eventDescription" rows="3" required><?php echo htmlspecialchars($event['eventDescription']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="eventStart" class="form-label">Start Date and Time</label>
                <input type="datetime-local" class="form-control w-auto" id="eventStart" name="eventStart" value="<?php echo date('Y-m-d\TH:i', strtotime($event['eventStart'])); ?>" required>
            </div>
            <div class="mb-3">
                <label for="eventEnd" class="form-label">End Date and Time</label>
                <input type="datetime-local" class="form-control w-auto" id="eventEnd" name="eventEnd" value="<?php echo date('Y-m-d\TH:i', strtotime($event['eventEnd'])); ?>" required>
            </div>
            <div class="mb-3">
                <label for="eventFile" class="form-label">Event File</label>
                <input type="file" class="form-control w-50" id="eventFile" name="eventFile">
            </div>
            <button type="submit" class="btn btn-primary">Update Event</button>
        </form>
    </div>
</body>
</html>
