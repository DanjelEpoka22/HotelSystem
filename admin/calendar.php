<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Calendar - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1>Master Calendar</h1>
                <p>View all reservations and room availability</p>
            </div>

            <!-- Calendar Filters -->
            <div class="dashboard-section">
                <div class="calendar-filters">
                    <div class="form-group">
                        <label class="form-label">Filter by Room:</label>
                        <select id="roomFilter" class="form-control">
                            <option value="">All Rooms</option>
                            <?php
                            $query = "SELECT * FROM rooms ORDER BY room_number";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            
                            while ($room = $stmt->fetch(PDO::FETCH_ASSOC)):
                            ?>
                            <option value="<?php echo $room['id']; ?>">
                                <?php echo $room['room_number'] . ' - ' . getRoomTypeName($room['room_type']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Filter by Source:</label>
                        <select id="sourceFilter" class="form-control">
                            <option value="">All Sources</option>
                            <option value="website">Website</option>
                            <option value="booking_com">Booking.com</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Calendar -->
            <div class="dashboard-section">
                <div id="calendar"></div>
            </div>

            <!-- Legend -->
            <div class="dashboard-section">
                <h3>Legend</h3>
                <div class="calendar-legend">
                    <div class="legend-item">
                        <span class="legend-color website"></span>
                        <span>Website Reservation</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color booking"></span>
                        <span>Booking.com Reservation</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color checked-in"></span>
                        <span>Checked In</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color maintenance"></span>
                        <span>Maintenance</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="../assets/js/calendar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: '../api/calendar_events.php',
                eventClick: function(info) {
                    const eventObj = info.event;
                    alert(
                        'Reservation Details:\n\n' +
                        'Guest: ' + eventObj.extendedProps.guestName + '\n' +
                        'Room: ' + eventObj.extendedProps.roomNumber + '\n' +
                        'Status: ' + eventObj.extendedProps.status + '\n' +
                        'Source: ' + eventObj.extendedProps.source
                    );
                },
                eventDidMount: function(info) {
                    // Add custom styling based on reservation source
                    if (info.event.extendedProps.source === 'booking_com') {
                        info.el.style.backgroundColor = '#e74c3c';
                        info.el.style.borderColor = '#e74c3c';
                    } else if (info.event.extendedProps.status === 'checked_in') {
                        info.el.style.backgroundColor = '#27ae60';
                        info.el.style.borderColor = '#27ae60';
                    } else if (info.event.extendedProps.status === 'maintenance') {
                        info.el.style.backgroundColor = '#f39c12';
                        info.el.style.borderColor = '#f39c12';
                    }
                }
            });

            calendar.render();

            // Filter functionality
            document.getElementById('roomFilter').addEventListener('change', function() {
                calendar.refetchEvents();
            });

            document.getElementById('sourceFilter').addEventListener('change', function() {
                calendar.refetchEvents();
            });
        });
    </script>
</body>
</html>