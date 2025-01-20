<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include_once("connection.php");
include_once("navigation.php");
//include_once("audit.php");

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
    // Check if the user is logged in and session variables exist
    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        if (!empty($_SESSION["groupID"])) {
            $query = "SELECT eventID, eventTitle, eventType, eventDescription, eventStart 
                      FROM tbl_events WHERE groupID = ? AND stateID = 1";
            if ($stmt = mysqli_prepare($link, $query)) {
                mysqli_stmt_bind_param($stmt, "i", $_SESSION["groupID"]);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                echo '<a href="createEvent.php" class="btn btn-primary mb-3">Create Event</a>';
            } else {
                die("Database query preparation failed: " . mysqli_error($link));
            }
        } else {
            $query = "SELECT eventID, eventTitle, eventType, eventDescription, eventStart 
                      FROM tbl_events WHERE stateID = 1";
            $result = mysqli_query($link, $query);
            if ($result === false) {
                die("Database query failed: " . mysqli_error($link));
            }
        }
    }
    ?>

    <div class="container mt-5">
        <h2 class="mb-4">Events</h2>
        <input type="text" id="searchBar" class="form-control mb-3" placeholder="Search for events...">
        <table class="table table-bordered table-striped">
        <thead class="thead-dark">
    <tr>
        <!-- Define static column headers -->
        <th>Event Title</th>
        <th>Event Type</th>
        <th>Event Description</th>
        <th>Start Date</th>
    </tr>
</thead>
<tbody id="eventTableBody">
    <?php
    // Display only the required columns
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td><a href='eventDetails.php?eventID=" . htmlspecialchars($row['eventID']) . "'>" . htmlspecialchars($row['eventTitle']) . "</a></td>";
            echo "<td>" . htmlspecialchars($row['eventType']) . "</td>";
            echo "<td>" . htmlspecialchars($row['eventDescription']) . "</td>";
            echo "<td>" . htmlspecialchars($row['eventStart']) . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='4'>No results found.</td></tr>";
    }


    
    function updateEventStates($link) {
        $currentDate = date("Y-m-d H:i:s");
    
        // Prepare the SQL query to update the event state
        $query = "UPDATE tbl_events SET stateID = 2 WHERE eventEnd < ? AND stateID = 1";
    
        if ($stmt = mysqli_prepare($link, $query)) {
            mysqli_stmt_bind_param($stmt, "s", $currentDate);
    
            mysqli_stmt_close($stmt);
        } else {
            echo "Database query preparation failed: " . mysqli_error($link);
        }
    }
    
    // Call the function to update event states
    updateEventStates($link);
    ?>

</tbody>

        </table>
    </div>
</body>
</html>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchBar = document.getElementById('searchBar');
        const tableBody = document.getElementById('eventTableBody');
        const rows = tableBody.getElementsByTagName('tr');

        searchBar.addEventListener('keyup', function () {
            const filter = searchBar.value.toLowerCase();

            // Loop through all table rows
            for (let row of rows) {
                const cells = row.getElementsByTagName('td');
                let match = false;

                // Check if any cell in the row contains the filter text
                for (let cell of cells) {
                    if (cell.textContent.toLowerCase().indexOf(filter) > -1) {
                        match = true;
                        break;
                    }
                }

                // Toggle the row's visibility based on match status
                row.style.display = match ? '' : 'none';
            }
        });
    });

</script>
