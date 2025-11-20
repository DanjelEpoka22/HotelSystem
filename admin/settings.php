<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
requireRole('admin');

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'setting_') === 0) {
            $setting_key = substr($key, 8);
            $setting_value = sanitizeInput($value);
            
            // Update setting in database
            $query = "INSERT INTO settings (setting_key, setting_value) 
                     VALUES (:key, :value) 
                     ON DUPLICATE KEY UPDATE setting_value = :value, updated_at = NOW()";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':key', $setting_key);
            $stmt->bindParam(':value', $setting_value);
            $stmt->execute();
        }
    }
    
    $success = "Settings updated successfully!";
}

// Load current settings
$query = "SELECT * FROM settings";
$stmt = $db->prepare($query);
$stmt->execute();
$settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$settings_array = [];
foreach ($settings as $setting) {
    $settings_array[$setting['setting_key']] = $setting['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1>System Settings</h1>
                <p>Configure hotel management system</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" class="settings-form">
                <!-- Hotel Information -->
                <div class="dashboard-section">
                    <h2>Hotel Information</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Hotel Name</label>
                            <input type="text" name="setting_hotel_name" class="form-control" 
                                   value="<?php echo $settings_array['hotel_name'] ?? SITE_NAME; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Check-in Time</label>
                            <input type="time" name="setting_check_in_time" class="form-control" 
                                   value="<?php echo $settings_array['check_in_time'] ?? '14:00'; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Check-out Time</label>
                            <input type="time" name="setting_check_out_time" class="form-control" 
                                   value="<?php echo $settings_array['check_out_time'] ?? '11:00'; ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Booking Settings -->
                <div class="dashboard-section">
                    <h2>Booking Settings</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Cancellation Policy (Days)</label>
                            <input type="number" name="setting_cancellation_days" class="form-control" 
                                   value="<?php echo $settings_array['cancellation_days'] ?? CANCELLATION_DAYS; ?>" 
                                   min="0" max="30" required>
                            <small class="form-text">Number of days before check-in when cancellation is allowed</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Default Currency</label>
                            <select name="setting_currency" class="form-control" required>
                                <option value="EUR" <?php echo ($settings_array['currency'] ?? 'EUR') === 'EUR' ? 'selected' : ''; ?>>Euro (€)</option>
                                <option value="USD" <?php echo ($settings_array['currency'] ?? 'EUR') === 'USD' ? 'selected' : ''; ?>>US Dollar ($)</option>
                                <option value="GBP" <?php echo ($settings_array['currency'] ?? 'EUR') === 'GBP' ? 'selected' : ''; ?>>British Pound (£)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Booking.com Integration -->
                <div class="dashboard-section">
                    <h2>Booking.com Integration</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">API Key</label>
                            <input type="password" name="setting_booking_api_key" class="form-control" 
                                   value="<?php echo $settings_array['booking_api_key'] ?? ''; ?>" 
                                   placeholder="Enter your Booking.com API key">
                            <small class="form-text">Required for automatic reservation sync</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Sync Frequency</label>
                            <select name="setting_sync_frequency" class="form-control">
                                <option value="hourly" <?php echo ($settings_array['sync_frequency'] ?? 'hourly') === 'hourly' ? 'selected' : ''; ?>>Hourly</option>
                                <option value="daily" <?php echo ($settings_array['sync_frequency'] ?? 'hourly') === 'daily' ? 'selected' : ''; ?>>Daily</option>
                                <option value="manual" <?php echo ($settings_array['sync_frequency'] ?? 'hourly') === 'manual' ? 'selected' : ''; ?>>Manual Only</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="testBookingConnection()">Test Connection</button>
                        <button type="button" class="btn btn-primary" onclick="syncBookings()">Sync Now</button>
                    </div>
                </div>

                <!-- Email Settings -->
                <div class="dashboard-section">
                    <h2>Email Settings</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">SMTP Host</label>
                            <input type="text" name="setting_smtp_host" class="form-control" 
                                   value="<?php echo $settings_array['smtp_host'] ?? ''; ?>" 
                                   placeholder="smtp.gmail.com">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">SMTP Port</label>
                            <input type="number" name="setting_smtp_port" class="form-control" 
                                   value="<?php echo $settings_array['smtp_port'] ?? '587'; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">SMTP Username</label>
                            <input type="text" name="setting_smtp_username" class="form-control" 
                                   value="<?php echo $settings_array['smtp_username'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">SMTP Password</label>
                            <input type="password" name="setting_smtp_password" class="form-control" 
                                   value="<?php echo $settings_array['smtp_password'] ?? ''; ?>">
                        </div>
                    </div>
                </div>

                <!-- System Maintenance -->
                <div class="dashboard-section">
                    <h2>System Maintenance</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Backup Frequency</label>
                            <select name="setting_backup_frequency" class="form-control">
                                <option value="daily" <?php echo ($settings_array['backup_frequency'] ?? 'daily') === 'daily' ? 'selected' : ''; ?>>Daily</option>
                                <option value="weekly" <?php echo ($settings_array['backup_frequency'] ?? 'daily') === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                                <option value="monthly" <?php echo ($settings_array['backup_frequency'] ?? 'daily') === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Auto-logout Time (Minutes)</label>
                            <input type="number" name="setting_auto_logout" class="form-control" 
                                   value="<?php echo $settings_array['auto_logout'] ?? '30'; ?>" 
                                   min="5" max="240">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-warning" onclick="createBackup()">Create Backup Now</button>
                        <button type="button" class="btn btn-danger" onclick="clearCache()">Clear System Cache</button>
                    </div>
                </div>

                <!-- Save Settings -->
                <div class="dashboard-section">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">Save All Settings</button>
                        <button type="reset" class="btn btn-secondary">Reset to Defaults</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
    function testBookingConnection() {
        fetch('ajax/booking_sync.php?action=test')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Connection successful!');
                } else {
                    alert('Connection failed: ' + data.error);
                }
            });
    }

    function syncBookings() {
        if (confirm('This will sync all reservations from Booking.com. Continue?')) {
            fetch('ajax/booking_sync.php?action=sync')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Sync completed! ' + data.synced + ' reservations synced.');
                    } else {
                        alert('Sync failed: ' + data.error);
                    }
                });
        }
    }

    function createBackup() {
        if (confirm('This will create a full database backup. Continue?')) {
            fetch('ajax/system_actions.php?action=backup')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Backup created successfully!');
                    } else {
                        alert('Backup failed: ' + data.error);
                    }
                });
        }
    }

    function clearCache() {
        if (confirm('This will clear all system cache. Continue?')) {
            fetch('ajax/system_actions.php?action=clear_cache')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Cache cleared successfully!');
                    } else {
                        alert('Cache clear failed: ' + data.error);
                    }
                });
        }
    }
    </script>
</body>
</html> 