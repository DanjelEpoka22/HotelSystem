<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('admin');

// Handle staff role updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_staff'])) {
        $user_id = $_POST['user_id'];
        $position = $_POST['position'];
        $salary = $_POST['salary'];
        $shift = $_POST['shift'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Check if staff record exists
        $checkQuery = "SELECT id FROM staff WHERE user_id = :user_id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':user_id', $user_id);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            // Update existing staff record
            $query = "UPDATE staff SET position = :position, salary = :salary, shift = :shift, is_active = :is_active 
                     WHERE user_id = :user_id";
        } else {
            // Insert new staff record
            $query = "INSERT INTO staff (user_id, position, salary, shift, is_active, hire_date) 
                     VALUES (:user_id, :position, :salary, :shift, :is_active, NOW())";
        }
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':salary', $salary);
        $stmt->bindParam(':shift', $shift);
        $stmt->bindParam(':is_active', $is_active);
        
        if ($stmt->execute()) {
            $success = "Staff member updated successfully!";
        } else {
            $error = "Failed to update staff member.";
        }
    }
    
    if (isset($_POST['add_staff_role'])) {
        $user_id = $_POST['user_id'];
        $role = $_POST['role'];
        
        // Update user role
        $query = "UPDATE users SET role = :role WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':id', $user_id);
        
        if ($stmt->execute()) {
            $success = "User role updated to staff successfully!";
        } else {
            $error = "Failed to update user role.";
        }
    }
}

// Get staff members (users with receptionist or housekeeper role)
$query = "SELECT u.*, s.position, s.salary, s.shift, s.hire_date, s.is_active as staff_active 
         FROM users u 
         LEFT JOIN staff s ON u.id = s.user_id 
         WHERE u.role IN ('receptionist', 'housekeeper') 
         ORDER BY u.role, u.first_name";
$stmt = $db->prepare($query);
$stmt->execute();
$staff_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get regular users who can be promoted to staff
$regular_users_query = "SELECT * FROM users WHERE role = 'user' AND is_active = 1 ORDER BY first_name";
$regular_users_stmt = $db->prepare($regular_users_query);
$regular_users_stmt->execute();
$regular_users = $regular_users_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .role-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .role-receptionist {
            background: #3498db;
            color: white;
        }
        .role-housekeeper {
            background: #9b59b6;
            color: white;
        }
        .role-admin {
            background: #e74c3c;
            color: white;
        }
        .staff-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2C5F7C;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        .form-control-sm {
            padding: 0.4rem 0.6rem;
            font-size: 0.85rem;
            height: auto;
        }
        .inline-form {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1>Staff Management</h1>
                <p>Manage hotel staff members and their details</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Staff Statistics -->
            <div class="staff-stats">
                <?php
                // Count staff by role
                $receptionist_count = 0;
                $housekeeper_count = 0;
                $active_staff = 0;
                
                foreach ($staff_members as $staff) {
                    if ($staff['role'] === 'receptionist') $receptionist_count++;
                    if ($staff['role'] === 'housekeeper') $housekeeper_count++;
                    if ($staff['staff_active'] || $staff['staff_active'] === null) $active_staff++;
                }
                ?>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($staff_members); ?></div>
                    <div class="stat-label">Total Staff</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $receptionist_count; ?></div>
                    <div class="stat-label">Receptionists</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $housekeeper_count; ?></div>
                    <div class="stat-label">Housekeepers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $active_staff; ?></div>
                    <div class="stat-label">Active Staff</div>
                </div>
            </div>

            <!-- Promote User to Staff -->
            <?php if (count($regular_users) > 0): ?>
            <div class="dashboard-section">
                <h2>Promote User to Staff</h2>
                <form method="POST" class="staff-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Select User</label>
                            <select name="user_id" class="form-control" required>
                                <option value="">Select a user to promote</option>
                                <?php foreach ($regular_users as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo $user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['email'] . ')'; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Staff Role</label>
                            <select name="role" class="form-control" required>
                                <option value="receptionist">Receptionist</option>
                                <option value="housekeeper">Housekeeper</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" name="add_staff_role" class="btn btn-primary">Promote to Staff</button>
                </form>
            </div>
            <?php endif; ?>

            <!-- Staff List -->
            <div class="dashboard-section">
                <h2>Current Staff Members</h2>
                <div class="table-container">
                    <?php if (count($staff_members) > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Role</th>
                                <th>Position</th>
                                <th>Salary</th>
                                <th>Shift</th>
                                <th>Hire Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($staff_members as $staff): ?>
                            <tr>
                                <td>
                                    <strong><?php echo $staff['first_name'] . ' ' . $staff['last_name']; ?></strong><br>
                                    <small><?php echo $staff['email']; ?></small><br>
                                    <small><?php echo $staff['phone']; ?></small>
                                </td>
                                <td>
                                    <span class="role-badge role-<?php echo $staff['role']; ?>">
                                        <?php echo ucfirst($staff['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" class="inline-form">
                                        <input type="hidden" name="user_id" value="<?php echo $staff['id']; ?>">
                                        <input type="text" name="position" 
                                               value="<?php echo htmlspecialchars($staff['position'] ?? 'Not set'); ?>" 
                                               class="form-control-sm" placeholder="Position" required>
                                </td>
                                <td>
                                        <input type="number" name="salary" step="0.01" 
                                               value="<?php echo $staff['salary'] ?? '0'; ?>" 
                                               class="form-control-sm" placeholder="0.00" required> €
                                </td>
                                <td>
                                        <select name="shift" class="form-control-sm" required>
                                            <option value="morning" <?php echo ($staff['shift'] ?? 'morning') === 'morning' ? 'selected' : ''; ?>>Morning</option>
                                            <option value="evening" <?php echo ($staff['shift'] ?? '') === 'evening' ? 'selected' : ''; ?>>Evening</option>
                                            <option value="night" <?php echo ($staff['shift'] ?? '') === 'night' ? 'selected' : ''; ?>>Night</option>
                                        </select>
                                </td>
                                <td><?php echo $staff['hire_date'] ? formatDate($staff['hire_date']) : formatDate($staff['created_at']); ?></td>
                                <td>
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="is_active" value="1" 
                                                   <?php echo ($staff['staff_active'] === null || $staff['staff_active']) ? 'checked' : ''; ?>>
                                            <span class="checkmark"></span>
                                            Active
                                        </label>
                                </td>
                                <td>
                                        <button type="submit" name="update_staff" class="btn btn-sm btn-primary">Update</button>
                                    </form>
                                    <button class="btn btn-sm btn-danger" onclick="demoteStaff(<?php echo $staff['id']; ?>)">Demote</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="no-data">
                        <p>No staff members found. Use the form above to promote users to staff roles.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    function demoteStaff(userId) {
        if (confirm('Are you sure you want to demote this staff member to regular user?')) {
            fetch('ajax/staff_actions.php?action=demote&id=' + userId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        }
    }

    // Auto-size text inputs
    document.querySelectorAll('input[type="text"]').forEach(input => {
        input.addEventListener('input', function() {
            this.style.width = (this.value.length + 2) + 'ch';
        });
        // Set initial width
        input.style.width = (input.value.length + 2) + 'ch';
    });
    </script>
</body>
</html>