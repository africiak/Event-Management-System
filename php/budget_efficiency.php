<?php

session_start();
require 'connection.php';

if(!isset($_SESSION['user_id'])|| $_SESSION['role'] !== 'admin'){
    header("Location: unauthorized.php");
exit();
}


try{
    $stmt = $pdo-> prepare("
      SELECT 
            e.name AS event_name,
            e.budget AS allocated_budget,
            IFNULL(SUM(b.amount), 0) AS used_budget,
            (e.budget - IFNULL(SUM(b.amount), 0)) AS remaining_budget,
            ROUND((IFNULL(SUM(b.amount), 0) / e.budget) * 100, 2) AS efficiency
        FROM events e
        LEFT JOIN budget_items b ON e.event_id = b.eventid
        WHERE e.status = 'Approved' AND e.activity_status = 'active'
        GROUP BY e.event_id
        ORDER BY efficiency DESC
    ");
    $stmt->execute();
    $report = $stmt->fetchAll(PDO::FETCH_ASSOC);
}catch(PDOException $e){
 die("Database Error: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>report</title>
</head>

<body>
    <div class="container" style="font-family: 'poppins'; padding:50px;">
        <h2>Budget Efficiency Report</h2>
        <div class="alert alert-info" style="font-size: 14px;">
            <strong>Budget Efficiency Guide:</strong><br>
            An event is considered:
            <ul style="margin: 0; padding-left: 20px;">
                <li><strong>Efficient</strong> if it uses 90% or more of its allocated budget wisely.</li>
                <li><strong>Moderate</strong> if it uses between 60% and 89% of the allocated budget.</li>
                <li><strong>Inefficient</strong> if less than 60% of the budget is used — this may indicate
                    underutilization or poor planning.</li>
            </ul>
        </div>
        <table class="table table-bordered table-hover bg-white shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th>Event Name</th>
                    <th>Allocated Budget (KES)</th>
                    <th>Used Budget (KES)</th>
                    <th>Remaining (KES)</th>
                    <th>Efficiency (%)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($report as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['event_name']) ?></td>
                    <td><?= number_format($row['allocated_budget'], 2) ?></td>
                    <td><?= number_format($row['used_budget'], 2) ?></td>
                    <td><?= number_format($row['remaining_budget'], 2) ?></td>
                    <td>
                        <?= $row['efficiency'] ?>%
                        <?php if ($row['efficiency'] >= 80): ?>
                        <span class="badge bg-success">Efficient</span>
                        <?php elseif ($row['efficiency'] >= 50): ?>
                        <span class="badge bg-warning text-dark">Moderate</span>
                        <?php else: ?>
                        <span class="badge bg-danger">Inefficient</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="reports.php" class="btn btn-secondary mt-3"> Back to Reports</a>
    </div>
</body>

</html>