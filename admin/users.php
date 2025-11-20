<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('admin');

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_user'])) {
        $user_id = $_POST['user_id'];
        $role = $_POST['role'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $query = "UPDATE users SET role = :role, is_active = :is_active WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':is_active', $is_active);
        $stmt->bindParam(':id', $user_id);
        
        if ($stmt->execute()) {
            $success = "User updated successfully!";
        } else {
            $error = "Failed to update user.";
        }
    }
}

// Get search parameters
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1>User Management</h1>
                <p>Manage system users and their roles</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="dashboard-section">
                <h2>Filters</h2>
                <form method="GET" class="filter-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search by name, email, or username..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-control">
                                <option value="">All Roles</option>
                                <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="receptionist" <?php echo $role_filter === 'receptionist' ? 'selected' : ''; ?>>Receptionist</option>
                                <option value="housekeeper" <?php echo $role_filter === 'housekeeper' ? 'selected' : ''; ?>>Housekeeper</option>
                                <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>User</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="users.php" class="btn btn-secondary">Clear Filters</a>
                    </div>
                </form>
            </div>

            <!-- Users List -->
            <div class="dashboard-section">
                <h2>All Users</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User Info</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Build query with filters
                            $query = "SELECT * FROM users WHERE 1=1";
                            $params = [];
                            
                            if (!empty($search)) {
                                $query .= " AND (first_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR username LIKE :search)";
                                $params['search'] = "%$search%";
                            }
                            
                            if (!empty($role_filter)) {
                                $query .= " AND role = :role";
                                $params['role'] = $role_filter;
                            }
                            
                            $query .= " ORDER BY created_at DESC";
                            
                            $stmt = $db->prepare($query);
                            $stmt->execute($params);
                            
                            while ($user = $stmt->fetch(PDO::FETCH_ASSOC)):
                            ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td>
                                    <strong><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></strong><br>
                                    <small><?php echo $user['email']; ?></small><br>
                                    <small><?php echo $user['phone']; ?></small>
                                </td>
                                <td><?php echo $user['username']; ?></td>
                                <td>
                                    <form method="POST" class="role-form">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <select name="role" class="role-select">
                                            <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                            <option value="receptionist" <?php echo $user['role'] === 'receptionist' ? 'selected' : ''; ?>>Receptionist</option>
                                            <option value="housekeeper" <?php echo $user['role'] === 'housekeeper' ? 'selected' : ''; ?>>Housekeeper</option>
                                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" class="status-form">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="is_active" value="1" 
                                                   <?php echo $user['is_active'] ? 'checked' : ''; ?> 
                                                   onchange="this.form.submit()">
                                            <span class="checkmark"></span>
                                            Active
                                        </label>
                                        <input type="hidden" name="update_user" value="1">
                                    </form>
                                </td>
                                <td><?php echo formatDate($user['created_at'], 'M j, Y'); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-secondary" onclick="viewUser(<?php echo $user['id']; ?>)">View</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">Delete</button>
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
    function viewUser(userId) {
        window.open('user_details.php?id=' + userId, '_blank');
    }

    function deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            fetch('ajax/user_actions.php?action=delete&id=' + userId)
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

    // Auto-submit role forms on change
    document.querySelectorAll('.role-select').forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
    </script>
</body>
</html>