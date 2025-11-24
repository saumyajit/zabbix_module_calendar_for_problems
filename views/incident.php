<?php declare(strict_types = 0); ?>

<!-- Including FullCalendar via CDN -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/daygrid/main.min.css' rel='stylesheet' />
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/timegrid/main.min.css' rel='stylesheet' />

<style>
    html, body {
        font-family: "Trebuchet MS", "Helvetica Neue", Helvetica, "Roboto", "Segoe UI", Arial, sans-serif !important;
        margin: 0;
        padding: 0;
        height: 100%;
    }
    #incidentCalendar {
        width: 98%;
        margin: 0 auto;
        height: calc(100% - 100px);
    }

    .incident-details {
        position: fixed;
        top: 0;
        right: -50%;
        width: 50%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        transition: right 0.3s ease;
        z-index: 999;
    }

    .incident-details-content {
        background-color: #fff;
        height: 100%;
        overflow-y: auto;
        padding: 20px;
    }

    .incident-title {
        background-color: #333;
        color: #fff;
        padding: 10px;
        font-weight: bold;
        font-size: 1.2em;
    }

    .incident-title.low { background-color: #008000; color: white; }      /* Info/Low */
    .incident-title.medium { background-color: #FFA500; color: white; }  /* Average */
    .incident-title.high { background-color: #FF0000; color: white; }    /* High */
    .incident-title.disaster { background-color: #800080; color: white; }/* Disaster */

    .incident-info {
        background-color: #0a466a;
        color: #fff;
        padding: 20px;
        margin-bottom: 10px;
        border-radius: 5px;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    #calendarTitle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: #0a466a;
        color: #fff;
        padding: 10px;
        font-weight: bold;
        font-size: 1.5em;
    }

    .export-button {
        margin-left: 20px;
        font-size: 12px;
        background-color: #fff;
        color: #0a466a;
        padding: 0px;
        border: 1px solid #0a466a;
        border-radius: 3px;
        cursor: pointer;
    }

    .fc-event {
        color: #fff !important;
        padding: 2px 5px;
    }

</style>

<div id="calendarTitle">
    <div class="title">Incident Calendar</div>
    <div style="display:flex;align-items:center;">
        <button class="export-button" id="exportCsvBtn" title="Export report as CSV">Export</button>
    </div>
</div>

<div id="incidentCalendar"></div>

<div class="incident-details">
    <div class="incident-details-content">
        <span class="close" onclick="closeIncidentDetails()">&times;</span>
        <div id="incidentInfoContent"></div>
    </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/daygrid/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/timegrid/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/interaction/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js'></script>

<script>
var incidentCalendar;
var incidentData = <?php echo json_encode($data['problems']); ?> || [];

function severityColor(severity) {
    switch(severity) {
        case '0': return { color: '#008000', label: 'Low' };
        case '1': return { color: '#FFFF00', label: 'Warning' };
        case '2': return { color: '#FFA500', label: 'Medium' };
        case '3': return { color: '#FF0000', label: 'High' };
        case '4': return { color: '#800080', label: 'Disaster' };
        default: return { color: '#808080', label: 'Unknown' };
    }
}

function initializeIncidentCalendar(data) {
    var events = data.map(p => {
        var startDate = moment.unix(p.clock);
        var endDate = startDate.clone().add(p.period || 86400, 'seconds'); // Default 1 day
        var severity = severityColor(p.severity);

        return {
            id: p.id,
            title: p.name,
            start: startDate.toISOString(),
            end: endDate.toISOString(),
            severity: severity.label,
            acknowledged: p.acknowledged ? 'Yes' : 'No',
            hosts: p.hosts || [],
            groups: p.groups || [],
            tags: p.tags || [],
            backgroundColor: severity.color,
            borderColor: severity.color
        };
    });

    var calendarEl = document.getElementById('incidentCalendar');
    if (incidentCalendar) incidentCalendar.destroy();

    incidentCalendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: events,
        eventClick: function(info) {
            openIncidentDetails(info.event);
        },
        dayMaxEvents: true,
        height: '100%'
    });

    incidentCalendar.render();
}

function openIncidentDetails(event) {
    var start = moment(event.start).format('YYYY-MM-DD HH:mm:ss');
    var end = moment(event.end).format('YYYY-MM-DD HH:mm:ss');

    var html = `
        <div class="incident-info">
            <p class="incident-title ${event.extendedProps.severity.toLowerCase()}">Incident - ${event.extendedProps.severity}</p>
            <p><strong>Title:</strong> ${event.title}</p>
            <p><strong>Start:</strong> ${start}</p>
            <p><strong>End:</strong> ${end}</p>
            <p><strong>Hosts:</strong> ${event.extendedProps.hosts.join(', ') || 'N/A'}</p>
            <p><strong>Groups:</strong> ${event.extendedProps.groups.join(', ') || 'N/A'}</p>
            <p><strong>Acknowledged:</strong> ${event.extendedProps.acknowledged}</p>
            <p><strong>Tags:</strong> ${event.extendedProps.tags.map(t => t.tag || t.key || '').join(', ')}</p>
        </div>
    `;
    document.getElementById('incidentInfoContent').innerHTML = html;
    document.querySelector('.incident-details').style.right = '0';
}

function closeIncidentDetails() {
    document.querySelector('.incident-details').style.right = '-50%';
    document.getElementById('incidentInfoContent').innerHTML = '';
}

document.addEventListener('DOMContentLoaded', function() {
    initializeIncidentCalendar(incidentData);

    document.getElementById('exportCsvBtn').addEventListener('click', function() {
        exportIncidentCSV();
    });
});

function exportIncidentCSV() {
    if (!incidentData.length) return alert('No incident data to export.');

    var rows = [['ID', 'Title', 'Start', 'End', 'Hosts', 'Groups', 'Severity', 'Acknowledged', 'Tags']];
    incidentData.forEach(p => {
        var start = moment.unix(p.clock).format('YYYY-MM-DD HH:mm:ss');
        var end = moment.unix(p.clock).add(p.period || 86400, 'seconds').format('YYYY-MM-DD HH:mm:ss');
        rows.push([
            p.id,
            p.name,
            start,
            end,
            (p.hosts || []).join(';'),
            (p.groups || []).join(';'),
            severityColor(p.severity).label,
            p.acknowledged ? 'Yes' : 'No',
            (p.tags || []).map(t => t.tag || t.key || '').join(';')
        ]);
    });

    var csvContent = rows.map(r => r.map(c => `"${String(c).replace(/"/g,'""')}"`).join(',')).join('\n');
    var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'incident_report.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
</script>
