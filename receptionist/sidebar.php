<?php
// receptionist/sidebar.php
?>
<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h2><?php echo SITE_NAME; ?></h2>
        <p>Receptionist Panel</p>
    </div>
    <ul class="sidebar-menu">
        <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">Dashboard</a></li>
        <li><a href="calendar.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'calendar.php' ? 'active' : ''; ?>">Calendar</a></li>
        <li><a href="checkin.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'checkin.php' ? 'active' : ''; ?>">Check-in</a></li>
        <li><a href="checkout.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'checkout.php' ? 'active' : ''; ?>">Check-out</a></li>
        <li><a href="guests.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'guests.php' ? 'active' : ''; ?>">Guests</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>