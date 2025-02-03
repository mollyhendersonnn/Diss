<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include_once("connection.php");
include_once("navigation.php");

// Fetch events from the tbl_events table
$sql = "SELECT eventID, eventTitle, eventStart, eventEnd FROM tbl_events";
$result = mysqli_query($link, $sql);

$sqlarch = "SELECT archiveID, eventTitle, eventStart, eventEnd FROM tbl_archive";
$resultarch = mysqli_query($link, $sqlarch);

$events = [];
while ($row = mysqli_fetch_assoc($result)) {
    $events[] = [
        "id" => $row["eventID"],
        "title" => $row['eventTitle'],
        "start" => $row['eventStart'],
        "end" => $row['eventEnd'],
    ];
}

$archevents = [];
while ($row = mysqli_fetch_assoc($resultarch)) {
    $archevents[] = [
        "idarch" => $row["archiveID"],
        "title" => $row['eventTitle'],
        "start" => $row['eventStart'],
        "end" => $row['eventEnd'],
    ];
}

// Fetch UK holidays using an API
$year = date("Y");
$holidaysApiUrl = "https://www.gov.uk/bank-holidays.json";
$holidays = [];

try {
    $response = file_get_contents($holidaysApiUrl);
    $data = json_decode($response, true);

    foreach ($data['england']['events'] as $holiday) {
        $holidays[] = [
            "title" => $holiday['title'],
            "start" => $holiday['date'],
            "end" => $holiday['date'],
        ];
    }
} catch (Exception $e) {
    error_log("Error fetching holidays: " . $e->getMessage());
}

// Merge holidays with database events
$allEvents = array_merge($events, $archevents, $holidays);


