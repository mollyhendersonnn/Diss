<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("../connection.php");
include_once("../navigation.php");  

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["success" => false, "message" => "You must be logged in to update an archived event."]);
    exit;
}

// Check if eventID is provided
if (!isset($_GET['eventID'])) {
    echo json_encode(["success" => false, "message" => "No event ID provided."]);
    exit;
}

$eventID = $_GET['eventID'];

// Fetch the archived event details
$query = "SELECT * FROM tbl_archive WHERE eventID = ?";
if ($stmt = mysqli_prepare($link, $query)) {
    mysqli_stmt_bind_param($stmt, "i", $eventID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $event = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["success" => false, "message" => "Failed to fetch archived event details."]);
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $eventTitle = htmlspecialchars(trim($_POST["eventTitle"]));
    $eventType = $_POST["eventType"];
    $eventStart = $_POST["eventStart"];
    $eventEnd = $_POST["eventEnd"];
    $userID = $_SESSION["userID"];
    $groupID = $_SESSION["groupID"];
    $eventFeedback = htmlspecialchars(trim($_POST["eventFeedback"]));
    $eventCost = htmlspecialchars(trim($_POST["eventCost"]));
    $numAttendees = htmlspecialchars(trim($_POST["numAttendees"]));

    // Prepare the SQL query to update the archived event
    $query= "UPDATE tbl_archive SET eventTitle = ? , eventType = ? , eventStart = ? , eventEnd = ? , eventFeedback = ? , eventCost = ? , numAttendees = ?  WHERE eventID = ?";

    if ($stmt = mysqli_prepare($link, $query)) {
        mysqli_stmt_bind_param($stmt, "sssssssi", $eventTitle, $eventType, $eventStart, $eventEnd, $eventFeedback, $eventCost, $numAttendees, $eventID);

        if (mysqli_stmt_execute($stmt)) {
            //auditAction($userID, "Updated archived event with ID: $eventID");
            echo "<script>
            alert('Event updated successfully.');
            window.location.href = 'archive.php';
          </script>";
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update archived event."]);
        }

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
        <title>Update Archived Event</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../css/styles.css">
    </head>
    <body>
        <div class="container mt-5">
            <h2>Update Archived Event</h2>
            <br>
            <form action="updateArchivedEvent.php?eventID=<?php echo $eventID; ?>" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="eventTitle" class="form-label">Event Title</label>
                    <input type="text" class="form-control w-50" id="eventTitle" name="eventTitle" value="<?php echo htmlspecialchars($event['eventTitle']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="eventType" class="form-label">Event Type</label>
                    <input type="text" class="form-control w-50" id="eventType" name="eventType" value="<?php echo htmlspecialchars($event['eventType']); ?>" required>
                </div>
                <div class="mb-3">
                    <lebel for="eventFeedback" class="form-label">Event Feedback</lebel>
                    <input type="text" class="form-control w-50" id="eventFeedback" name="eventFeedback" value="<?php echo htmlspecialchars($event['eventFeedback']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="eventCost" class="form-label">Event Cost</label>
                    <input type="text" class="form-control w-50" id="eventCost" name="eventCost" value="<?php echo htmlspecialchars($event['eventCost']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="numAttendees" class="form-label">Number of Attendees</label>
                    <input type="text" class="form-control w-50" id="numAttendees" name="numAttendees" value="<?php echo htmlspecialchars($event['numAttendees']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="eventStart" class="form-label">Start Date and Time</label>
                    <input type="datetime-local" class="form-control w-auto" id="eventStart" name="eventStart" value="<?php echo date('Y-m-d\TH:i', strtotime($event['eventStart'])); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="eventEnd" class="form-label">End Date and Time</label>
                    <input type="datetime-local" class="form-control w-auto" id="eventEnd" name="eventEnd" value="<?php echo date('Y-m-d\TH:i', strtotime($event['eventEnd'])); ?>" required>
                </div>

                <button type="submit" class="btn btn-primary">Update Archived Event</button>
            </form>
        </div>
    </body>
    </html>
    <?php
}
?>