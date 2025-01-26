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
    echo json_encode(["success" => false, "message" => "You must be logged in to update an event."]);
    exit;
}

// Check if eventID is provided
if (!isset($_GET['eventID'])) {
    echo json_encode(["success" => false, "message" => "No event ID provided."]);
    exit;
}

$eventID = $_GET['eventID'];

// Fetch the event details
$query = "SELECT * FROM tbl_events WHERE eventID = ?";
if ($stmt = mysqli_prepare($link, $query)) {
    mysqli_stmt_bind_param($stmt, "i", $eventID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $event = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["success" => false, "message" => "Failed to fetch event details."]);
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

    // Handling file upload
    $imageContent = $event['eventFile'];
    if (isset($_FILES["eventFile"]) && $_FILES["eventFile"]["error"] == 0) {
        $imageContent = file_get_contents($_FILES["eventFile"]["tmp_name"]);
    }

    // Prepare the SQL query to update the event
    $query = "UPDATE tbl_events SET eventFile = ?, eventTitle = ?, eventType = ?, eventDescription = ?, eventStart = ?, eventEnd = ? WHERE eventID = ?";

    if ($stmt = mysqli_prepare($link, $query)) {
        mysqli_stmt_bind_param($stmt, "ssssssi", $imageContent, $eventTitle, $eventType, $eventDescription, $eventStart, $eventEnd, $eventID);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>
            window.location.href = 'dashboard.php';
            </script>";
        } 
        echo '<p class="success-message">Event Updated Successfully!</p>';

           // auditAction($userID, "Updated event with ID: $eventID");
        //    echo "<script>
        //    alert('Event updated successfully.');
        //    window.location.href = 'dashboard.php'; // Redirect after showing the message
        //  </script>";
        //     exit;
        // } else {
        //     echo json_encode(["success" => false, "message" => "Failed to update event."]);
        // }

        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(["success" => false, "message" => "Database query preparation failed."]);
    }
} else {
    // Display the form if the request method is not POST
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Update Event</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href=css/styles.css rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <h2>Update Event</h2>
            <br>
            <form action="updateEvent.php?eventID=<?php echo $eventID; ?>" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="eventTitle" class="form-label">Event Title</label>
                    <input type="text" class="form-control w-50" id="eventTitle" name="eventTitle" value="<?php echo htmlspecialchars($event['eventTitle']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="eventType" class="form-label">Event Type</label>
                    <select type="text" class="form-control w-50" id="dropdown" value="" name="dropdown" required="">
                    <option value="" selected="" disabled="">Select an Event Type</option>
                    <option value="option1">Lunch & Learns</option>
                    <option value="option2">Town Halls</option>
                    <option value="option3">Holiday Party</option>
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
    <?php
}
?>