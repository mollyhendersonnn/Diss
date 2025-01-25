<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include_once("connection.php");
include_once("navigation.php");

// Fetch events from the `tbl_events` table
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Event Calendar</title>
</head>

<body>
    <div class="container">
        <div id="right">
            <button id="previous" onclick="previous()">‹</button> 
            <h3 id="monthAndYear"></h3> 
            <button id="next" onclick="next()">›</button>
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <a href="createEvent.php" class="btn btn-primary mb-3">Create Event</a>
            <?php endif; ?>
        </div>
        <table class="table-calendar" id="calendar" data-lang="en">
            <thead id="thead-month"></thead>
            <tbody id="calendar-body"></tbody>
        </table>
        <div class="footer-container-calendar">
            <label for="month">Jump To: </label>
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
            <select id="year" onchange="jump()"></select>
        </div>
    </div>
</body>

</html>

<script>
    // Fetch the UK Bank Holidays data
    async function fetchUKHolidays(month, year) {
        const url = `https://www.gov.uk/bank-holidays.json`;

        try {
            const response = await fetch(url);
            const data = await response.json();

            // Select the England and Wales holidays (you can choose other regions too)
            const holidays = data.england_and_wales.public_holidays;

            // Filter holidays for the selected month and year
            const filteredHolidays = holidays.filter(holiday => {
                const holidayDate = new Date(holiday.date);
                return holidayDate.getFullYear() === year && holidayDate.getMonth() === month;
            });

            return filteredHolidays;
        } catch (error) {
            console.error("Error fetching holidays:", error);
            return [];
        }
    }

    // Function to show the calendar
    async function showCalendar(month, year) {
        let firstDay = new Date(year, month, 1).getDay();
        let tbl = document.getElementById("calendar-body");
        tbl.innerHTML = ""; // Clear calendar body

        monthAndYear.innerHTML = `${months[month]} ${year}`;
        selectYear.value = year;
        selectMonth.value = month;

        let date = 1;
        for (let i = 0; i < 6; i++) {
            let row = document.createElement("tr");
            for (let j = 0; j < 7; j++) {
                if (i === 0 && j < firstDay) {
                    let cell = document.createElement("td");
                    row.appendChild(cell);
                } else if (date > daysInMonth(month, year)) {
                    break;
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

                    // Fetch and add holiday titles to the day cell (if any)
                    const holidaysForDay = await fetchUKHolidays(month, year);
                    holidaysForDay.forEach(holiday => {
                        if (holiday.date === cellDate) {
                            const holidayTitle = document.createElement("div");
                            holidayTitle.classList.add("event-title");
                            holidayTitle.innerText = holiday.title;
                            cell.appendChild(holidayTitle);
                        }
                    });

                    cell.innerHTML = `<span>${date}</span>` + cell.innerHTML;
                    if (date === today.getDate() &&
                        year === today.getFullYear() &&
                        month === today.getMonth()) {
                        cell.className = "selected";
                    }

                    row.appendChild(cell);
                    date++;
                }
            }
            tbl.appendChild(row);
        }
    }

    function daysInMonth(month, year) {
        return new Date(year, month + 1, 0).getDate();
    }
</script>
