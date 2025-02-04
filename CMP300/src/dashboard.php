<?php
//start the session if there isnt one detected
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("../connection.php");
include_once("../navigation.php"); 
include_once("../clean.php"); 

if (!empty($_SESSION['success_message'])) {
    echo "<p class='alert alert-success'>" . $_SESSION['success_message'] . "</p>";
    unset($_SESSION['success_message']); 
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8"> 
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php

$result = null;


//check user is logged in and if session variables exist
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    if (!empty($_SESSION["groupID"])) {
        $query = "SELECT eventID, eventTitle, eventType, eventStart 
                  FROM tbl_events WHERE groupID = ? AND stateID = 1 ORDER BY eventStart ASC";
        if ($stmt = mysqli_prepare($link, $query)) {
            mysqli_stmt_bind_param($stmt, "i", $_SESSION["groupID"]);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        } else {
            die("Query binding failed: " . mysqli_error($link));
        }
    } else {
        //if the user is logged in but has no group, fetch all active events
        $query = "SELECT eventID, eventTitle, eventType, eventStart 
                  FROM tbl_events WHERE stateID = 1 ORDER BY eventStart ASC";
        $result = mysqli_query($link, $query);
        if ($result === false) {
            die("SQL failed: " . mysqli_error($link));
        }
    }
} else {
    //if the user is not logged in, fetch all active events
    $query = "SELECT eventID, eventTitle, eventType, eventStart 
              FROM tbl_events WHERE stateID = 1 ORDER BY eventStart ASC";
    $result = mysqli_query($link, $query);
    if ($result === false) {
        die("SQL failed: " . mysqli_error($link));
    }
}
?>
    <div class="container mt-5">
        <h2 class="mb-4">Events</h2>
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <a href="events/createEvent.php" class="btn btn-primary mb-3">Create Event</a>
            <?php endif; ?>
        <input type="text" id="searchBar" class="form-control mb-3" placeholder="Search for events...">
        <table class="table table-bordered table-striped">
        <thead class="thead-dark">
    <tr>
        <th>Event Title</th>
        <th>Event Type</th>
        <th>Start Date</th>
    </tr>
</thead>
<tbody id="eventTableBody">
    <?php
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {

            $eventStartDate = new DateTime($row['eventStart']);
            $formattedDate = $eventStartDate->format('l jS F Y H:i');

            echo "<tr>";
            echo "<td><a href='events/eventDetails.php?eventID=" . htmlspecialchars($row['eventID']) . "'>" . htmlspecialchars($row['eventTitle']) . "</a></td>";
            echo "<td>" . htmlspecialchars($row['eventType']) . "</td>";
            echo "<td>" . $formattedDate . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='4'>No results found.</td></tr>";
    }


    //function to put events in DB if the end time has elapsed
    function archiveExpiredEvents($link) {
        $currentDate = date("Y-m-d H:i:s");
        $archiveReason = 1;
    
        $sql = "
        INSERT INTO `tbl_archive` (`eventID`, `stateID`, `groupID`, `userID`, `eventTitle`, `eventType`, `eventStart`, `eventEnd`, `numAttendees`)
        SELECT eventID, stateID, groupID, userID, eventTitle, eventType, eventStart, eventEnd, numAttendees
        FROM tbl_events
        WHERE eventEnd < ?";
    
    
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $currentDate);
            if (!mysqli_stmt_execute($stmt)) {
                echo "Error archiving events: " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "Query prep failed for archive: " . mysqli_error($link);
        }
    
        //delete the archived events from tbl_events if successfully added to tbl_archive
        $deleteQuery = "DELETE FROM tbl_events WHERE eventEnd < ?";
    
        if ($stmt = mysqli_prepare($link, $deleteQuery)) {
            mysqli_stmt_bind_param($stmt, "s", $currentDate);
            if (!mysqli_stmt_execute($stmt)) {
                echo "Error deleting events: " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "Query prep failed: " . mysqli_error($link);
        }
    }

    archiveExpiredEvents($link);
    ?>

</tbody>

        </table>
    </div>
</body>
</html>
<script>
    //search functionality
    document.addEventListener('DOMContentLoaded', function () {
        const searchBar = document.getElementById('searchBar');
        const tableBody = document.getElementById('eventTableBody');
        const rows = tableBody.getElementsByTagName('tr');

        searchBar.addEventListener('keyup', function () {
            const filter = searchBar.value.toLowerCase();

            for (let row of rows) {
                const cells = row.getElementsByTagName('td');
                let match = false;

                for (let cell of cells) {
                    if (cell.textContent.toLowerCase().indexOf(filter) > -1) {
                        match = true;
                        break;
                    }
                }

                row.style.display = match ? '' : 'none';
            }
        });
    });

</script>