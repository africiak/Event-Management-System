<?php
session_start();
include 'connection.php';

$user_id = $_SESSION['user_id'] ?? null;
$event_id = $_SESSION['event_id'] ?? null;

//redirect to profile page if nulls exist
$query = "SELECT * FROM users WHERE id = :id AND (gender IS NULL OR rank IS NULL OR department IS NULL OR nationality IS NULL OR profilepic IS NULL)";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetchAll();

if (count($result) > 0) {
    echo "<script>
        alert('Your profile is incomplete. Please update your information.');
        window.location.href = 'edit_profile.php';
    </script>";
}

//display user profile and rank
if ($user_id) {
    $sql = "SELECT firstname, lastname, profilepic, rank, role FROM users WHERE id = :user_id";
    $stmt = $pdo->prepare($sql);
    //bind parameter to sql query
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    //execute query
    $stmt->execute();

    
     // Fetch the data
     if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $firstname = $row['firstname'];
        $lastname = $row['lastname'];
        $rank = $row['rank'];
        $role = $row['role'];
        $profilepic = $row['profilepic'];
    }
}

//count approved events
$sql = "SELECT COUNT(*) AS approved_total FROM events WHERE status = 'approved'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$approved_total = $stmt->fetchColumn();

// Count pending events
$sql = "SELECT COUNT(*) AS pending_total FROM events WHERE status = 'pending'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$pending_total = $stmt->fetchColumn();

// Count rejected events
$sql = "SELECT COUNT(*) AS rejected_total FROM events WHERE status = 'rejected'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$rejected_total = $stmt->fetchColumn();


$unread_count = 0;
if ($user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = :user_id AND status = 'unread'");
    $stmt->execute([':user_id' => $user_id]);
    $notif = $stmt->fetch();
    $unread_count = $notif['unread_count'];
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            SUM(e.budget) AS total_allocated,
            IFNULL(SUM(b.amount), 0) AS total_used
        FROM events e
        LEFT JOIN budget_items b ON e.event_id = b.eventid
        WHERE e.status = 'Approved'
    ");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $total_allocated = (float) $row['total_allocated'];
    $total_used = (float) $row['total_used'];
    $total_remaining = $total_allocated - $total_used;

} catch (PDOException $e) {
    die("Error fetching totals: " . $e->getMessage());
}


try {
    $stmt = $pdo->prepare("
        SELECT 
            r.resource_name AS resource_name, 
            COUNT(b.id) AS booking_count
        FROM bookings b
        JOIN resources r ON b.resource_id = r.id
        GROUP BY r.id
        ORDER BY booking_count DESC
        LIMIT 5
    ");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare arrays for JS
    $resourceLabels = [];
    $resourceValues = [];

    foreach ($data as $row) {
        $resourceLabels[] = $row['resource_name'];
        $resourceValues[] = (int)$row['booking_count'];
    }

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
    <title>ADMIN</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <p>Logout</p>
            </a>
        </div>
    </nav>

    <div class="container" style="overflow: scroll;">
        <div class="chart-container">
            <div class="card">
                <h4 class="card-title">Event Status Distribution</h4>
                <input type="hidden" id="approvedCount" value="<?= $approved_total ?>">
                <input type="hidden" id="pendingCount" value="<?= $pending_total ?>">
                <input type="hidden" id="rejectedCount" value="<?= $rejected_total ?>">
                <canvas id="eventStatusChart"></canvas>
            </div>
            <div class="card">
                <h4 class="card-title">Most Booked Resources</h4>
                <canvas id="resourceBarChart"></canvas>
            </div>

            <div class="card">
                <h4 class="card-title">Budget Overview</h4>
                <div style="max-width: 250px; margin: auto;">
                    <canvas id="totalBudgetChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="side_nav">
        <div class="img" style="display: flex; justify-content: center; align-items: center; margin-top: 20px;">
            <?php if (!empty($profilepic)): ?>
            <img src="<?= htmlspecialchars($profilepic); ?>" alt="Profile Picture"
                style="width:90px; height:100px; border-radius:50%; object-fit: cover;">
            <?php else: ?>
            <img src="uploads/default-avatar.png" alt="Default Profile Picture"
                style="width:90px; height:100px; border-radius:50%; object-fit: cover;">
            <?php endif; ?>
        </div>
        <div class="rank">
            <p style="text-align: center;">
                <span style="font-weight: bold;">
                    <?= htmlspecialchars($firstname . ' ' . $lastname); ?>
                </span><br>
                <?= htmlspecialchars($rank ?? ''); ?>
                <br>
                <a href="adminalert.php" class="notif-bell"
                    style="display: inline-flex; align-items: center; gap: 6px;">
                    <span class="bell-icon">📨
                        <?php if ($unread_count > 0): ?>
                        <span class="notif-badge"><?= $unread_count ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="notif-label">Mail</span>
                </a>

            </p>
        </div>



    </div>

    <script src="script.js"></script>
    <script src="../js/chart.js"></script>
    <script>
    const resourceLabels = <?= json_encode($resourceLabels) ?>;
    const resourceValues = <?= json_encode($resourceValues) ?>;
    </script>
    <script src="../js/graph.js"></script>
    <script>
    const totalCtx = document.getElementById('totalBudgetChart').getContext('2d');

    new Chart(totalCtx, {
        type: 'pie',
        data: {
            labels: ['Used Budget', 'Remaining Budget'],
            datasets: [{
                label: 'University Budget Distribution',
                data: [<?= $total_used ?>, <?= $total_remaining ?>],
                backgroundColor: [
                    'rgba(255, 109, 31, 0.7)', // Used - Orange
                    'rgba(22, 51, 119, 0.7)' // Remaining - Navy
                ],
                borderColor: [
                    'rgba(255, 109, 31, 1)',
                    'rgba(20, 33, 61, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let value = context.raw.toLocaleString();
                            return context.label + ': KES ' + value;
                        }
                    }
                }
            }
        }
    });
    </script>


</body>

</html>