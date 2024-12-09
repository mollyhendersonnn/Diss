<html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>

<body>

    <?php //Initialise the session
    

    //Include config file
    include("connection.php");
    include("navigation.php");

    //Define variables and initialise with empty values
    $username = $password = "";
    $username_err = $pasdword_err = "";

    //Processing form data when form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        //Check if username is empty
        if (empty(trim($_POST["username"]))) {
            $username_err = "Please enter username";
        } else {
            $username = trim($_POST["username"]);
        }

        //Check if password is empty
        if (empty(trim($_POST["password"]))) {
            $password_err = "Please enter your password";
        } else {
            $password = trim($_POST["password"]);
        }


        //Validate credentials
        if (empty($username_err) && empty($password_err)) {
            //Prepare SQL
            $sql = "SELECT id, username, password, roleID FROM tblUsers WHERE username = ?";

            if ($stmt = mysqli_prepare($link, $sql)) {
                //Bind variables to the prepared statement asd perams
                mysqli_stmt_bind_param($stmt, "s", $param_username);

                //Set parameters
                $param_username = $username;

                //Execute SQL
                if (mysqli_stmt_execute($stmt)) {
                    //Store result
                    mysqli_stmt_store_result($stmt);

                    //Check if username exists, if yes then verify password
                    if (mysqli_stmt_num_rows($stmt) == 1) {
                        var_dump($stmt);
                        //Bind result variables
                        mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $roleID);
                        if (mysqli_stmt_fetch($stmt)) {
                            if (password_verify($password, $hashed_password)) {
                                //password is correct start a new session
                                session_start();
                                session_regenerate_id(true);

                                //store data in session variables 
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["username"] = $username;
                                $_SESSION["roleID"] = $roleID;

                                // Redirect user to welcome page
                                header("location: dashboard.php");


                            } else {
                                //display error message
                                $password_err = "Password invalid, please try again.";
                            }
                        }
                    } else {
                        //Display username error message
                        $username_err = "invalid username";
                    }
                } else {
                    echo "Oops, something went wrong!";
                }

            }

            //Close statement
            mysqli_stmt_close($stmt);
        }
    }

    ?>


    <div class="login-container">
        <h2>Login</h2>
        <p>Please fill in your credentials to login.</p>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" value="<?php echo $username; ?>">
                <span class="help-block">
                    <?php echo $username_err; ?>
                </span>
            </div>

            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control">
                <span class="help-block">
                    <?php echo $password_err; ?>
                </span>
            </div>

            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>

        
        </form>
    </div>

</body>

</html>