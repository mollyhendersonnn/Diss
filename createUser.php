<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
include("connection.php");
include("navigation.php");

// Define variables and initialize with empty values
$username = $password = $firstname = $roleID = $groupID = "";
$username_err = $password_err = $firstname_err = $roleID_err = $groupID_err = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture inputs
    $username = isset($_POST["username"]) ? trim($_POST["username"]) : "";
    $password = isset($_POST["password"]) ? trim($_POST["password"]) : "";
    $firstname = isset($_POST["firstname"]) ? trim($_POST["firstname"]) : "";
    $roleID = isset($_POST["roleID"]) ? (int) $_POST["roleID"] : 0;
    $groupID = isset($_POST["groupID"]) ? (int) $_POST["groupID"] : 0;
    
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user into the database
    $sql = "INSERT INTO tbl_users (roleID, groupID, enterpriseID, password, firstName) VALUES (?, ?, ?, ?, ?)";

    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "iisss", $param_roleID, $param_groupID, $param_username, $param_password, $param_firstname);

        // Set parameters
        $param_roleID = $roleID;
        $param_groupID = $groupID;
        $param_username = $username;
        $param_password = $hashed_password;
        $param_firstname = $firstname;

        echo "<pre>";
        var_dump($param_roleID, $param_groupID, $param_username, $param_password, $param_firstname);
        echo "</pre>";

        // Execute statement
        if (mysqli_stmt_execute($stmt)) {
            echo "<div class='alert alert-success'>User created successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . mysqli_stmt_error($stmt) . "</div>";
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "<div class='alert alert-danger'>Error preparing statement: " . mysqli_error($link) . "</div>";
    }
}



    // Validate and sanitize group ID
    if (empty(trim($_POST["groupID"]))) {
        $groupID_err = "Please enter a group ID.";
    } else {
        $groupID = (int) $_POST["groupID"];
    }

    // Check for errors before inserting into database
    if (empty($username_err) && empty($password_err) && empty($firstname_err) && empty($roleID_err) && empty($groupID_err)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert the new user into the database
        $sql = "INSERT INTO tbl_users (roleID, groupID, enterpriseID, password, firstname) VALUES (?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iisss", $param_roleID, $param_groupID, $param_username, $param_password, $param_firstname);

            // Set parameters
            $param_roleID = $roleID;
            $param_groupID = $groupID;
            $param_username = $username;
            $param_password = $hashed_password;
            $param_firstname = $firstname;

            if (mysqli_stmt_execute($stmt)) {
                echo "<div class='alert alert-success'>User created successfully!</div>";
            } else {
                echo "<div class='alert alert-danger'>Something went wrong. Please try again later.</div>";
            }

            mysqli_stmt_close($stmt);
        }
    }

    // Close database connection
    mysqli_close($link);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Create User</h2>
        <p>Fill out this form to create a new user.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <label for="firstname">First Name</label>
                <input type="text" id="firstname" name="firstname" class="form-control <?php echo (!empty($firstname_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $firstname; ?>">
                <span class="invalid-feedback"><?php echo $firstname_err; ?></span>
            </div>
            <div class="form-group">
                <label for="roleID">Role ID</label>
                <input type="number" id="roleID" name="roleID" class="form-control <?php echo (!empty($roleID_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $roleID; ?>">
                <span class="invalid-feedback"><?php echo $roleID_err; ?></span>
            </div>
            <div class="form-group">
                <label for="groupID">Group ID</label>
                <input type="number" id="groupID" name="groupID" class="form-control <?php echo (!empty($groupID_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $groupID; ?>">
                <span class="invalid-feedback"><?php echo $groupID_err; ?></span>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</body>
</html>
