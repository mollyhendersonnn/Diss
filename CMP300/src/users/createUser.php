<?php
//start the session if there isnt one detected
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("../connection.php");
include_once("../navigation.php"); 
include_once("../clean.php"); 

//define the variables to empty strings
$username = $password = $firstname = $roleID = $groupID = "";
$username_err = $password_err = $firstname_err = $roleID_err = $groupID_err = "";


//get roles for dropdown
$sql_roles = "SELECT roleID, roleName FROM tbl_roles"; 
$result_roles = mysqli_query($link, $sql_roles);

if ($result_roles) {
    $tbl_roles = mysqli_fetch_all($result_roles, MYSQLI_ASSOC); 
} else {
    echo "Error fetching roles: " . mysqli_error($link);
}

// get groups for dropdown
$sql_group = "SELECT groupID, groupName FROM tbl_group";
$result_group = mysqli_query($link, $sql_group);

if ($result_group) {
    $tbl_group = mysqli_fetch_all($result_group, MYSQLI_ASSOC);
} else {
    echo "Error fetching roles: " . mysqli_error($link);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //sanitise the inputs
    $username = isset($_POST["username"]) ? trim($_POST["username"]) : "";
    $password = isset($_POST["password"]) ? trim($_POST["password"]) : "";
    $firstname = isset($_POST["firstname"]) ? trim($_POST["firstname"]) : "";
    $roleID = isset($_POST["roleID"]) ? (int) $_POST["roleID"] : 0;
    $groupID = isset($_POST["groupID"]) ? (int) $_POST["groupID"] : 0;

    //ensure everything has been filled in
    if (empty($username)) $username_err = "Please enter a username.";
    if (empty($password)) $password_err = "Please enter a password.";
    if (empty($firstname)) $firstname_err = "Please enter the name.";
    if (empty($roleID)) $roleID_err = "Please enter a role ID.";
    if (empty($groupID)) $groupID_err = "Please enter a group ID.";

    //check for any erros
    if (empty($username_err) && empty($password_err) && empty($firstname_err) && empty($roleID_err) && empty($groupID_err)) {
        //dont create user if username exists
        $sql = "SELECT * FROM tbl_users WHERE enterpriseID = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $username);

            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) > 0) {
                   echo '<p class="alert alert-success">"Enterprise ID exists, please try again.";</p>';
                } else {
                    //hash the password using PHP hashing
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    //insert details into db
                    $sql_insert = "INSERT INTO tbl_users (roleID, groupID, enterpriseID, password, firstName) VALUES (?, ?, ?, ?, ?)";
                    if ($stmt_insert = mysqli_prepare($link, $sql_insert)) {
                        mysqli_stmt_bind_param($stmt_insert, "iisss", $roleID, $groupID, $username, $hashed_password, $firstname);

                        if (mysqli_stmt_execute($stmt_insert)) {
                            $_SESSION['success_message'] = "User Created Successfully!";
                            header("Location: users.php");
                            exit();
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

//close the connection
mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
    <div class="container mt-5">
        <h2>Create User</h2>
        <p>Fill out this form to create a new user.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="username">Enterprise ID</label>
                <input type="text" id="username" name="username" class="form-control w-50" <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control w-50" <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <label for="firstname">Name</label>
                <input type="text" id="firstname" name="firstname"
                    class="form-control w-50 <?php echo (!empty($firstname_err)) ? 'is-invalid' : ''; ?>"
                    value="<?php echo $firstname; ?>">
                <span class="invalid-feedback"><?php echo $firstname_err; ?></span>
            </div>
             <div class="form-group">
    <label for="roleID">Role ID</label>
    <select id="roleID" name="roleID" class="form-control w-50 <?php echo (!empty($roleID_err)) ? 'is-invalid' : ''; ?>">
        <option value="">Select a Role</option>
        <?php
        foreach ($tbl_roles as $role) {
            echo '<option value="' . $role['roleID'] . '">' . htmlspecialchars($role['roleName']) . '</option>';
        }
        ?>
    </select>
    <span class="invalid-feedback"><?php echo $roleID_err; ?></span>
</div>
            <div class="form-group">
    <label for="groupID">Group ID</label>
    <select id="groupID" name="groupID" class="form-control w-50 <?php echo (!empty($groupID_err)) ? 'is-invalid' : ''; ?>">
        <option value="">Select a Group</option>
        <?php
        foreach ($tbl_group as $group) {
            echo '<option value="' . $group['groupID'] . '">' . htmlspecialchars($group['groupName']) . '</option>';
        }
        ?>
    </select>
    <span class="invalid-feedback"><?php echo $groupID_err; ?></span>
</div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</body>

</html>