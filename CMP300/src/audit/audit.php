<?php
//start the session if there isnt one detected
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
    <title>User Audit</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php
    //check the user is logged in 
    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        //get info from audit table
        $query = "SELECT userID, action, timestamp FROM tbl_audit ORDER BY timestamp DESC";
        if ($stmt = mysqli_prepare($link, $query)) {
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        } else {
            die("Database query preparation failed: " . mysqli_error($link)); }}
    

    // delete entries over 12 months old
    function deleteExpiredAuditEntries($link) {
        $twelveMonthsAgo = date("Y-m-d H:i:s", strtotime("-12 months"));
        $query = "DELETE FROM tbl_audit WHERE timestamp < ?";
        if ($stmt = mysqli_prepare($link, $query)) {
            mysqli_stmt_bind_param($stmt, "s", $twelveMonthsAgo);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            echo "Database query preparation failed: " . mysqli_error($link); }}
    deleteExpiredAuditEntries($link);
    ?>

    <div class="container mt-5">
        <h2 class="mb-4">User Audit</h2>
        <input type="text" id="searchBar" class="form-control mb-3" placeholder="Search for actions...">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>User</th>
                    <th>Action</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody id="auditTableBody">
                <?php
                //show the audit data
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['userID']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['action']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['timestamp']) . "</td>";
                        echo "</tr>";
                    }} else {
                    echo "<tr><td colspan='3'>No audit records found.</td></tr>"; }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<script>

    //filter search bar functionality
    document.addEventListener('DOMContentLoaded', function () {
        const searchBar = document.getElementById('searchBar');
        const tableBody = document.getElementById('auditTableBody');
        const rows = tableBody.getElementsByTagName('tr');

        searchBar.addEventListener('keyup', function () {
            const filter = searchBar.value.toLowerCase();

            for (let row of rows) {
                const cells = row.getElementsByTagName('td');
                let match = false;

                for (let cell of cells) {
                    if (cell.textContent.toLowerCase().indexOf(filter) > -1) {
                        match = true;
                        break; }}
                row.style.display = match ? '' : 'none';
            } }); });
</script>
