<?php

require 'connection.php';

try {
    $stmt = $pdo->prepare("
       SELECT 
    e.event_id,
    e.name AS event_name,
    e.budget AS allocated_budget,
    IFNULL(SUM(b.amount), 0) AS used_budget,
    (e.budget - IFNULL(SUM(b.amount), 0)) AS remaining_budget
FROM events e
LEFT JOIN budget_items b ON e.event_id = b.eventid
WHERE e.status = 'Approved' AND e.activity_status = 'Active'
GROUP BY e.event_id
ORDER BY e.created_at DESC;

    ");
    $stmt->execute();
    $event_budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/adminbudget.css">
    <title>Budget</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>

<body>
    <nav class="custom_nav">
        <div class="logo">
            <img src="../img/logowhite.png" alt="bee icon" width="30px" height="30px">
        </div>
        <div class="nav-links">
            <a href="admin.php" class="nav-item" data-text="Dashboard"><i class="fas fa-chart-line"></i>
                <p style="font-size:13px; padding-left:5px;">Home</p>
            </a>
            <a href="adminevents.php" class="nav-item" data-text="Events"><i class="fas fa-calendar-check"></i>
                <p style="font-size:13px; padding-left:5px;">Events</p>
            </a>
            <a href="adminusers.php" class="nav-item" data-text="Users"><i class="fas fa-users"></i>
                <p style="font-size:13px; padding-left:5px;">Users</p>
            </a>
            <a href="adminutil.php" class="nav-item" data-text="Resources"><i class="fas fa-cubes"></i>
                <p style="font-size:13px; padding-left:5px;">Utility</p>
            </a>
            <a href="reports.php" class="nav-item" data-text="Reports"><i class="fas fa-file-alt"></i>
                <p style="font-size:13px; padding-left:5px;">Reports</p>
            </a>
            <a href="adminlogs.php" class="nav-item" data-text="Analytics"><i class="fas fa-chart-bar"></i>
                <p style="font-size:13px; padding-left:5px;">Logs</p>
            </a>
            <a href="adminbudget.php" class="nav-item" data-text="Budget"><i class="fas fa-wallet"></i>
                <p style="font-size:13px; padding-left:5px;">Budget</p>
            </a>
            <a href="adminprofile.php" class="nav-item" data-text="Profile"><i class="fas fa-user-cog"></i>
                <p style="font-size:13px; padding-left:5px;">Profile</p>
            </a>
            <a href="adminalert.php" class="nav-item" data-text="Notifications"><i class="fas fa-bell"></i>
                <p style="font-size:13px; padding-left:5px;">Alerts</p>
            </a>
        </div>
        <div class="exit">
            <a href="logout.php" onclick="confirmLogout(event)" id="logout"> <i class="fas fa-sign-out-alt"></i>
                <p>logout</p>
            </a>
        </div>
    </nav>
    <div class="container" style="font-family:'poppins';">
        <div class="list">
            <div class="logo" style=" font-size:20px; color:#FF6D1F ; margin-bottom: 10px;">
                <strong>Budget list</strong> <img src="../img/logoblack.png" alt="bee icon" width="30px" height="30px">
            </div>
            <table class="table table-hover" style="margin-left: 40px;">
                <thead>
                    <tr>
                        <th scole="col">Event name</th>
                        <th scope="col">Approved Funds</th>
                        <th scope="col">Expenditure to Date</th>
                        <th scope="col">Available balance</th>
                        <th scope="col">Actions</th>

                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($event_budgets as $event): ?>
                    <tr>
                        <td><?= htmlspecialchars($event['event_name']) ?></td>
                        <td>KES <?= number_format($event['allocated_budget'], 2) ?></td>
                        <td>KES <?= number_format($event['used_budget'], 2) ?></td>
                        <td>KES <?= number_format($event['remaining_budget'], 2) ?></td>
                        <td>
                            <a href="budget_details.php?event_id=<?= $event['event_id'] ?>" class="btn btn btn-sm"
                                style="background-color: #FF6D1F; color:#fff; font-family: 'poppins'">View Financial
                                Report</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
        </div>
    </div>
    <script src="script.js"></script>
</body>

</html>