<?php
//start the session if there isnt one detected
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("../connection.php");
include_once("../navigation.php"); 
 

//check for the event ID
if (isset($_GET['eventID'])) {
    $eventID = $_GET['eventID'];

    //get all details based on the eventID
    $query = "SELECT * FROM tbl_events WHERE eventID = ?";
    if ($stmt = mysqli_prepare($link, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $eventID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($event = mysqli_fetch_assoc($result)) {
        } else {
            echo "<p>Event not found.</p>";
                    exit; }
    } else {
        echo "<p>Error fetching event details.</p>";
        exit;}
} else {
    echo "<p>No event specified.</p>";
    exit;}

//RSVP button functionality
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['rsvp'])) {
    //check session if user has already rsvpd
    if (!isset($_SESSION['rsvp'][$eventID])) {
        //add one to the number of attendees
        $query = "UPDATE tbl_events SET numAttendees = numAttendees + 1 WHERE eventID = ?";
        if ($stmt = mysqli_prepare($link, $query)) {
            mysqli_stmt_bind_param($stmt, "i", $eventID);
            if (mysqli_stmt_execute($stmt)) {
                echo '<p class="alert alert-success">RSVP Updated Successfully!</p>';
                $_SESSION['rsvp'][$eventID] = true;
            } else {
                echo '<p class="alert alert-danger">Error updating attendees.</p>';
            }}}else {
        echo '<p class="alert alert-danger">You have already RSVP’d for this event!</p>';}}


//update number of attendees after button pressed
$query = "SELECT numAttendees FROM tbl_events WHERE eventID = ?";
$numAttendees = null;
if ($stmt = mysqli_prepare($link, $query)) {
    mysqli_stmt_bind_param($stmt, "i", $eventID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $numAttendees);
    mysqli_stmt_fetch($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container mt-5">
        <h2><?php echo htmlspecialchars($event['eventTitle']); ?></h2>
        <br>
        <p><strong>Event Type:</strong> <?php echo htmlspecialchars($event['eventType']); ?></p>
        <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($event['eventDescription'])); ?></p>
        <p><strong>Start:</strong> <?php echo htmlspecialchars($event['eventStart']); ?></p>
        <p><strong>End:</strong> <?php echo htmlspecialchars($event['eventEnd']); ?></p>
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <p><strong>Attendees:</strong> <?php echo isset($numAttendees) ? htmlspecialchars($numAttendees) : "0"; ?></p>
        <?php endif; ?>
            <form method="post">
                <button type="submit" name="rsvp" class="btn btn-primary">RSVP</button>
            </form>
        <?php if ($event['eventFile']): ?>
            <a href="downloadFile.php?eventID=<?php echo $eventID; ?>" class="btn btn-success mt-3">Download Event File</a>
        <?php endif; ?>
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <a href="updateEvent.php?eventID=<?php echo $eventID; ?>" class="btn btn-success mt-3">Update Event</a>
            <a href="cancelEvent.php?eventID=<?php echo $eventID; ?>" class="btn btn-danger mt-3">Cancel Event</a>
        <?php endif; ?>
    </div>
</body>
</html>
