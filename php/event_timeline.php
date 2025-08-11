<?php
session_start();
require 'connection.php';

// Ensure only admins or presidents view this
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin')) {
    header("Location: unauthorized.php");
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            e.event_id,
            e.name AS event_name,
            e.created_at,
            e.status,
            e.activity_status,
            (SELECT COUNT(*) FROM tasks t WHERE t.event_id = e.event_id) AS task_count,
            (SELECT COUNT(*) FROM budget_items b WHERE b.eventid = e.event_id) AS budget_count,
            (SELECT COUNT(*) FROM bookings bk WHERE bk.event_id = e.event_id) AS booking_count
        FROM events e
        WHERE status = 'Approved' AND activity_status = 'active'
        ORDER BY e.created_at DESC
    ");
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching event timelines: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Event Timeline Report</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <style>
    body {
        padding: 20px;
        background-color: #f4f6f9;
    }

    table {
        background-color: #fff;
    }

    .badge-success {
        background-color: #28a745;
    }

    .badge-warning {
        background-color: #ffc107;
    }

    .badge-secondary {
        background-color: #6c757d;
    }
    </style>
</head>

<body>

    <h3>📊 Event Timeline Report</h3>
    <p>This report shows each event's key milestone dates and related activity.</p>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Event Name</th>
                <th>Created At</th>
                <th>Tasks</th>
                <th>Budget</th>
                <th>Resources Booked</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($events as $event): ?>
            <tr>
                <td><?= htmlspecialchars($event['event_name']) ?></td>
                <td><?= date('Y-m-d', strtotime($event['created_at'])) ?></td>
                <td>
                    <?= $event['task_count'] ?>
                    <?= $event['task_count'] > 0 ? '<span class="badge badge-success">Assigned</span>' : '<span class="badge badge-secondary">None</span>' ?>
                </td>
                <td>
                    <?= $event['budget_count'] ?>
                    <?= $event['budget_count'] > 0 ? '<span class="badge badge-success">Submitted</span>' : '<span class="badge badge-secondary">None</span>' ?>
                </td>
                <td>
                    <?= $event['booking_count'] ?>
                    <?= $event['booking_count'] > 0 ? '<span class="badge badge-success">Booked</span>' : '<span class="badge badge-secondary">None</span>' ?>
                </td>
                <td>
                    <span
                        class="badge badge-<?= $event['activity_status'] === 'completed' ? 'success' : ($event['activity_status'] === 'active' ? 'warning' : 'secondary') ?>">
                        <?= ucfirst($event['activity_status']) ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>

</html>