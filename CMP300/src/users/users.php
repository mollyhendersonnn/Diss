<?php
//start the session if there isnt one detected
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("../connection.php");
include_once("../navigation.php"); 
include_once("../clean.php"); 

if (!empty($_SESSION['success_message'])) 
    echo "<p class='alert alert-success'>" . $_SESSION['success_message'] . "</p>";
    unset($_SESSION['success_message']); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> 
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    
 <?php

//ini the result variable as null
$result = null;

//check user is logged in
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        $query = "SELECT u.userID, u.enterpriseID, u.firstName, u.groupID, u.roleID, r.roleName, g.groupName
        FROM tbl_users u 
        INNER JOIN tbl_roles r ON u.roleID = r.roleID
        INNER JOIN tbl_group g ON u.groupID = g.groupID";
        if ($stmt = mysqli_prepare($link, $query)) {
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        } else {
            die("Database query preparation failed: " . mysqli_error($link));
        }
    mysqli_stmt_close($stmt);
}
?>

    <div class="container mt-5">
        <h2 class="mb-4">Users</h2>

            <a href="createUser.php" class="btn btn-primary mb-3">Create User</a>
        <input type="text" id="searchBar" class="form-control mb-3" placeholder="Search for user...">
        <table class="table table-bordered table-striped">
        <thead class="thead-dark">
    <tr>
        <th>Enterprise ID</th>
        <th>First Name</th>
        <th>Group</th>
        <th>Role</th>
    </tr>
</thead>
<tbody id="userTableBody">
    <?php
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            //assign as variables for columns
            $roleName = $row['roleName'];
            $groupName = $row['groupName'];

            echo "<tr>";
            echo "<td><a href='userDetails.php?userID=" . htmlspecialchars($row['userID']) . "'>" . htmlspecialchars($row['enterpriseID']) . "</a></td>";
            echo "<td>" . htmlspecialchars($row['firstName']) . "</td>";
            echo "<td>" . htmlspecialchars($groupName) . "</td>";
            echo "<td>" . htmlspecialchars($roleName) . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='4'>No results found.</td></tr>";
    }
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
        const tableBody = document.getElementById('userTableBody');
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