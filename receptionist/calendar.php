<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('receptionist');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Calendar - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><?php echo SITE_NAME; ?></h2>
                <p>Receptionist Panel</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="calendar.php" class="active">Calendar</a></li>
                <li><a href="checkin.php">Check-in</a></li>
                <li><a href="checkout.php">Check-out</a></li>
                <li><a href="guests.php">Guests</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1>Reservation Calendar</h1>
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
                        <label class="form-label">Show:</label>
                        <select id="statusFilter" class="form-control">
                            <option value="">All Reservations</option>
                            <option value="checked_in">Checked-in Only</option>
                            <option value="confirmed">Confirmed Only</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Calendar -->
            <div class="dashboard-section">
                <div id="calendar"></div>
            </div>

            <!-- Today's Summary -->
            <div class="dashboard-section">
                <h3>Today's Summary - <?php echo date('F j, Y'); ?></h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📥</div>
                        <div class="stat-info">
                            <h3>
                                <?php
                                $query = "SELECT COUNT(*) as total FROM reservations 
                                         WHERE check_in = CURDATE() 
                                         AND status = 'confirmed'";
                                $stmt = $db->prepare($query);
                                $stmt->execute();
                                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                echo $result['total'];
                                ?>
                            </h3>
                            <p>Arrivals Today</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">📤</div>
                        <div class="stat-info">
                            <h3>
                                <?php
                                $query = "SELECT COUNT(*) as total FROM reservations 
                                         WHERE check_out = CURDATE() 
                                         AND status = 'checked_in'";
                                $stmt = $db->prepare($query);
                                $stmt->execute();
                                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                echo $result['total'];
                                ?>
                            </h3>
                            <p>Departures Today</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">🏨</div>
                        <div class="stat-info">
                            <h3>
                                <?php
                                $query = "SELECT COUNT(*) as total FROM reservations 
                                         WHERE status = 'checked_in'";
                                $stmt = $db->prepare($query);
                                $stmt->execute();
                                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                echo $result['total'];
                                ?>
                            </h3>
                            <p>Current Guests</p>
                        </div>
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
                    const reservationId = eventObj.id;
                    
                    // Open reservation details in new tab
                    window.open('../admin/reservation_details.php?id=' + reservationId, '_blank');
                },
                eventDidMount: function(info) {
                    // Add custom styling based on reservation status
                    if (info.event.extendedProps.status === 'checked_in') {
                        info.el.style.backgroundColor = '#27ae60';
                        info.el.style.borderColor = '#27ae60';
                    } else if (info.event.extendedProps.source === 'booking_com') {
                        info.el.style.backgroundColor = '#e74c3c';
                        info.el.style.borderColor = '#e74c3c';
                    }
                }
            });

            calendar.render();

            // Filter functionality
            document.getElementById('roomFilter').addEventListener('change', function() {
                calendar.refetchEvents();
            });

            document.getElementById('statusFilter').addEventListener('change', function() {
                calendar.refetchEvents();
            });
        });
    </script>
</body>
</html>