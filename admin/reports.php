<?php
require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireRole('admin');

// Get report parameters
$report_type = $_GET['report_type'] ?? 'revenue';
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-t');
$group_by = $_GET['group_by'] ?? 'month';

// Generate reports
$reports = [];
$chart_data = [];

switch ($report_type) {
    case 'revenue':
        $reports = generateRevenueReport($date_from, $date_to, $group_by);
        $chart_data = prepareRevenueChartData($reports);
        break;
        
    case 'occupancy':
        $reports = generateOccupancyReport($date_from, $date_to, $group_by);
        $chart_data = prepareOccupancyChartData($reports);
        break;
        
    case 'reservations':
        $reports = generateReservationsReport($date_from, $date_to, $group_by);
        $chart_data = prepareReservationsChartData($reports);
        break;
}

function generateRevenueReport($date_from, $date_to, $group_by) {
    global $db;
    
    $group_format = $group_by === 'day' ? '%Y-%m-%d' : '%Y-%m';
    
    $query = "SELECT 
                DATE_FORMAT(check_in, '$group_format') as period,
                COUNT(*) as reservation_count,
                SUM(total_price) as total_revenue,
                AVG(total_price) as average_revenue
              FROM reservations 
              WHERE status IN ('confirmed', 'checked_in', 'checked_out')
              AND check_in BETWEEN :date_from AND :date_to
              GROUP BY period 
              ORDER BY period";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':date_from', $date_from);
    $stmt->bindParam(':date_to', $date_to);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function generateOccupancyReport($date_from, $date_to, $group_by) {
    global $db;
    
    $group_format = $group_by === 'day' ? '%Y-%m-%d' : '%Y-%m';
    $total_rooms = 10; // This should be dynamic based on actual room count
    
    $query = "SELECT 
                DATE_FORMAT(check_in, '$group_format') as period,
                COUNT(*) as occupied_rooms,
                (COUNT(*) / ($total_rooms * DATEDIFF(:date_to, :date_from))) * 100 as occupancy_rate
              FROM reservations 
              WHERE status IN ('confirmed', 'checked_in', 'checked_out')
              AND check_in BETWEEN :date_from AND :date_to
              GROUP BY period 
              ORDER BY period";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':date_from', $date_from);
    $stmt->bindParam(':date_to', $date_to);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function generateReservationsReport($date_from, $date_to, $group_by) {
    global $db;
    
    $group_format = $group_by === 'day' ? '%Y-%m-%d' : '%Y-%m';
    
    $query = "SELECT 
                DATE_FORMAT(created_at, '$group_format') as period,
                COUNT(*) as total_reservations,
                SUM(CASE WHEN source = 'website' THEN 1 ELSE 0 END) as website_reservations,
                SUM(CASE WHEN source = 'booking_com' THEN 1 ELSE 0 END) as booking_com_reservations,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_reservations
              FROM reservations 
              WHERE created_at BETWEEN :date_from AND :date_to
              GROUP BY period 
              ORDER BY period";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':date_from', $date_from);
    $stmt->bindParam(':date_to', $date_to);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function prepareRevenueChartData($data) {
    $labels = [];
    $revenue = [];
    
    foreach ($data as $row) {
        $labels[] = $row['period'];
        $revenue[] = (float)$row['total_revenue'];
    }
    
    return [
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Revenue (€)',
                'data' => $revenue,
                'borderColor' => '#3498db',
                'backgroundColor' => 'rgba(52, 152, 219, 0.1)'
            ]
        ]
    ];
}

function prepareOccupancyChartData($data) {
    $labels = [];
    $occupancy = [];
    
    foreach ($data as $row) {
        $labels[] = $row['period'];
        $occupancy[] = (float)$row['occupancy_rate'];
    }
    
    return [
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Occupancy Rate (%)',
                'data' => $occupancy,
                'borderColor' => '#27ae60',
                'backgroundColor' => 'rgba(39, 174, 96, 0.1)'
            ]
        ]
    ];
}

