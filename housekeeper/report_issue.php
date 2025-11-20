<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('housekeeper');

// Handle issue reporting
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = $_POST['room_id'];
    $issue_type = $_POST['issue_type'];
    $description = $_POST['description'];
    $priority = $_POST['priority'];
    
    $query = "INSERT INTO room_issues (room_id, reported_by, issue_type, description, priority) 
              VALUES (:room_id, :reported_by, :issue_type, :description, :priority)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':room_id', $room_id);
    $stmt->bindParam(':reported_by', $_SESSION['user_id']);
    $stmt->bindParam(':issue_type', $issue_type);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':priority', $priority);
    
    if ($stmt->execute()) {
        $success = "Issue reported successfully! Maintenance has been notified.";
        
        // Update room status to maintenance
        $statusQuery = "INSERT INTO room_status (room_id, status, notes) 
                       VALUES (:room_id, 'maintenance', 'Reported issue: $issue_type')
                       ON DUPLICATE KEY UPDATE status = 'maintenance', notes = 'Reported issue: $issue_type'";
        $statusStmt = $db->prepare($statusQuery);
        $statusStmt->bindParam(':room_id', $room_id);
        $statusStmt->execute();
        
    } else {
        $error = "Failed to report issue.";
    }
}

// Get rooms for dropdown
$roomsQuery = "SELECT * FROM rooms ORDER BY room_number";
$roomsStmt = $db->prepare($roomsQuery);
$roomsStmt->execute();
$rooms = $roomsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get reported issues by this housekeeper
$issuesQuery = "SELECT ri.*, r.room_number, rm.room_type 
               FROM room_issues ri 
               JOIN rooms r ON ri.room_id = r.id 
               JOIN rooms rm ON ri.room_id = rm.id 
               WHERE ri.reported_by = :user_id 
               ORDER BY ri.reported_at DESC";
$issuesStmt = $db->prepare($issuesQuery);
$issuesStmt->bindParam(':user_id', $_SESSION['user_id']);
$issuesStmt->execute();
$reported_issues = $issuesStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Issue - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><?php echo SITE_NAME; ?></h2>
                <p>Housekeeper Panel</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="rooms.php">Room Status</a></li>
                <li><a href="report_issue.php" class="active">Report Issue</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1>Report Room Issue</h1>
                <p>Report maintenance issues and problems</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Report Issue Form -->
            <div class="dashboard-section">
                <h2>Report New Issue</h2>
                <form method="POST" class="issue-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Room</label>
                            <select name="room_id" class="form-control" required>
                                <option value="">Select a room</option>
                                <?php foreach ($rooms as $room): ?>
                                <option value="<?php echo $room['id']; ?>">
                                    Room <?php echo $room['room_number']; ?> - <?php echo getRoomTypeName($room['room_type']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Issue Type</label>
                            <select name="issue_type" class="form-control" required>
                                <option value="">Select issue type</option>
                                <option value="Plumbing">Plumbing (leaks, clogged drains)</option>
                                <option value="Electrical">Electrical (lights, outlets)</option>
                                <option value="Furniture">Furniture (broken, damaged)</option>
                                <option value="Appliances">Appliances (TV, AC, fridge)</option>
                                <option value="Cleanliness">Cleanliness (stains, odors)</option>
                                <option value="Safety">Safety (locks, smoke detectors)</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-control" required>
                                <option value="low">Low (Cosmetic, not urgent)</option>
                                <option value="medium" selected>Medium (Functional, should be fixed)</option>
                                <option value="high">High (Affects guest comfort)</option>
                                <option value="urgent">Urgent (Safety hazard, immediate attention)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" required 
                                  placeholder="Please provide a detailed description of the issue..."></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Report Issue</button>
                        <button type="reset" class="btn btn-secondary">Clear Form</button>
                    </div>
                </form>
            </div>

            <!-- My Reported Issues -->
            <div class="dashboard-section">
                <h2>My Reported Issues</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Room</th>
                                <th>Issue Type</th>
                                <th>Description</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Reported</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reported_issues as $issue): ?>
                            <tr>
                                <td>
                                    <strong>Room <?php echo $issue['room_number']; ?></strong><br>
                                    <small><?php echo getRoomTypeName($issue['room_type']); ?></small>
                                </td>
                                <td><?php echo $issue['issue_type']; ?></td>
                                <td><?php echo $issue['description']; ?></td>
                                <td>
                                    <span class="priority-badge priority-<?php echo $issue['priority']; ?>">
                                        <?php echo ucfirst($issue['priority']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $issue['status']; ?>">
                                        <?php echo ucfirst($issue['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($issue['reported_at'], 'M j, Y g:i A'); ?></td>
                                <td>
                                    <?php if ($issue['status'] === 'reported'): ?>
                                    <button class="btn btn-sm btn-warning" 
                                            onclick="updateIssue(<?php echo $issue['id']; ?>)">
                                        Update
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Report Templates -->
            <div class="dashboard-section">
                <h2>Quick Report Templates</h2>
                <div class="quick-templates">
                    <div class="template-card">
                        <h4>Broken TV</h4>
                        <p>TV not working, no power or no signal</p>
                        <button class="btn btn-sm btn-secondary" onclick="useTemplate('TV not working', 'Appliances', 'medium')">Use Template</button>
                    </div>
                    
                    <div class="template-card">
                        <h4>Leaky Faucet</h4>
                        <p>Bathroom faucet leaking continuously</p>
                        <button class="btn btn-sm btn-secondary" onclick="useTemplate('Faucet leaking in bathroom', 'Plumbing', 'medium')">Use Template</button>
                    </div>
                    
                    <div class="template-card">
                        <h4>AC Not Working</h4>
                        <p>Air conditioning not cooling properly</p>
                        <button class="btn btn-sm btn-secondary" onclick="useTemplate('AC not cooling', 'Appliances', 'high')">Use Template</button>
                    </div>
                    
                    <div class="template-card">
                        <h4>Broken Lock</h4>
                        <p>Door lock not functioning properly</p>
                        <button class="btn btn-sm btn-secondary" onclick="useTemplate('Door lock broken', 'Safety', 'urgent')">Use Template</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function useTemplate(description, type, priority) {
        document.querySelector('textarea[name="description"]').value = description;
        document.querySelector('select[name="issue_type"]').value = type;
        document.querySelector('select[name="priority"]').value = priority;
        
        // Scroll to form
        document.querySelector('.issue-form').scrollIntoView({ behavior: 'smooth' });
    }

    function updateIssue(issueId) {
        const newDescription = prompt('Update issue description:');
        if (newDescription) {
            fetch('ajax/housekeeper_actions.php?action=update_issue', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    issue_id: issueId,
                    description: newDescription
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Issue updated successfully!');
                    location.reload();
                } else {
                    alert('Error updating issue: ' + data.error);
                }
            });
        }
    }
    </script>

    <style>
    .priority-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .priority-low {
        background: #d1ecf1;
        color: #0c5460;
    }

    .priority-medium {
        background: #fff3cd;
        color: #856404;
    }

    .priority-high {
        background: #f8d7da;
        color: #721c24;
    }

    .priority-urgent {
        background: #721c24;
        color: #fff;
    }

    .quick-templates {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .template-card {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        border-left: 4px solid #3498db;
    }

    .template-card h4 {
        margin-bottom: 0.5rem;
        color: #2c3e50;
    }

    .template-card p {
        color: #7f8c8d;
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }
    </style>
</body>
</html>