<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include_once("connection.php");
include_once("navigation.php");

// Fetch events from the tbl_events table
$sql = "SELECT eventTitle, eventStart, eventEnd FROM tbl_events";
$result = mysqli_query($link, $sql);

$events = [];
while ($row = mysqli_fetch_assoc($result)) {
    $events[] = [
        "title" => $row['eventTitle'],
        "start" => $row['eventStart'],
        "end" => $row['eventEnd'],
    ];
}

// Pass events to JavaScript as a JSON object
echo "<script>const dbEvents = " . json_encode($events, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ";</script>";
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

            <!-- <button id="previous" onclick="previous()">‹</button> <h3 id="monthAndYear"></h3> <button id="next" onclick="next()">›</button>
   -->
            <!-- create event button might go here -->
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
    // Function to generate a range of 
    // years for the year select input
    function generate_year_range(start, end) {
        let years = "";
        for (let year = start; year <= end; year++) {
            years += "<option value='" +
                year + "'>" + year + "</option>";
        }
        return years;
    }

    // Initialize date-related letiables
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

    // Function to navigate to the next month
    function next() {
        currentYear = currentMonth === 11 ?
            currentYear + 1 : currentYear;
        currentMonth = (currentMonth + 1) % 12;
        showCalendar(currentMonth, currentYear);
    }

    // Function to navigate to the previous month
    function previous() {
        currentYear = currentMonth === 0 ?
            currentYear - 1 : currentYear;
        currentMonth = currentMonth === 0 ?
            11 : currentMonth - 1;
        showCalendar(currentMonth, currentYear);
    }

    // Function to jump to a specific month and year
    function jump() {
        currentYear = parseInt(selectYear.value);
        currentMonth = parseInt(selectMonth.value);
        showCalendar(currentMonth, currentYear);
    }

    //const apiCache = {}; // Cache for API responses

    // function fetchAPIData(month, year, callback) {
    //     const cacheKey = ${year}-${month};
    //     if (apiCache[cacheKey]) {
    //         console.log(Cache hit for ${cacheKey});
    //         callback(apiCache[cacheKey]); // Use cached data
    //         return;
    //     }

    //     console.log(Fetching API data for ${cacheKey});
    //     const url = https://holidays.abstractapi.com/v1/?api_key=a57bf4f136374e0b9fb1b7ef886aabe9&country=GB&year=${year}&month=${month + 1};
    //     const xhr = new XMLHttpRequest();

    //     xhr.onreadystatechange = function () {
    //         if (xhr.readyState === 4) {
    //             if (xhr.status === 200) {
    //                 const apiEvents = JSON.parse(xhr.responseText);
    //                 console.log("API Response:", apiEvents);
    //                 apiCache[cacheKey] = apiEvents; // Cache the result
    //                 callback(apiEvents);
    //             } else if (xhr.status === 429) {
    //                 alert("Rate limit exceeded. Please try again later.");
    //             } else {
    //                 console.error(Error fetching API data. Status: ${xhr.status});
    //             }
    //         }
    //     };

    //     xhr.open("GET", url, true);
    //     xhr.send();
    // }


    // // Function to check if an API event matches a database event
    // function matchEvent(apiEvent) {
    //     return dbEvents.find(dbEvent => {
    //         return (
    //             apiEvent.name.toLowerCase() === dbEvent.name.toLowerCase()
    //         );
    //     });
    // }

    // Function to add markers to the calendar
    // function addEventMarkers(tbl, apiEvents) {
    //     const cells = tbl.getElementsByTagName("td");

    //     for (const cell of cells) {
    //         if (cell.hasAttribute("data-date")) {
    //             const cellDate = new Date(
    //                 cell.getAttribute("data-year"),
    //                 cell.getAttribute("data-month") - 1,
    //                 cell.getAttribute("data-date")
    //             ).toISOString().split("T")[0];

    //             // Loop through API events and check if they match database events
    //             apiEvents.forEach(apiEvent => {
    //                 const matchedEvent = matchEvent(apiEvent);
    //                 if (matchedEvent) {
    //                     // Get the start date and duration from the matched event
    //                     const startDate = new Date(apiEvent.date);
    //                     const duration = parseInt(matchedEvent.duration);

    //                     // Highlight the corresponding cells in the calendar
    //                     for (let i = 0; i < duration; i++) {
    //                         const eventDate = new Date(startDate);
    //                         eventDate.setDate(startDate.getDate() + i);

    //                         const eventCell = Array.from(cells).find(cell => {
    //                             const eventCellDate = new Date(
    //                                 cell.getAttribute("data-year"),
    //                                 cell.getAttribute("data-month") - 1,
    //                                 cell.getAttribute("data-date")
    //                             ).toISOString().split("T")[0];
    //                             return eventCellDate === eventDate.toISOString().split("T")[0];
    //                         });

    //                         if (eventCell) {
    //                             eventCell.classList.add("event-marker");
    //                             eventCell.innerHTML += <span class="event-tooltip">${apiEvent.name}</span>;
    //                         }
    //                     }
    //                 }
    //             });
    //         }
    //     }
    // }


// Ensure firstDay is calculated correctly and that cells are being added
function showCalendar(month, year) {
    let firstDay = new Date(year, month, 1).getDay();
    let tbl = document.getElementById("calendar-body");
    tbl.innerHTML = ""; // Clear calendar body

    // Fix the template literal for monthAndYear
    monthAndYear.innerHTML = months[month] + ' ' + year;
    selectYear.value = year;
    selectMonth.value = month;

    let date = 1;
    for (let i = 0; i < 6; i++) {  // 6 rows (weeks) max
        let row = document.createElement("tr");
        for (let j = 0; j < 7; j++) { // 7 days in a week
            if (i === 0 && j < firstDay) {
                let cell = document.createElement("td");  // Empty cell for days before the first of the month
                row.appendChild(cell);
            } else if (date > daysInMonth(month, year)) {
                break;  // Stop when we exceed the number of days in the month
            } else {
                let cell = document.createElement("td");
                cell.setAttribute("data-date", date);
                cell.setAttribute("data-month", month + 1);
                cell.setAttribute("data-year", year);

                // Check for events on this day
                const cellDate = new Date(year, month, date).toISOString().split("T")[0];
                const eventsForDay = dbEvents.filter(event => {
                    const startDate = new Date(event.start).toISOString().split("T")[0];
                    const endDate = new Date(event.end).toISOString().split("T")[0];
                    return cellDate >= startDate && cellDate <= endDate;
                });

                // Add event titles to the day cell
                if (eventsForDay.length > 0) {
                    eventsForDay.forEach(event => {
                        const eventTitle = document.createElement("div");
                        eventTitle.classList.add("event-title");
                        eventTitle.innerText = event.title;
                        cell.appendChild(eventTitle);
                    });
                }

                // Add the date number to the cell
                cell.innerHTML = `<span>${date}</span>` + cell.innerHTML;

                // Highlight today's date
                if (
                    date === today.getDate() &&
                    year === today.getFullYear() &&
                    month === today.getMonth()
                ) {
                    cell.className = "selected";
                }

                row.appendChild(cell);
                date++;
            }
        }
        tbl.appendChild(row);
    }
}

// Make sure the daysInMonth function works correctly
function daysInMonth(month, year) {
    return new Date(year, month + 1, 0).getDate();
}

</script>