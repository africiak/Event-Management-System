<?php
session_start();
include 'connection.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'admin') {
    header("Location: unauthorized.php");
    exit();
}
$user_role = $_SESSION['role'] ?? null;
$rank = $_SESSION['rank'] ?? null;
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

try{
$sql = "
SELECT 
    e.name AS event_name,
    e.date,
    e.category, 
    v.name AS location,
    u1.firstname AS organiser, 
    u2.firstname AS created_by,
    e.status,
    u2.role AS creator_role
    FROM  events e
    LEFT JOIN venues v ON e.location_id = v.location_id
    LEFT JOIN users u1 ON e.organiser_id = u1.id
    LEFT JOIN users u2 ON e.created_by = u2.id
    WHERE e.activity_status = 'active'
    ORDER BY e.date DESC
    ";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
$userRole = $_SESSION['role'] ?? 'studentLeader';

if ($userRole !== 'facultyMember') {
    $events = array_filter($events, function($event) {
        return $event['creator_role'] !== 'facultyMember';
    });
}
$filter = $_GET['filter'] ?? 'all';
$filteredEvents = array_filter($events, function ($event) use ($filter) {
    if ($filter === 'all') return true;
    return strtolower($event['status']) === strtolower($filter);
});
}catch(PDOException $e){
die("Database error: ". $e->getMessage());
}

$colors = ['#f4f7fc', '#f3e5f5', '#e2f2f1', '#f0f4c3', '#ffebee'];
function isActive($value, $current) {
    return $value === $current ? 'font-weight: bold; text-decoration: underline;' : '';
}

$unread_count = 0;
if ($user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = :user_id AND status = 'unread'");
    $stmt->execute([':user_id' => $user_id]);
    $notif = $stmt->fetch();
    $unread_count = $notif['unread_count'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/student.css">
    <title>Dashboard</title>
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
            <a href="dashboard.php" class="nav-item" data-text="My Events"><i class="fas fa-calendar-check"></i>
                <p style="font-size:13px; padding-left:5px;">Events</p>
            </a>
            <a href="eventform.php" class="nav-item" data-text="Create"><i class="fas fa-plus-circle"></i>
                <p style="font-size:13px; padding-left:5px;">create</p>
            </a>
            <?php if ($_SESSION['role'] != 'facultyMember'): ?>
            <a href="tasks.php" class="nav-item" data-text="Tasks">
                <i class="fas fa-tasks"></i>
                <p style="font-size:13px; padding-left:5px;">Tasks</p>
            </a>

            <a href="resources.php" class="nav-item" data-text="Resources">
                <i class="fas fa-folder-open"></i>
                <p style="font-size:13px; padding-left:5px;">Utility</p>
            </a>
            <?php endif; ?>
            <a href="calender.php" class="nav-item" data-text="Calendar"><i class="fas fa-calendar-alt"></i>
                <p style="font-size:13px; padding-left:5px;">agenda</p>
            </a>
            <a href="profile.php" class="nav-item" data-text="Profile"><i class="fas fa-user"></i>
                <p style="font-size:13px; padding-left:5px;">profile</p>
            </a>
            <a href="notifications.php" class="nav-item" data-text="Notifications"><i class="fas fa-bell"></i>
                <p style="font-size:13px; padding-left:5px;">alerts</p>
            </a>
        </div>
        <div class="exit">
            <a href="logout.php" onclick="confirmLogout(event)" id="logout"> <i class="fas fa-sign-out-alt"></i>
                <p>Logout</p>
            </a>
        </div>
    </nav>

    <div class="container" style="padding-left: 150px; overflow-x:auto; ">
        <div class="status-wrapper">
            <div class="status-card">
                <div class="approved">
                    <p>Approved:<?= $approved_total ?></p>
                </div>
                <div class="pending">

                    <p>Pending:<?= $pending_total ?></p>
                </div>
                <div class="rejected">

                    <p>Rejected:<?= $rejected_total ?></p>
                </div>
                <div class="welcome">
                    <img src="../img/Big Shoes - Torso.png" alt="bee icon" width="60px" height="40px">
                </div>
            </div>

        </div>
        <div class="event-cards">
            <?php if (!empty($filteredEvents)): ?>
            <?php foreach ($filteredEvents as $index => $event): ?>

            <div class="event-card">
                <div class="event-content">
                    <div class="accent-strip"
                        style="background-color: <?php echo $colors[$index % count($colors)]; ?>;">
                        <div class="event-summary">
                            <h3><?php echo htmlspecialchars($event['event_name']); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <p>No <?php echo htmlspecialchars($filter); ?> events found.</p>
            <?php endif; ?>

        </div>
    </div>

    <div class="side_nav">

        <!-- Profile Image -->
        <div class="img" style="display: flex; justify-content: center; align-items: center; margin-top: 20px;">
            <?php if (!empty($profilepic)): ?>
            <img src="<?= htmlspecialchars($profilepic); ?>" alt="Profile Picture"
                style="width:90px; height:100px; border-radius:50%; object-fit: cover;">
            <?php else: ?>
            <img src="uploads/default-avatar.png" alt="Default Profile Picture"
                style="width:90px; height:100px; border-radius:50%; object-fit: cover;">
            <?php endif; ?>
        </div>

        <!-- User Name + Rank -->
        <div class="rank">
            <p style="text-align: center;">
                <span style="font-weight: bold;">
                    <?= htmlspecialchars($firstname . ' ' . $lastname); ?>
                </span><br>
                <?= htmlspecialchars($rank ?? ''); ?>
                <br>
                <a href="notifications.php" class="notif-bell"
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

        <!-- Event Filters -->
        <ul class="filter">
            <li><a href="?filter=all" style="text-decoration: none; color: #3b465f; <?= isActive('all', $filter); ?>">📋
                    All Events</a></li>
            <li><a href="?filter=approved"
                    style="text-decoration: none; color: #28a745; <?= isActive('approved', $filter); ?>">✅ Approved</a>
            </li>
            <li><a href="?filter=pending"
                    style="text-decoration: none; color: #ffc107; <?= isActive('pending', $filter); ?>">⏳ Pending</a>
            </li>
            <li><a href="?filter=rejected"
                    style="text-decoration: none; color: #dc3545; <?= isActive('rejected', $filter); ?>">❌ Rejected</a>
            </li>
        </ul>

        <?php if (isset($_SESSION['rank']) && $_SESSION['rank'] === 'President'): ?>
        <a href="budget.php" class="btn"
            style="margin-top: 100px; margin-left:20px; background-color: #FF6D1F; color: #fff; font-family:'poppins'">
            Manage Budget
        </a>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous">
    </script>
    <script src="script.js"></script>
</body>

</html>