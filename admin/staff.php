<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
requireRole('admin');

// Handle staff actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_staff'])) {
        $user_id = $_POST['user_id'];
        $position = $_POST['position'];
        $salary = $_POST['salary'];
        $hire_date = $_POST['hire_date'];
        $shift = $_POST['shift'];
        
        // Check if staff already exists
        $checkQuery = "SELECT id FROM staff WHERE user_id = :user_id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':user_id', $user_id);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            $error = "This user is already a staff member.";
        } else {
            $query = "INSERT INTO staff (user_id, position, salary, hire_date, shift) 
                     VALUES (:user_id, :position, :salary, :hire_date, :shift)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':position', $position);
            $stmt->bindParam(':salary', $salary);
            $stmt->bindParam(':hire_date', $hire_date);
            $stmt->bindParam(':shift', $shift);
            
            if ($stmt->execute()) {
                $success = "Staff member added successfully!";
            } else {
                $error = "Failed to add staff member.";
            }
        }
    }
    
    if (isset($_POST['update_staff'])) {
        $staff_id = $_POST['staff_id'];
        $position = $_POST['position'];
        $salary = $_POST['salary'];
        $shift = $_POST['shift'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $query = "UPDATE staff SET position = :position, salary = :salary, shift = :shift, is_active = :is_active 
                 WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':salary', $salary);
        $stmt->bindParam(':shift', $shift);
        $stmt->bindParam(':is_active', $is_active);
        $stmt->bindParam(':id', $staff_id);
        
        if ($stmt->execute()) {
            $success = "Staff member updated successfully!";
        } else {
            $error = "Failed to update staff member.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1>Staff Management</h1>
                <p>Manage hotel staff members</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Add Staff Form -->
            <div class="dashboard-section">
                <h2>Add New Staff Member</h2>
                <form method="POST" class="staff-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Select User</label>
                            <select name="user_id" class="form-control" required>
                                <option value="">Select a user</option>
                                <?php
                                $query = "SELECT u.id, u.first_name, u.last_name, u.email 
                                         FROM users u 
                                         LEFT JOIN staff s ON u.id = s.user_id 
                                         WHERE s.id IS NULL 
                                         AND u.role IN ('receptionist', 'housekeeper')";
                                $stmt = $db->prepare($query);
                                $stmt->execute();
                                
                                while ($user = $stmt->fetch(PDO::FETCH_ASSOC)):
                                ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo $user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['email'] . ')'; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Position</label>
                            <input type="text" name="position" class="form-control" required 
                                   placeholder="e.g., Senior Receptionist, Head Housekeeper">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Salary (€)</label>
                            <input type="number" name="salary" step="0.01" class="form-control" required 
                                   placeholder="0.00">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Hire Date</label>
                            <input type="date" name="hire_date" class="form-control" required 
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Shift</label>
                            <select name="shift" class="form-control" required>
                                <option value="morning">Morning</option>
                                <option value="evening">Evening</option>
                                <option value="night">Night</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" name="add_staff" class="btn btn-primary">Add Staff Member</button>
                </form>
            </div>

            <!-- Staff List -->
            <div class="dashboard-section">
                <h2>Current Staff</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Staff ID</th>
                                <th>Employee</th>
                                <th>Position</th>
                                <th>Salary</th>
                                <th>Hire Date</th>
                                <th>Shift</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT s.*, u.first_name, u.last_name, u.email, u.role 
                                     FROM staff s 
                                     JOIN users u ON s.user_id = u.id 
                                     ORDER BY s.hire_date DESC";
                            $stmt = $db->prepare($query);
                            $stmt->execute();
                            
                            while ($staff = $stmt->fetch(PDO::FETCH_ASSOC)):
                            ?>
                            <tr>
                                <td>#<?php echo $staff['id']; ?></td>
                                <td>
                                    <strong><?php echo $staff['first_name'] . ' ' . $staff['last_name']; ?></strong><br>
                                    <small><?php echo $staff['email']; ?></small><br>
                                    <small class="role-badge"><?php echo ucfirst($staff['role']); ?></small>
                                </td>
                                <td>
                                    <form method="POST" class="inline-form">
                                        <input type="hidden" name="staff_id" value="<?php echo $staff['id']; ?>">
                                        <input type="text" name="position" value="<?php echo htmlspecialchars($staff['position']); ?>" 
                                               class="form-control-sm" required>
                                </td>
                                <td>
                                        <input type="number" name="salary" step="0.01" value="<?php echo $staff['salary']; ?>" 
                                               class="form-control-sm" required> €
                                </td>
                                <td><?php echo formatDate($staff['hire_date']); ?></td>
                                <td>
                                        <select name="shift" class="form-control-sm" required>
                                            <option value="morning" <?php echo $staff['shift'] === 'morning' ? 'selected' : ''; ?>>Morning</option>
                                            <option value="evening" <?php echo $staff['shift'] === 'evening' ? 'selected' : ''; ?>>Evening</option>
                                            <option value="night" <?php echo $staff['shift'] === 'night' ? 'selected' : ''; ?>>Night</option>
                                        </select>
                                </td>
                                <td>
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="is_active" value="1" 
                                                   <?php echo $staff['is_active'] ? 'checked' : ''; ?>>
                                            <span class="checkmark"></span>
                                            Active
                                        </label>
                                </td>
                                <td>
                                        <button type="submit" name="update_staff" class="btn btn-sm btn-primary">Update</button>
                                    </form>
                                    <button class="btn btn-sm btn-danger" onclick="removeStaff(<?php echo $staff['id']; ?>)">Remove</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    function removeStaff(staffId) {
        if (confirm('Are you sure you want to remove this staff member?')) {
            fetch('ajax/staff_actions.php?action=remove&id=' + staffId)
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
    </script>
</body>
</html>