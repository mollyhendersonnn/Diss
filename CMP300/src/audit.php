<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include_once("connection.php");
include_once("navigation.php");

function auditAction($actionDescription) {
    global $connection;

    if (!isset($_SESSION["userID"])) {
        error_log("User ID not found in session.");
        return;
    }

    $userID = $_SESSION["userID"];
    $timestamp = date("Y-m-d H:i:s");
    $query = "INSERT INTO audit_log (userID, actionDescription, timestamp) VALUES (?, ?, ?)";

    if ($stmt = mysqli_prepare($connection, $query)) {
        mysqli_stmt_bind_param($stmt, "iss", $userID, $actionDescription, $timestamp);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        error_log("Failed to prepare audit log statement: " . mysqli_error($connection));
    }
}
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
</head>

<body>
    <?php
    // Fetch data from database
    $query = "SELECT * FROM tbl_audit"; 
    $result = mysqli_query($connection, $query);
    ?>

    <div class="dashboard">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <?php
                        // Dynamic header generation
                        if ($result) {
                            $fields = mysqli_fetch_fields($result);
                            foreach ($fields as $field) {
                                echo "<th>" . htmlspecialchars($field->name) . "</th>";
                            }
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Dynamic row generation
                    if ($result) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            foreach ($row as $cell) {
                                echo "<td>" . htmlspecialchars($cell) . "</td>";
                            }
                            echo "</tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>