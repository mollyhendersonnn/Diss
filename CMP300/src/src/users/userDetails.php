<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("../connection.php");
include_once("../navigation.php");  


// Check if enterpriseID is passed in the URL
if (isset($_GET['userID'])) {
    $userID = $_GET['userID'];

    // Fetch user details based on enterpriseID
    $query = "SELECT u.userID, u.enterpriseID, u.firstName, u.groupID, u.roleID, r.roleName, g.groupName
              FROM tbl_users u 
              INNER JOIN tbl_roles r ON u.roleID = r.roleID
              INNER JOIN tbl_group g ON u.groupID = g.groupID 
              WHERE u.userID = ?";

    if ($stmt = mysqli_prepare($link, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $userID); 
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            // Fetching groupName and roleName from the query result
            $groupName = $user['groupName'];
            $roleName = $user['roleName'];
        } else {
            echo "<p>User not found.</p>";
            exit;
        }
    } else {
        echo "<p>Error preparing query: " . htmlspecialchars(mysqli_error($link)) . "</p>";
        exit;
    }
} else {
    echo "<p>No user specified.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container mt-5">
        <h2><?php echo htmlspecialchars($user['enterpriseID']); ?></h2>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['firstName']); ?></p>
        <p><strong>Group:</strong> <?php echo htmlspecialchars($groupName); ?></p>
        <p><strong>Role:</strong> <?php echo htmlspecialchars($roleName); ?></p>

        <a href="updateUser.php?userID=<?php echo $userID; ?>" class="btn btn-success mt-3">Update User</a>
        <a href="deleteUser.php?userID=<?php echo $userID; ?>" class="btn btn-danger mt-3">Delete User</a>
    </div>
</body>
</html>
