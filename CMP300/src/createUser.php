<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
include("connection.php");
include("navigation.php");
//include("audit.php");

// Define variables and initialize with empty values
$username = $password = $firstname = $roleID = $groupID = "";
$username_err = $password_err = $firstname_err = $roleID_err = $groupID_err = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $username = isset($_POST["username"]) ? trim($_POST["username"]) : "";
    $password = isset($_POST["password"]) ? trim($_POST["password"]) : "";
    $firstname = isset($_POST["firstname"]) ? trim($_POST["firstname"]) : "";
    $roleID = isset($_POST["roleID"]) ? (int) $_POST["roleID"] : 0;
    $groupID = isset($_POST["groupID"]) ? (int) $_POST["groupID"] : 0;

    // Validation
    if (empty($username)) $username_err = "Please enter a username.";
    if (empty($password)) $password_err = "Please enter a password.";
    if (empty($firstname)) $firstname_err = "Please enter the first name.";
    if (empty($roleID)) $roleID_err = "Please enter a role ID.";
    if (empty($groupID)) $groupID_err = "Please enter a group ID.";

    // Check for errors
    if (empty($username_err) && empty($password_err) && empty($firstname_err) && empty($roleID_err) && empty($groupID_err)) {
        // Check if the username already exists
        $sql = "SELECT * FROM tbl_users WHERE enterpriseID = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $username);

            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) > 0) {
                    echo "Enterprise ID exists, please try again.";
                } else {
                    // Hash the password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Insert the new user into the database
                    $sql_insert = "INSERT INTO tbl_users (roleID, groupID, enterpriseID, password, firstName) VALUES (?, ?, ?, ?, ?)";
                    if ($stmt_insert = mysqli_prepare($link, $sql_insert)) {
                        mysqli_stmt_bind_param($stmt_insert, "iisss", $roleID, $groupID, $username, $hashed_password, $firstname);

                        if (mysqli_stmt_execute($stmt_insert)) {
                            echo "<div class='alert alert-success'>User created successfully!</div>";
                            //Audit log
                           // auditAction($userID, "User created user: $username");
                        } else {
                            echo "<div class='alert alert-danger'>Error: " . mysqli_stmt_error($stmt_insert) . "</div>";
                        }

                        mysqli_stmt_close($stmt_insert);
                    }
                }
                mysqli_stmt_close($stmt);
            } else {
                echo "<div class='alert alert-danger'>Error executing query: " . mysqli_error($link) . "</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Error preparing query: " . mysqli_error($link) . "</div>";
        }
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
                <input type="text" id="firstname" name="firstname"
                    class="form-control <?php echo (!empty($firstname_err)) ? 'is-invalid' : ''; ?>"
                    value="<?php echo $firstname; ?>">
                <span class="invalid-feedback"><?php echo $firstname_err; ?></span>
            </div>
            <div class="form-group">
                <label for="roleID">Role ID</label>
                <input type="number" id="roleID" name="roleID"
                    class="form-control <?php echo (!empty($roleID_err)) ? 'is-invalid' : ''; ?>"
                    value="<?php echo $roleID; ?>">
                <span class="invalid-feedback"><?php echo $roleID_err; ?></span>
            </div>
            <div class="form-group">
                <label for="groupID">Group ID</label>
                <input type="number" id="groupID" name="groupID"
                    class="form-control <?php echo (!empty($groupID_err)) ? 'is-invalid' : ''; ?>"
                    value="<?php echo $groupID; ?>">
                <span class="invalid-feedback"><?php echo $groupID_err; ?></span>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</body>

</html>