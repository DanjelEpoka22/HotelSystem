<!-- Admin Sidebar Component -->
<div class="sidebar">
    <div class="sidebar-header">
        <h2><?php echo SITE_NAME; ?></h2>
        <p>Admin Panel</p>
    </div>
    <ul class="sidebar-menu">
        <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">Dashboard</a></li>
        <li><a href="rooms.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'rooms.php' ? 'active' : ''; ?>">Rooms Management</a></li>
        <li><a href="reservations.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'reservations.php' ? 'active' : ''; ?>">Reservations</a></li>
        <li><a href="calendar.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'calendar.php' ? 'active' : ''; ?>">Calendar</a></li>
        <li><a href="users.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">Users</a></li>
        <li><a href="staff.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'staff.php' ? 'active' : ''; ?>">Staff</a></li>
        <li><a href="reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?>">Reports</a></li>
        <li><a href="settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">Settings</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>