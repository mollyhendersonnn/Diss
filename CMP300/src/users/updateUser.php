<?php
//start the session if there isnt one detected
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("../connection.php");
include_once("../navigation.php"); 
 

//check the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["success" => false, "message" => "You must be logged in to update a user."]);
    exit;
}

//check the userID
if (!isset($_GET['userID'])) {
    echo json_encode(["success" => false, "message" => "No user ID provided."]);
    exit;
}
$userID = $_GET['userID'];

//get all user information based on userID
$query = "SELECT * FROM tbl_users WHERE userID = ?";
if ($stmt = mysqli_prepare($link, $query)) {
    mysqli_stmt_bind_param($stmt, "i", $userID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["success" => false, "message" => "Failed to fetch user details."]);
    exit;
}

//form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = htmlspecialchars(trim($_POST["firstName"]));
    $roleID = $_POST["roleID"];
    $groupID = $_POST["groupID"];
    $enterpriseID = htmlspecialchars(trim($_POST["enterpriseID"]));
    $password = password_hash(trim($_POST["password"]), PASSWORD_DEFAULT); 

    //prep query
    $query = "UPDATE tbl_users SET firstName = ?, roleID = ?, groupID = ?, enterpriseID = ?, password = ? WHERE userID = ?";

    if ($stmt = mysqli_prepare($link, $query)) {
        mysqli_stmt_bind_param($stmt, "siissi", $firstName, $roleID, $groupID, $enterpriseID, $password, $userID);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "User Created Successfully!";
            header("Location: users.php");
            exit();
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update user."]);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(["success" => false, "message" => "Database query preparation failed."]);
    }
} else {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Update User</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../css/styles.css">
    </head>
    <body>
        <div class="container mt-5">
            <h2>Update User</h2>
            <br>
            <form action="updateUser.php?userID=<?php echo $userID; ?>" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="firstName" class="form-label">Name</label>
                    <input type="text" class="form-control w-50" id="firstName" name="firstName" value="<?php echo htmlspecialchars($user['firstName']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="roleID" class="form-label">Role</label>
                    <input type="number" class="form-control w-50" id="roleID" name="roleID" value="<?php echo htmlspecialchars($user['roleID']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="groupID" class="form-label">Group</label>
                    <input type="number" class="form-control w-50" id="groupID" name="groupID" value="<?php echo htmlspecialchars($user['groupID']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="enterpriseID" class="form-label">Enterprise ID</label>
                    <input type="text" class="form-control w-50" id="enterpriseID" name="enterpriseID" value="<?php echo htmlspecialchars($user['enterpriseID']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control w-50" id="password" name="password" placeholder="Enter a new password">
                </div>
                <button type="submit" class="btn btn-primary">Update User</button>
            </form>
        </div>
    </body>
    </html>
    <?php
}
?>
