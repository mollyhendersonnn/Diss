<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include_once("connection.php");
include_once("navigation.php");
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
    $query = "SELECT * FROM your_table"; // Replace 'your_table' with your actual table name
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
                    // Data rows
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        foreach ($row as $value) {
                            echo "<td>" . htmlspecialchars($value) . "</td>";
                        }
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>


if user is logged in show only for group 
if user is logged in show relevent </button>
othwerise in a excel show all </data>