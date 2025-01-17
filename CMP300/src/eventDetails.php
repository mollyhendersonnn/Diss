<?php
// Start session
session_start();

// Include necessary files
include_once("connection.php");
include_once("navigation.php");  // Include navigation bar

// Ensure error reporting is enabled for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if eventID is passed in the URL
if (isset($_GET['eventID'])) {
    $eventID = $_GET['eventID'];

    // Fetch event details based on eventID
    $query = "SELECT * FROM tbl_events WHERE eventID = ?";
    if ($stmt = mysqli_prepare($link, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $eventID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($event = mysqli_fetch_assoc($result)) {
            // Event data fetched successfully
        } else {
            echo "<p>Event not found.</p>";
            exit;
        }
    } else {
        echo "<p>Error fetching event details.</p>";
        exit;
    }
} else {
    echo "<p>No event specified.</p>";
    exit;
}

// RSVP functionality
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['rsvp'])) {
    // Update number of attendees
    $query = "UPDATE tbl_events SET numAttendees = numAttendees + 1 WHERE eventID = ?";
    if ($stmt = mysqli_prepare($link, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $eventID);
        if (mysqli_stmt_execute($stmt)) {
            echo "<p>RSVP successful!</p>";
        } else {
            echo "<p>Error updating attendees.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container mt-5">
        <h2><?php echo htmlspecialchars($event['eventTitle']); ?></h2>
        <p><strong>Event Type:</strong> <?php echo htmlspecialchars($event['eventType']); ?></p>
        <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($event['eventDescription'])); ?></p>
        <p><strong>Start:</strong> <?php echo htmlspecialchars($event['eventStart']); ?></p>
        <p><strong>End:</strong> <?php echo htmlspecialchars($event['eventEnd']); ?></p>
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
        <p><strong>Attendees:</strong> <?php echo htmlspecialchars($event['numAttendees']); ?></p>
        <?php endif; ?>
       
            <form method="post">
                <button type="submit" name="rsvp" class="btn btn-primary">RSVP</button>
            </form>


        <!-- File Download -->
        <?php if ($event['eventFile']): ?>
            <a href="downloadFile.php?eventID=<?php echo $eventID; ?>" class="btn btn-success mt-3">Download Event File</a>
        <?php endif; ?>

        <a href="cancelEvent.php" class="btn btn-primary mb-3">Cancel Event</a>
    </div>
</body>
</html>
