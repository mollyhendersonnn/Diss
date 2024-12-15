
<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include_once("connection.php");
include_once("navigation.php");

date_default_timezone_set('Europe/London'); // Set your timezone

define("ADAY", (60*60*24));

if ((!isset($_POST['month'])) || (!isset($_POST['year']))) {
    $nowArray = getdate();
    $month = $nowArray['mon'];
    $year = $nowArray['year'];
} else {
    $month = $_POST['month'];
    $year = $_POST['year'];
}

// Get the first day of the month
$start = mktime(12, 0, 0, $month, 1, $year);
$firstDayArray = getdate($start);

// Get the total number of days in the month
$totalDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="stylesheet" type="text/css" href="css/styles.css" />
    <title>Calendar Booking</title>
</head>
<body>
    
<h1>Calendar Booking</h1>  

<form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">

    <select name="month" class="monthSelect">
    <?php
    $months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
    for ($x = 1; $x <= count($months); $x++) {
        echo "<option value=\"$x\"";
        if ($x == $month) {
            echo " selected";
        }
        echo ">" . $months[$x - 1] . "</option>";
    }
    ?>
    </select>

    <select name="year" class="yearSelect">
    <?php
    for ($x = 2022; $x <= 2023; $x++) {
        echo "<option";
        if ($x == $year) {
            echo " selected";
        }
        echo ">$x</option>";
    }
    ?>
    </select>

    <button type="submit" class="displayBtn" name="submit" value="submit">Display</button>
</form>
<br/>

<?php
// DISPLAY THE DAYS OF THE WEEK
$days = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
echo "<table><tr>\n";
foreach ($days as $day) {
    echo "<td class='daysOfTheWeek'>$day</td>\n";
}
echo "</tr><tr>";

// FIND THE STARTING EMPTY CELLS
for ($i = 0; $i < $firstDayArray['wday']; $i++) {
    echo "<td>&nbsp;</td>\n";  // empty cells before the first day
}


// DISPLAY THE DAYS OF THE MONTH
for ($day = 1; $day <= $totalDays; $day++) {
    echo "<td>$day</td>\n";

    // Check if a new row is needed after Saturday (6th day of the week)
    if (($firstDayArray['wday'] + $day - 1) % 7 == 6) {
        echo "</tr><tr>\n";
    }
}

// FILL IN THE REMAINING EMPTY CELLS
$lastDayOfWeek = ($firstDayArray['wday'] + $totalDays - 1) % 7;
for ($i = $lastDayOfWeek; $i < 6; $i++) {
    echo "<td>&nbsp;</td>\n";  // empty cells after the last day
}

echo "</tr></table>";
?>

</body>
</html>