function prepareReservationsChartData($data) {
    $labels = [];
    $website = [];
    $booking_com = [];
    
    foreach ($data as $row) {
        $labels[] = $row['period'];
        $website[] = (int)$row['website_reservations'];
        $booking_com[] = (int)$row['booking_com_reservations'];
    }
    
    return [
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Website Reservations',
                'data' => $website,
                'borderColor' => '#3498db',
                'backgroundColor' => 'rgba(52, 152, 219, 0.1)'
            ],
            [
                'label' => 'Booking.com Reservations',
                'data' => $booking_com,
                'borderColor' => '#e74c3c',
                'backgroundColor' => 'rgba(231, 76, 60, 0.1)'
            ]
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1>Reports & Analytics</h1>
                <p>Hotel performance and business insights</p>
            </div>

            <!-- Report Filters -->
            <div class="dashboard-section">
                <h2>Report Configuration</h2>
                <form method="GET" class="report-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Report Type</label>
                            <select name="report_type" class="form-control" onchange="this.form.submit()">
                                <option value="revenue" <?php echo $report_type === 'revenue' ? 'selected' : ''; ?>>Revenue Report</option>
                                <option value="occupancy" <?php echo $report_type === 'occupancy' ? 'selected' : ''; ?>>Occupancy Report</option>
                                <option value="reservations" <?php echo $report_type === 'reservations' ? 'selected' : ''; ?>>Reservations Report</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Group By</label>
                            <select name="group_by" class="form-control">
                                <option value="month" <?php echo $group_by === 'month' ? 'selected' : ''; ?>>Month</option>
                                <option value="day" <?php echo $group_by === 'day' ? 'selected' : ''; ?>>Day</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                        <button type="button" class="btn btn-secondary" onclick="exportReport()">Export to PDF</button>
                    </div>
                </form>
            </div>

            <!-- Report Summary -->
            <div class="dashboard-section">
                <h2>Report Summary</h2>
                <div class="stats-grid">
                    <?php if ($report_type === 'revenue'): ?>
                        <div class="stat-card">
                            <div class="stat-icon">💰</div>
                            <div class="stat-info">
                                <h3>€<?php echo array_sum(array_column($reports, 'total_revenue')); ?></h3>
                                <p>Total Revenue</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">📊</div>
                            <div class="stat-info">
                                <h3><?php echo count($reports); ?></h3>
                                <p>Reporting Periods</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">📈</div>
                            <div class="stat-info">
                                <h3>€<?php echo round(array_sum(array_column($reports, 'average_revenue')) / count($reports), 2); ?></h3>
                                <p>Average Revenue</p>
                            </div>
                        </div>
                    <?php elseif ($report_type === 'occupancy'): ?>
                        <div class="stat-card">
                            <div class="stat-icon">🏨</div>
                            <div class="stat-info">
                                <h3><?php echo round(array_sum(array_column($reports, 'occupancy_rate')) / count($reports), 1); ?>%</h3>
                                <p>Average Occupancy</p>
                            </div>
                        </div>
                    <?php elseif ($report_type === 'reservations'): ?>
                        <div class="stat-card">
                            <div class="stat-icon">📅</div>
                            <div class="stat-info">
                                <h3><?php echo array_sum(array_column($reports, 'total_reservations')); ?></h3>
                                <p>Total Reservations</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">🌐</div>
                            <div class="stat-info">
                                <h3><?php echo array_sum(array_column($reports, 'website_reservations')); ?></h3>
                                <p>Website Reservations</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">📱</div>
                            <div class="stat-info">
                                <h3><?php echo array_sum(array_column($reports, 'booking_com_reservations')); ?></h3>
                                <p>Booking.com Reservations</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Chart -->
            <div class="dashboard-section">
                <h2>Visual Report</h2>
                <div class="chart-container">
                    <canvas id="reportChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Data Table -->
            <div class="dashboard-section">
                <h2>Detailed Data</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <?php if (!empty($reports)): ?>
                                    <?php foreach (array_keys($reports[0]) as $column): ?>
                                        <th><?php echo ucfirst(str_replace('_', ' ', $column)); ?></th>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $report): ?>
                                <tr>
                                    <?php foreach ($report as $value): ?>
                                        <td><?php echo $value; ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Initialize chart
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('reportChart').getContext('2d');
        const chartData = <?php echo json_encode($chart_data); ?>;
        
        new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: '<?php echo ucfirst($report_type); ?> Report'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });

    function exportReport() {
        // Simple PDF export - in production, use a proper PDF library
        window.print();
    }
    </script>
</body>
</html>