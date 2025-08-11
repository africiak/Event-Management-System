<?php
session_start();
include 'connection.php';


$user_id = $_SESSION['user_id'] ?? null;
$event_id = $_SESSION['event_id'] ?? null;


$filters = [];
$params = [];

// Only add filters if the corresponding GET values are set and not empty
if (!empty($_GET['from_date'])) {
    $filters[] = "activity_logs.created_at >= :from_date";
    $params[':from_date'] = $_GET['from_date'] . " 00:00:00";
}

if (!empty($_GET['to_date'])) {
    $filters[] = "activity_logs.created_at <= :to_date";
    $params[':to_date'] = $_GET['to_date'] . " 23:59:59";
}

if (!empty($_GET['action'])) {
    $filters[] = "activity_logs.action = :action";
    $params[':action'] = $_GET['action'];
}

if (!empty($_GET['user'])) {
    $filters[] = "(users.firstname LIKE :user OR users.lastname LIKE :user)";
    $params[':user'] = "%" . $_GET['user'] . "%";
}

if (!empty($_GET['keyword'])) {
    $filters[] = "activity_logs.description LIKE :keyword";
    $params[':keyword'] = "%" . $_GET['keyword'] . "%";
}

// Combine filters into a WHERE clause
$where = count($filters) > 0 ? "WHERE " . implode(" AND ", $filters) : "";


try{
$sql = "
SELECT 
    activity_logs.id,
    activity_logs.user_id,
     CONCAT(users.firstname, ' ', users.lastname) AS user_name,
    activity_logs.action,
    activity_logs.description,
    activity_logs.created_at
FROM activity_logs
LEFT JOIN users ON activity_logs.user_id = users.id
$where
    ORDER BY activity_logs.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}catch(PDOException $e){
die("Database error: ". $e->getMessage());
}

function getColor($action) {
    return match ($action) {
        'Login Failure' => 'danger',
        'Login Success' => 'success',
        'Create Event' => 'primary',
        'Book Resource' => 'warning',
        'Profile Update' => 'info',
        'Assign Task' => 'dark',
        'Logout' => 'danger',
        default => 'secondary',
    };
}




?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/adminlogs.css">
    <title>ADMIN</title>
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

        <div class="eventstable">

            <form method="GET" style="margin-bottom: 20px;">
                <label>From: <input type="date" name="from_date" value="<?= $_GET['from_date'] ?? '' ?>"></label>
                <label>To: <input type="date" name="to_date" value="<?= $_GET['to_date'] ?? '' ?>"></label>

                <label>Action Type:
                    <select name="action">
                        <option value="">All</option>
                        <option value="Login Success">Login</option>
                        <option value="Create Event">event</option>
                        <option value="Profile Update">profile</option>
                        <option value="Logout">Logout</option>
                        <option value="Book Resource">bookings</option>
                        <option value="Assign Task">tasks</option>
                    </select>
                </label>

                <label>User: <input type="text" name="user" placeholder="First or Last name"
                        value="<?= $_GET['user'] ?? '' ?>"></label>

                <label>Keyword: <input type="text" name="keyword" placeholder="Search in description..."
                        value="<?= $_GET['keyword'] ?? '' ?>"></label>

                <button type="submit">Filter</button>
            </form>
            <option value="Login" <?= ($_GET['action'] ?? '') === 'Login' ? 'selected' : '' ?>>Login</option>

            <div class="table-responsive" style="max-height: 400px; overflow-y: auto; border-radius: 12px;">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= $log['id'] ?></td>
                            <td><?= $log['user_name'] ?? 'Unknown' ?></td>
                            <td><span class="badge bg-<?= getColor($log['action']) ?>"><?= $log['action'] ?></span></td>
                            <td><?= $log['description'] ?></td>
                            <td><?= $log['created_at'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
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
<script src="script.js"></script>


</html>