<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include_once("connection.php");
include_once("navigation.php");
//include_once("audit.php");
?>


<!DOCTYPE html>
<html lang="en" xmlns:mso="urn:schemas-microsoft-com:office:office"
    xmlns:msdt="uuid:C2F41010-65B3-11d1-A29F-00AA00C14882">

<head>
    <meta charset="UTF-8"> 
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <head>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>


</head>

<body>
    <?php

    

// Check if the user is logged in and session variables exist
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true ) {
    // Check if groupID is not null
    if (!empty($_SESSION["groupID"])) {
        $query = "SELECT * FROM tbl_events WHERE groupID = ? AND stateID = 1";
        if ($stmt = mysqli_prepare($link, $query)) {
            mysqli_stmt_bind_param($stmt, "i", $_SESSION["groupID"]);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            echo '<a href="createEvent.php" class="btn btn-primary mb-3">Create Event</a>';
        } else {
            die("Database query preparation failed: " . mysqli_error($link));
        }
    }
    else{
        echo '<a href="createEvent.php" class="btn btn-primary mb-3">Create Event</a>';
    }
} else {
    // Default query for users without a group
    $query = "SELECT * FROM tbl_events WHERE stateID = 1";
    $result = mysqli_query($link, $query);
    if ($result === false) {
        die("Database query failed: " . mysqli_error($link));
    }
}
        ?>

<div class="container mt-5">
        <h2 class="mb-4">Events</h2>
       <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <?php
                    // Fetch the first row to get column names dynamically
                    if ($result && mysqli_num_rows($result) > 0) {
                        $first_row = mysqli_fetch_assoc($result);

                        // Display column names
                        foreach ($first_row as $column_name => $value) {
                            echo "<th>" . htmlspecialchars($column_name) . "</th>";
                        }
                        echo "</tr>";
                        // Reset the result pointer
                        mysqli_data_seek($result, 0);
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                // Loop through each row of the query result
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        foreach ($row as $value) {
                            echo "<td>" . htmlspecialchars($value) . "</td>";
                        }
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='100%'>No results found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
   