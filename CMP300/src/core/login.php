<?php
//start the session if there isnt one detected
if (session_status() === PHP_SESSION_NONE) {
    session_start();}

include_once("../connection.php");
include_once("../navigation.php"); 
 

//assign the variables a blank string 
$enterpriseID = $password = "";
$enterpriseID_err = $password_err = $login_err = "";

//form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    //get EID
    if (empty(trim($_POST["enterpriseID"]))) {
        $enterpriseID_err = "Please enter your Enterprise ID.";
    } else {
        $enterpriseID = trim($_POST["enterpriseID"]);}

    //get password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    //check for errors
    if (empty($enterpriseID_err) && empty($password_err)) {
        $query = "SELECT userID, enterpriseID, password, roleID, groupID FROM tbl_users WHERE enterpriseID = ?";

        if ($stmt = mysqli_prepare($link, $query)) {
            //bind parameters
            mysqli_stmt_bind_param($stmt, "s", $enterpriseID);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                //check EID exists
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    //get all info needed
                    mysqli_stmt_bind_result($stmt, $id, $fetchedEnterpriseID, $hashed_password, $roleID, $groupID);
                    if (mysqli_stmt_fetch($stmt)) {
                        //password matching
                        if (password_verify($password, $hashed_password)) {
                            //assing info to the right variables
                            $_SESSION['loggedin'] = true;
                            $_SESSION['userID'] = $id;
                            $_SESSION['enterpriseID'] = $fetchedEnterpriseID;
                            $_SESSION['roleID'] = $roleID;
                            $_SESSION['groupID'] = $groupID;

                            //once logged in redirect to dashboard
                            header("Location: ../dashboard.php");
                            exit();
                        } else {
                            $login_err = "Invalid Enterprise ID or password.";}}
                } else {
                    $login_err = "Invalid Enterprise ID or password.";}
            } else {
                echo "Oops! Something went wrong. Please try again later.";}
            mysqli_stmt_close($stmt);}}
    //close DB connection
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">

</head>

<body>
    <div class="login-container">
        <?php 
        if (!empty($login_err)) {
            echo '<div class="alert alert-danger corner-alert">' . $login_err . '</div>'; }?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <h2>Login</h2>
            <br>
            <div class="form-group content <?php echo (!empty($enterpriseID_err)) ? 'has-error' : ''; ?>">
                <label for="enterpriseID">Enterprise ID</label>
                <input type="text" id="enterpriseID" placeholder="example@domain.com" name="enterpriseID" class="form-control input.custom" value="<?php echo htmlspecialchars($enterpriseID); ?>">
                <span class="help-block"><?php echo $enterpriseID_err; ?></span>
            </div> 
            <div class="form-group content <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control input.custom">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
        </form>
    </div>
</body>
</html>