// Pass events to JavaScript as a JSON object
echo "<script>const dbEvents = " . json_encode($allEvents, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ";</script>";
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, 
                initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <title>Event Calendar</title>
</head>

<body>
    <div class="container">
        <br>
        <div id="right">
        <button id="previous" aria-label="Previous Month" onclick="previous()">‹</button>
        <h3 id="monthAndYear"></h3>
        <button id="next" aria-label="Next Month" onclick="next()">›</button>
        </div>
    </div>
        <br>
        <table class="table-calendar" id="calendar" data-lang="en">
            <thead id="thead-month"></thead>
            <!-- Table body for displaying the calendar -->
            <tbody id="calendar-body"></tbody>
        </table>
        <div class="footer-container-calendar">
            <label for="month">Jump To: </label>
            <!-- Dropdowns to select a specific month and year -->
            <select id="month" onchange="jump()">
                <option value=0>Jan</option>
                <option value=1>Feb</option>
                <option value=2>Mar</option>
                <option value=3>Apr</option>
                <option value=4>May</option>
                <option value=5>Jun</option>
                <option value=6>Jul</option>
                <option value=7>Aug</option>
                <option value=8>Sep</option>
                <option value=9>Oct</option>
                <option value=10>Nov</option>
                <option value=11>Dec</option>
            </select>
            <!-- Dropdown to select a specific year -->
            <select id="year" onchange="jump()"></select>
        </div>
    </div>
    </div>
    </div>

</body>

</html>


<script>
    //Function to generate a range of years
    function generate_year_range(start, end) {
        let years = "";
        for (let year = start; year <= end; year++) {
            years += "<option value='" +
                year + "'>" + year + "</option>";
        }
        return years;
    }

    //Set date variables
    today = new Date();
    currentMonth = today.getMonth();
    currentYear = today.getFullYear();
    selectYear = document.getElementById("year");
    selectMonth = document.getElementById("month");
    createYear = generate_year_range(1970, 2050);
    document.getElementById("year").innerHTML = createYear;

    let calendar = document.getElementById("calendar");

    let months = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December"
    ];
    let days = [
        "Sun", "Mon", "Tue", "Wed",
        "Thu", "Fri", "Sat"];

    let dataHead = "<tr>";
    for (dhead in days) {
        dataHead += "<th data-days='" +
            days[dhead] + "'>" +
            days[dhead] + "</th>";
    }
    dataHead += "</tr>";

    document.getElementById("thead-month").innerHTML = dataHead;

    monthAndYear =
        document.getElementById("monthAndYear");
    showCalendar(currentMonth, currentYear);

    //Function to go to the next month
    function next() {
        currentYear = currentMonth === 11 ?
            currentYear + 1 : currentYear;
        currentMonth = (currentMonth + 1) % 12;
        showCalendar(currentMonth, currentYear);
    }

    //Function to go to the previous month
    function previous() {
        currentYear = currentMonth === 0 ?
            currentYear - 1 : currentYear;
        currentMonth = currentMonth === 0 ?
            11 : currentMonth - 1;
        showCalendar(currentMonth, currentYear);
    }

    //Function to go to a specific month and year
    function jump() {
        currentYear = parseInt(selectYear.value);
        currentMonth = parseInt(selectMonth.value);
        showCalendar(currentMonth, currentYear);
    }

    // Calendar
    function showCalendar(month, year) {
    let firstDay = new Date(year, month, 1).getDay();
    let tbl = document.getElementById("calendar-body");
    tbl.innerHTML = ""; 

    monthAndYear.innerHTML = months[month] + " " + year;
    selectYear.value = year;
    selectMonth.value = month;

    let date = 1;
    for (let i = 0; i < 6; i++) { 
        let row = document.createElement("tr");


        for (let j = 0; j < 7; j++) { // 7 days in a week
            if (i === 0 && j < (firstDay === 0 ? 6 : firstDay - 1)) { 
                let cell = document.createElement("td");
                row.appendChild(cell);
            } else if (date > daysInMonth(month, year)) {
                break; 
            } else {
                let cell = document.createElement("td");
                cell.setAttribute("data-date", date);
                cell.setAttribute("data-month", month + 1);
                cell.setAttribute("data-year", year);
                cell.classList.add("date-picker");

                // Add the date number at the top-left corner
                cell.innerHTML = `<span class="day-number">${date}</span>`;

                // Check for events on this day
                const cellDate = new Date(year, month, date).toISOString().split("T")[0];
                const eventsForDay = dbEvents.filter(event => {
                    const startDate = new Date(event.start).toISOString().split("T")[0];
                    const endDate = new Date(event.end).toISOString().split("T")[0];
                    return cellDate >= startDate && cellDate <= endDate;
                });

                // If there are events, show the indicator with a pop-up tooltip
                if (eventsForDay.length > 0) {
                    const eventIndicator = document.createElement("span");
                    eventIndicator.classList.add("event-indicator");
                    eventIndicator.innerText = eventsForDay.length;
                    cell.appendChild(eventIndicator);

                // Tooltip container
                    const tooltip = document.createElement("div");
                    tooltip.classList.add("event-tooltip");
                // tooltip.innerHTML = eventsForDay.map(event => `<div>${event.title}</div>`).join("");
                    
                 // Create clickable links for event titles
                       tooltip.innerHTML = eventsForDay.map(event => {
                       let eventLink = '';

               // Check if it's a main event or archived event and build the corresponding link
                      if (event.id) {
                       eventLink = `<a href='events/eventDetails.php?eventID=${event.id}' class='event-link'>${event.title}</a>`;
                     } else if (event.idarch) {
                       eventLink = `<a href='events/archiveEventDetails.php?eventID=${event.idarch}' class='event-link'>${event.title}</a>`;
                       }

                      return `<div class='tooltip-item'>${eventLink}</div>`;
                     }).join("<br>");
                    



    // Attach hover event listeners for showing/hiding the tooltip
    let tooltipVisible = false;


                    // Attach hover event listeners
                    eventIndicator.addEventListener("mouseover", function () {
                        tooltip.style.display = "block";
                        tooltipVisible = true;
                    });
                    eventIndicator.addEventListener("mouseout", function () {
                        if (!tooltip.matches(':hover')) {
                        tooltip.style.display = "none";
                        tooltipVisible = false;
                    }
                    });

                    tooltip.addEventListener("mouseover", function () {
                    tooltip.style.display = "block";  // Keep it visible when hovering over the tooltip
                    });

                    tooltip.addEventListener("mouseout", function () {
                     // Only hide the tooltip if the mouse is not over the event indicator or the tooltip itself
                      if (!eventIndicator.matches(':hover')) {
                        tooltip.style.display = "none";
                        tooltipVisible = false;
                        }
    });

                    // Append tooltip to the cell
                    cell.appendChild(tooltip);
                }

                // Highlight today's date
                if (
                    date === today.getDate() &&
                    year === today.getFullYear() &&
                    month === today.getMonth()
                ) {
                    cell.classList.add("selected");
                }

                row.appendChild(cell);
                date++;
            }
        }
        tbl.appendChild(row);
    }
}



//daysInMonth function
function daysInMonth(month, year) {
    return new Date(year, month + 1, 0).getDate();
}

</script>