<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


include_once("../connection.php");
include_once("../navigation.php");  


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historical Events</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php
    // Check if the user is logged in and session variables exist
    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        // Correct the query syntax
        $query = "SELECT * FROM tbl_archive";
        if ($stmt = mysqli_prepare($link, $query)) {
            // Execute the query
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        } else {
            die("Database query preparation failed: " . mysqli_error($link));
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
                </tr>
            </thead>
            <tbody id="eventTableBody">
                <?php
                // Display only the required columns
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td><a href='archiveEventDetails.php?eventID=" . htmlspecialchars($row['eventID']) . "'>" . htmlspecialchars($row['eventTitle']) . "</a></td>";
                        echo "<td>" . htmlspecialchars($row['eventType']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='2'>No results found.</td></tr>";
                }

                // Function to delete expired events
                function deleteExpiredEvents($link) {
                    $currentDate = date("Y-m-d H:i:s");
                    $twelveMonthsAgo = date("Y-m-d H:i:s", strtotime("-12 months"));

                    // Prepare the SQL query to delete events
                    $query = "DELETE FROM tbl_archive WHERE eventEnd < ?";
                    if ($stmt = mysqli_prepare($link, $query)) {
                        mysqli_stmt_bind_param($stmt, "s", $twelveMonthsAgo);
                        mysqli_stmt_execute($stmt); // Execute the deletion query
                        mysqli_stmt_close($stmt);
                    } else {
                        echo "Database query preparation failed: " . mysqli_error($link);
                    }
                }

                // Call the function to update event states
                deleteExpiredEvents($link);
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
