<?php
session_start();
require 'connection.php';

$user_id = $_SESSION['user_id'];
if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'admin') {
    header("Location: unauthorized.php");
    exit();
}
$sql = "SELECT id, event_id, message, type, status, created_at
        FROM notifications 
        WHERE  user_id= ? 
        ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="../css/notification.css">
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
    <div class="custom-container">
        <div class="title" style="margin-top:20px;">
            <p style="font-size: 25px; color:#FF6D1F; "> <strong>Notifications</strong>
                <img src="../img/logoblack.png" alt="bee icon" width="20px" height="20px">
            </p>
        </div>
        </tr>
        <?php if (!empty($notifications)): ?>
        <table class="table table-bordered table-hover" style="border-radius:20px; overflow: hidden;">
            <thead class="table-dark" style="background-color: #14213D;">
                <tr>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notifications as $notif): ?>
                <tr class="notification <?= $notif['status'] == 'unread' ? 'table-warning' : '' ?>">
                    <td><?= htmlspecialchars($notif['message'])?></td>
                    <td>
                        <?php if ($notif['status'] == 'unread'): ?>
                        <span class="badge bg-warning text-dark">Unread</span>
                        <?php else: ?>
                        <span class="badge bg-success">Read</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
$link = "dashboard.php?event_id={$notif['event_id']}";
if ($notif['type'] === 'task') {
    $link = "tasks.php?event_id={$notif['event_id']}";
}
?>

                        <a href="<?= $link ?>" class="notif-link" data-notif-id="<?= $notif['id'] ?>">
                            view <?= $notif['type'] ?>
                        </a>

                    </td>
                </tr>
                <?php endforeach;?>
            </tbody>
        </table>
        <?php else:?>
        <div class="alert alert-info text-center">No notifications found.</div>
        <?php endif; ?>
    </div>
    <script>
    document.querySelectorAll('.notif-link').forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            let notifId = this.getAttribute('data-notif-id');
            let eventUrl = this.getAttribute('href');


            fetch(`status.php?notif_id=${notifId}`)
                .then(response => response.text())
                .then(data => {
                    console.log("Server Response:", data);
                    if (data.trim() === "success") {
                        window.location.href = eventUrl;
                    } else {
                        alert("Failed to update notification status.");
                    }
                })
                .catch(error => console.error("Fetch Error:", error));
        });
    });
    </script>
    <script src="script.js"></script>

</body>

</html>