<?php
session_start();
require 'connection.php';
$user_id = $_SESSION['user_id'];
if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'admin') {
    header("Location: unauthorized.php");
    exit();
}

try{
 $stmt = $pdo->prepare("SELECT * FROM events WHERE activity_status = 'active' AND status = 'Approved'
");
 $stmt->execute();
 $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
}catch(PDOException $e){
die("Error:" . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../css/calender.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
    <div class="container">
        <div class="events">
            <div class="title">
                <p> <strong>EVENTS</strong>
                    <img src="../img/logoblack.png" alt="bee icon" width="20px" height="20px">
                </p>
            </div>
            <div id="eventList">
                <div class="event_minimal">
                    <?php foreach ($events as $event): ?>
                    <div class="card-row">
                        <p class="card-text">
                            <strong><?= htmlspecialchars($event['name'])?></strong> <br>
                            <small>Date: <?= htmlspecialchars($event['date']) ?><br></small>
                            <small>Category: <?= htmlspecialchars($event['category']) ?></small>
                        </p>
                        <button type="button" class="view" data-event-id="<?= $event['event_id']?>">View More</button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div id="eventDetails">
        <p style="text-align: center; font-weight: bold;">
            Select an event to view agenda. <br>
            <img src="../img/hello.svg" alt="" width="500px" height="500px">
        </p>

    </div>


    <script src="script.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('.view').on('click', function() {
            const eventId = $(this).data('event-id');

            $.ajax({
                url: 'get_event_details.php',
                type: 'POST',
                data: {
                    event_id: eventId
                },
                success: function(response) {
                    $('#eventDetails').html(response);
                },
                error: function() {
                    $('#eventDetails').html('<p>Error loading event details.</p>');
                }
            });
        });
    });
    </script>

</body>

</html>