<?php
//Define credentials for connecting to the server
define('DB_SERVER', '213.171.200.34');
define('DB_USERNAME', 'mhenderson');
define('DB_PASSWORD', 'GingerBreadMan20');
define('DB_NAME', 'mhenderson');

//Connect to PHPMyAdmin
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

//Test connection
if ($link) {
    echo mysqli_connect_error();
} else {
    echo '<h1>Not connected to MySQL</h1>';
    echo mysqli_connect_error();
}
?>






