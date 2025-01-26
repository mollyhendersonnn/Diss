<?php

// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("../connection.php");

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["success" => false, "message" => "You must be logged in to delete a user."]);
    exit;
}

// Check if userID is provided
if (!isset($_GET['userID']) || empty($_GET['userID'])) {
    echo json_encode(["success" => false, "message" => "No user ID provided."]);
    exit;
}

$userID = intval($_GET['userID']); // Ensure userID is an integer

// Delete the user from tbl_users
$query = "DELETE FROM tbl_users WHERE userID = ?";
if ($stmt = mysqli_prepare($link, $query)) {
    mysqli_stmt_bind_param($stmt, "i", $userID);

    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo json_encode(["success" => true, "message" => "User deleted successfully."]);
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
