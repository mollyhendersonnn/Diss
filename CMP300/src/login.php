<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include_once("connection.php");
include_once("navigation.php");
//include_once("audit.php");



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
        $query = "SELECT userID, enterpriseID, password, roleID, groupID FROM tbl_users WHERE enterpriseID = ?";
        
        if ($stmt = mysqli_prepare($link, $query)) {
            // Bind input parameters
            mysqli_stmt_bind_param($stmt, "s", $enterpriseID);

            // Execute the query
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                // Check if enterprise ID exists
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // Fetch the result
                    mysqli_stmt_bind_result($stmt, $id, $fetchedEnterpriseID, $hashed_password, $roleID, $groupID);
                    if (mysqli_stmt_fetch($stmt)) {
                        // Verify password
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct; set session variables
                            $_SESSION['loggedin'] = true;
                            $_SESSION['userID'] = $id;
                            $_SESSION['enterpriseID'] = $fetchedEnterpriseID;
                            $_SESSION['roleID'] = $roleID;
                            $_SESSION['groupID'] = $groupID;

                            //Audit log
                             
    // $userID = $_SESSION["userID"];
    // $action = "User logged in";
    // logAction($link, $userID, $action);

                            // Redirect to the dashboard
                            header("Location: dashboard.php");
                            exit();
                        } else {
                            $login_err = "Invalid password.";
                        }
                    }
                } else {
                    $login_err = "No account found with that Enterprise ID.";
                   
                }
            } else {
    
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
     
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">

</head>

<body>
    <div class="login-container">

        <?php 
        if (!empty($login_err)) {
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }
        ?>

        <?php
        $customInputClass = 'input.custom'; // Set this dynamically if needed
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <h2>Login</h2>
            <br>
            
            <div class="form-group content <?php echo (!empty($enterpriseID_err)) ? 'has-error' : ''; ?>">
                <label for="enterpriseID">Enterprise ID</label>
                <input type="text" id="enterpriseID" placeholder="example@domain.com" name="enterpriseID" class="form-control <?php echo $customInputClass; ?>" value="<?php echo htmlspecialchars($enterpriseID); ?>">
                <span class="help-block"><?php echo $enterpriseID_err; ?></span>
            </div> 

            <div class="form-group content <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control <?php echo $customInputClass; ?>">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>

            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
        </form>
    </div>
</body>

</html>
