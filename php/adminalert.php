<?php
session_start();
require 'connection.php';

$admin_id = $_SESSION['user_id'];
$sql = "SELECT id, event_id, message, status, created_at
        FROM notifications 
        WHERE  user_id= ? 
        ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$admin_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/adminalert.css">
    <link rel="stylesheet" href="../css/admin.css">
    <title>Admin alerts</title>
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
    <div class="container">
        <?php if (!empty($notifications)): ?>
        <div class="table-wrapper" style="max-height: 600px; overflow-y: auto; border-radius: 12px;">
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
                            <a href="adminevents.php?event_id=<?=$notif['event_id']?>" class="notif-link"
                                data-notif-id="<?= $notif['id'] ?>" class="notif-link">
                                view event
                            </a>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>
        <?php else:?>
        <div class="alert alert-info text-center">No notifications found.</div>
        <?php endif; ?>
    </div>
    <script src="script.js"></script>
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
</body>

</html>