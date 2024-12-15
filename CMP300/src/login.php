<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include_once("connection.php");
include_once("navigation.php");

// Log errors for debugging (update path as needed)
$logFile = __DIR__ . '/debug_log.txt';
function logToFile($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

// Initialize variables
$enterpriseID = $password = "";
$enterpriseID_err = $password_err = $login_err = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate enterprise ID
    if (empty(trim($_POST["enterpriseID"]))) {
        $enterpriseID_err = "Please enter your Enterprise ID.";
    } else {
        $enterpriseID = trim($_POST["enterpriseID"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Process login if no validation errors
    if (empty($enterpriseID_err) && empty($password_err)) {
        $query = "SELECT userID, enterpriseID, password, roleID FROM tbl_users WHERE enterpriseID = ?";
        
        if ($stmt = mysqli_prepare($link, $query)) {
            // Bind input parameters
            mysqli_stmt_bind_param($stmt, "s", $enterpriseID);

            // Execute the query
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                // Check if enterprise ID exists
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // Fetch the result
                    mysqli_stmt_bind_result($stmt, $id, $fetchedEnterpriseID, $hashed_password, $roleID);
                    if (mysqli_stmt_fetch($stmt)) {
                        // Verify password
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct; set session variables
                            $_SESSION['loggedin'] = true;
                            $_SESSION['userID'] = $id;
                            $_SESSION['enterpriseID'] = $fetchedEnterpriseID;
                            $_SESSION['roleID'] = $roleID;

                            // Redirect to the dashboard
                            header("Location: dashboard.php");
                            exit();
                        } else {
                            $login_err = "Invalid password.";
                            logToFile("Failed login attempt for Enterprise ID: $enterpriseID (Incorrect Password)");
                        }
                    }
                } else {
                    $login_err = "No account found with that Enterprise ID.";
                    logToFile("Failed login attempt for Enterprise ID: $enterpriseID (No Account Found)");
                }
            } else {
                logToFile("Database query failed: " . mysqli_error($link));
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        } else {
            logToFile("Failed to prepare statement: " . mysqli_error($link));
        }
    }

    // Close database connection
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css"
    <link rel="stylesheet" href="css/styles.css">

</head>

<body>
    <div class="login-container">
        <h2>Login</h2>
        <p>Please fill in your credentials to login.</p>

        <?php 
        if (!empty($login_err)) {
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($enterpriseID_err)) ? 'has-error' : ''; ?>">
                <label for="enterpriseID">Enterprise ID</label>
                <input type="text" id="enterpriseID" name="enterpriseID" class="form-control" value="<?php echo htmlspecialchars($enterpriseID); ?>">
                <span class="help-block"><?php echo $enterpriseID_err; ?></span>
            </div> 

            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>

            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
        </form>
    </div>
</body>

</html>
