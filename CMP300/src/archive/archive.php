<?php
//start the session if there isnt one detected
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("../connection.php");
include_once("../navigation.php"); 
include_once("../clean.php");

//success message if previous action was completed successfully
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
    <title>Historical Events</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php
    //get the archived events from the db
    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        $query = "SELECT * FROM tbl_archive ORDER BY eventStart ASC";
        if ($stmt = mysqli_prepare($link, $query)) {
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        } else {
            die("Database query preparation failed: " . mysqli_error($link));}}
    ?>

    <div class="container mt-5">
        <h2 class="mb-4">Historical Events</h2>
        <input type="text" id="searchBar" class="form-control mb-3" placeholder="Search for events...">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Event Title</th>
                    <th>Event Type</th>
                    <th>Event Start</th>
                </tr>
            </thead>
            <tbody id="eventTableBody">
                <?php
                // column headers
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td><a href='archiveEventDetails.php?eventID=" . htmlspecialchars($row['eventID']) . "'>" . htmlspecialchars($row['eventTitle']) . "</a></td>";
                        echo "<td>" . htmlspecialchars($row['eventType']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['eventStart']) . "</td>";
                        echo "</tr>";
                    }} else { echo "<tr><td colspan='2'>No results found.</td></tr>";}

               //deleting events over 12 months old
                function deleteExpiredEvents($link) {
                    $twelveMonthsAgo = date("Y-m-d H:i:s", strtotime("-12 months"));

                    $query = "DELETE FROM tbl_archive WHERE eventEnd < ?";
                    if ($stmt = mysqli_prepare($link, $query)) {
                        mysqli_stmt_bind_param($stmt, "s", $twelveMonthsAgo);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                    } else {
                        echo "Database query preparation failed: " . mysqli_error($link); }}
                deleteExpiredEvents($link);
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<script>
    //javascript for the search bar filter
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
                    }}
                row.style.display = match ? '' : 'none'; }});
    });
</script>
