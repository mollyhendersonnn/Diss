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
    <head>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>


</head>

<body>
    <?php

    

// Check if the user is logged in and session variables exist
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true ) {

    // Default query for users without a group
    $query = "SELECT * FROM tbl_events WHERE stateID = 2";
    $result = mysqli_query($link, $query);
    if ($result === false) {
        die("Database query failed: " . mysqli_error($link));
    }
}
        ?>

<div class="container mt-5">
        <h2 class="mb-4">Archived events</h2>
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
    </div>
    <div class="modal" id="createEventModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Event</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="createEventForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="eventTitle">Event Title</label>
                        <input type="text" class="form-control" id="eventTitle" name="eventTitle" required>
                    </div>
                    <div class="form-group">
                        <label for="eventFile">Upload Image</label>
                        <input type="file" class="form-control" id="eventFile" name="eventFile" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label for="eventType">Event Type</label>
                        <select class="form-control" id="eventType" name="eventType" required>
                            <option value="holiday">Holiday</option>
                            <option value="celebration">Celebration</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="eventDescription">Description</label>
                        <textarea class="form-control" id="eventDescription" name="eventDescription" rows="3" required></textarea>
                    </div>
                                        <div class="form-group">
                        <label for="startDateTime">Start Date and Time</label>
                        <input type="datetime-local" class="form-control" id="startDateTime" name="startDateTime" required>
                    </div>
                    <div class="form-group">
                        <label for="endDateTime">End Date and Time</label>
                        <input type="datetime-local" class="form-control" id="endDateTime" name="endDateTime" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Create</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>

<script>
    $(document).ready(function() {
        $('#createEventForm').on('submit', function(e) {
            e.preventDefault(); // Prevent default form submission

            // Gather form data
            var formData = new FormData(this);

            // Send AJAX request to the server
            $.ajax({
                url: 'createEvent.php', // Server-side script to handle event creation
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false, // Ensure file upload is handled correctly
                dataType: 'json', // Expect JSON response
                success: function(response) {
                    if (response.sql) {
                        // Display the SQL query for debugging
                        alert('SQL Query: ' + response.sql);
                    } else if (response.success) {
                        $('#createEventModal').modal('hide');
                        $('#createEventForm')[0].reset();
                        alert('Event created successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('An unexpected error occurred: ' + error);
                }
            });
        });
    });
</script>
