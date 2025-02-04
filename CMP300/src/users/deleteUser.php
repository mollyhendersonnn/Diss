<?php
//start the session if there isnt one detected
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("../connection.php");
include_once("../clean.php"); 

//check user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["success" => false, "message" => "You must be logged in to delete a user."]);
    exit;
}

//get userID
if (!isset($_GET['userID']) || empty($_GET['userID'])) {
    echo json_encode(["success" => false, "message" => "No user ID provided."]);
    exit;
}
$userID = intval($_GET['userID']); 

//delete the user from tbl_users
$query = "DELETE FROM tbl_users WHERE userID = ?";
if ($stmt = mysqli_prepare($link, $query)) {
    mysqli_stmt_bind_param($stmt, "i", $userID);

    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            $_SESSION['success_message'] = "User Created Successfully!";
            header("Location: users.php");
            exit();
        } else {
            echo json_encode(["success" => false, "message" => "User not found or already deleted."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete the user."]);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["success" => false, "message" => "Failed to prepare delete query."]);
}

?>
