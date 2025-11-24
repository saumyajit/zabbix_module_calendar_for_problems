<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Incident Calendar</title>
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <style>
        body, html {
            font-family: Trebuchet MS, Helvetica Neue, Arial, sans-serif !important;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }
        #calendar {
            width: 98%;
            margin: 10px auto;
            background: white;
            border-radius: 6px;
            box-shadow: 0 1px 8px rgba(0,0,0,0.09);
        }
        .incident-details {
            /* Customize popup/modal styles as needed */
        }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Incident Calendar - Problem Events</h2>
    <div id="calendar"></div>

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var events = [];
            <?php if (!empty($incidents)): ?>
                <?php foreach ($incidents as $incident): ?>
                    events.push({
                        title: "<?php echo addslashes($incident['title']); ?>",
                        start: "<?php echo $incident['date']; ?>",
                        extendedProps: {
                            severity: "<?php echo $incident['severity']; ?>",
                            acknowledged: "<?php echo $incident['acknowledged']; ?>",
                            host: "<?php echo addslashes($incident['host']); ?>",
                            group: "<?php echo addslashes($incident['group']); ?>"
                        },
                        color: "<?php
                            // Color code by severity (example logic)
                            switch ($incident['severity']) {
                                case 'Disaster': echo '#d9534f'; break;
                                case 'High':     echo '#f0ad4e'; break;
                                case 'Average':  echo '#5bc0de'; break;
                                case 'Warning':  echo '#f7ecb5'; break;
                                case 'Information': echo '#5cb85c'; break;
                                default:         echo '#999999'; break;
                            }
                        ?>"
                    });
                <?php endforeach; ?>
            <?php endif; ?>

            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: events,
                eventClick: function(info) {
                    var e = info.event.extendedProps;
                    alert(
                        "Event: " + info.event.title +
                        "\nHost: " + e.host +
                        "\nGroup: " + e.group +
                        "\nSeverity: " + e.severity +
                        "\nAcknowledged: " + (e.acknowledged == "1" ? "Yes" : "No")
                    );
                }
            });
            calendar.render();
        });
    </script>
</body>
</html>
