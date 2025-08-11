<?php
require 'connection.php';
session_start();

// Fetch task completion summary per event
try {
    $stmt = $pdo->query("SELECT 
        e.name AS event_name,
        COUNT(t.id) AS total_tasks,
        SUM(t.status = 'Completed') AS completed,
        SUM(t.status = 'In Progress') AS in_progress,
        SUM(t.status = 'Pending') AS pending,
        SUM(t.due_date < NOW() AND t.status != 'Completed') AS overdue
    FROM events e
    JOIN tasks t ON t.event_id = e.event_id
    GROUP BY e.event_id");

    $report = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching task report: " . $e->getMessage());
}

function getEfficiencyStatus($percentage) {
    if ($percentage >= 90) return ['Excellent', 'success'];
    if ($percentage >= 50) return ['Moderate', 'warning'];
    return ['Poor', 'danger'];
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Task Completion Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container" style="padding: 50px; font-family: 'poppins';">
        <h2>Task Completion Report</h2>
        <div class="alert alert-info">
            <strong>Note:</strong> Completion % is based on completed tasks vs total tasks. Efficiency status is
            color-coded.
        </div>
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Event Name</th>
                    <th>Total Tasks</th>
                    <th>Completed</th>
                    <th>In Progress</th>
                    <th>Pending</th>
                    <th>Overdue</th>
                    <th>Completion %</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($report as $row): 
            $completion = $row['total_tasks'] > 0 ? round(($row['completed'] / $row['total_tasks']) * 100, 1) : 0;
            [$efficiency, $badge] = getEfficiencyStatus($completion);
        ?>
                <tr>
                    <td><?= htmlspecialchars($row['event_name']) ?></td>
                    <td><?= $row['total_tasks'] ?></td>
                    <td><?= $row['completed'] ?></td>
                    <td><?= $row['in_progress'] ?></td>
                    <td><?= $row['pending'] ?></td>
                    <td><?= $row['overdue'] ?></td>
                    <td><?= $completion ?>%</td>
                    <td><span class="badge bg-<?= $badge ?>"><?= $efficiency ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="reports.php" class="btn btn-secondary mt-3"> Back to Reports</a>
    </div>
</body>

</html>