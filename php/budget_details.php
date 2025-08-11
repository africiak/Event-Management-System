<?php
require 'connection.php';
session_start();


if (!isset($_GET['event_id'])) {
    header("Location: adminbudget.php");
    exit();
}

$event_id = (int)$_GET['event_id'];

// Fetch event summary
try {
    $stmt = $pdo->prepare("
        SELECT e.name, e.budget AS allocated_budget, 
               IFNULL(SUM(b.amount), 0) AS used_budget
        FROM events e
        LEFT JOIN budget_items b ON e.event_id = b.eventid
        WHERE e.event_id = :event_id
        GROUP BY e.event_id
    ");
    $stmt->execute([':event_id' => $event_id]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$summary) {
        die("Invalid event.");
    }

    $remaining = $summary['allocated_budget'] - $summary['used_budget'];

    // Fetch detailed budget items
    $stmt = $pdo->prepare("
        SELECT 
            b.item, 
            b.description, 
            b.amount, 
          CONCAT(u.firstname, ' ', u.lastname) AS submitted_by,
           b.created_at
        FROM budget_items b
        JOIN users u ON b.created_by = u.id
        WHERE b.eventid = :event_id
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([':event_id' => $event_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Financial Report - <?= htmlspecialchars($summary['name']) ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
    body {
        font-family: 'poppins';
    }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h3>Financial Report for: <strong><?= htmlspecialchars($summary['name']) ?></strong></h3>

        <table class="table table-bordered mt-3">
            <tr>
                <th>Approved Funds</th>
                <td>KES <?= number_format($summary['allocated_budget'], 2) ?></td>
            </tr>
            <tr>
                <th>Total Expenditure</th>
                <td>KES <?= number_format($summary['used_budget'], 2) ?></td>
            </tr>
            <tr>
                <th>Remaining Balance</th>
                <td style="color: <?= $remaining < 0 ? 'red' : 'green' ?>;">
                    KES <?= number_format($remaining, 2) ?>
                </td>
            </tr>
        </table>

        <h5 class="mt-4">Expenditures</h5>
        <table class="table table-striped table-sm">
            <thead class="thead-dark">
                <tr>
                    <th>Item</th>
                    <th>Description</th>
                    <th>Amount (KES)</th>
                    <th>Submitted By</th>
                    <th>Date Submitted</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($items) > 0): ?>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['item']) ?></td>
                    <td><?= htmlspecialchars($item['description']) ?></td>
                    <td><?= number_format($item['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($item['submitted_by']) ?></td>
                    <td><?= date('d M Y, H:i', strtotime($item['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">No expenditures recorded for this event.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <a href="adminbudget.php" class="btn btn-secondary">← Back to Budget Overview</a>
    </div>
</body>

</html>