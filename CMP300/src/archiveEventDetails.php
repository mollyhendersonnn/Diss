<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    $query = "SELECT * FROM tbl_archive WHERE eventID = ?";
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
        <p><strong>Event Type:</strong> <?php echo htmlspecialchars($event['eventType']) ?? ""; ?></p>
        <p><strong>Start:</strong> <?php echo htmlspecialchars($event['eventStart']) ?? ""; ?></p>
        <p><strong>End:</strong> <?php echo htmlspecialchars($event['eventEnd']) ?? ""; ?></p>
        <p><strong>Feedback:</strong> <?php echo htmlspecialchars($event['eventFeedback']) ?? ""; ?></p>
        <p><strong>Cost:</strong> <?php echo htmlspecialchars($event['eventCost']) ?? ""; ?></p>
        <p><strong>Attendees:</strong> <?php echo isset($numAttendees) ? htmlspecialchars($numAttendees) : "0"; ?></p>
        <p><strong>Cancelled?</strong> <?php 
                                        if ($event['archiveReason'] == 2) {
                                            echo 'Cancelled';} 
                                        elseif ($event['archiveReason'] == 1) {
                                            echo 'Completed';} 
                                            else {
                                            echo 'unknown';}?> </p>

            <a href="updateArchivedEvent.php?eventID=<?php echo $eventID; ?>" class="btn btn-success mt-3">Update Event</a>
            <a href="deleteArchivedEvent.php?eventID=<?php echo $eventID; ?>" class="btn btn-danger mt-3">Permanently delete Event</a>
    </div>
</body>
</html>
