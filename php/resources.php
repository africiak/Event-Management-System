<?php
session_start();
require 'connection.php';
require 'logger.php';
$user_id = $_SESSION['user_id'];
if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'admin') {
    header("Location: unauthorized.php");
    exit();
}

try{
    $stmt = $pdo->prepare("SELECT * FROM resources WHERE status = 'active'");
    $stmt-> execute();
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
}catch(PDOException $e){
    die("Error:" . $e->getMessage());
}

try{
    $stmt = $pdo->prepare("SELECT event_id, name, date FROM events WHERE  status= 'Approved' AND activity_status ='active'");
    $stmt-> execute();
    $events = $stmt-> fetchAll(PDO::FETCH_ASSOC);
}catch(PDOException $e ){
    die("Error:" . $e->getMessage());
}


if($_SERVER ["REQUEST_METHOD"] ==="POST"){
    $event_id = (int) $_POST['event_id'];
    $resource_id = (int) $_POST['resource_id'];
    $quantity = (int) $_POST['quantity'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT resource_name, quantity FROM resources WHERE id = :id");
    $stmt->execute([':id' => $resource_id]);
    $resource = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resource) {
        die("Resource not found.");
    }

    $resource_name = $resource['resource_name'];
    $available = (int) $resource['quantity'];

    $stmt = $pdo->prepare("SELECT firstname, lastname FROM users WHERE id = :id");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_fullname = $user ? $user['firstname'] . ' ' . $user['lastname'] : 'Unknown User';

    // Validation
    if ($quantity < 1) {
        die("Invalid quantity. Quantity must be at least 1.");
    }
    if ($quantity > $available) {
        die("Cannot book more than available ($available).");
    }


    $stmt = $pdo->prepare("INSERT INTO bookings (event_id, resource_id, created_by, quantity_booked)
    VALUES (:event_id, :resource_id, :created_by, :quantity_booked)");

    $stmt->execute([
        ':event_id' => $event_id,
        ':resource_id' => $resource_id,
        'created_by' => $user_id,
        ':created_by' => $user_id,
        ':quantity_booked' => $quantity
    ]);

       logActivity(
        $pdo,
        $user_id,
        'Book Resource',
        "$user_fullname booked $quantity of '$resource_name' (Resource ID: $resource_id) for event ID $event_id"
    );

             echo "<script> alert('Resource booked successfully'); window.location.href='resources.php'; </script>";

}

$sql = "SELECT r.*, 
               r.quantity - IFNULL(SUM(b.quantity_booked), 0) AS available_quantity
        FROM resources r
        LEFT JOIN bookings b ON r.id = b.resource_id 
        AND b.status IN ('booked', 'in_use')
        WHERE r.status = 'active'
        GROUP BY r.id";
$stmt = $pdo->query($sql);
$resources = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resources</title>
    <link rel="stylesheet" href="../css/resources.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

</head>


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
        <a href="tasks.php" class="nav-item" data-text="Tasks"><i class="fas fa-tasks"></i>
            <p style="font-size:13px; padding-left:5px;">tasks</p>
        </a>
        <a href="resources.php" class="nav-item" data-text="Resources"><i class="fas fa-folder-open"></i>
            <p style="font-size:13px; padding-left:5px;">Utility</p>
        </a>
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
<div class="utility" style="overflow-x: auto; overflow-y:hidden;">
    <div class="container">
        <div class="resource" style="height:600px; overflow-y: auto; margin-left: 60px; width:700px;">
            <div class="logo" style="font-family:'poppins'; font-size:25px; color:#FF6D1F">
                <strong>Resources</strong> <img src="../img/logoblack.png" alt="bee icon" width="30px" height="30px">
            </div>
            <table class="table table-hover" style=" margin-left:60px;">
                <thead>
                    <tr>
                        <th scope="col">Resource</th>
                        <th scope="col">type</th>
                        <th scope="col">Available</th>
                        <th scope="col">Description</th>
                        <th scope="col">Action</th>

                    </tr>
                </thead>
                <tbody>
                    <?php foreach($resources as $res): ?>
                    <tr>
                        <form method="GET" action="resources.php">
                            <td scope="row"><?= htmlspecialchars($res["resource_name"])?></td>
                            <td><?= htmlspecialchars($res["resource_type"])?></td>
                            <td><?= htmlspecialchars($res["available_quantity"])?> /
                                <?= htmlspecialchars($res["quantity"])?></td>
                            <td><?= htmlspecialchars($res["description"])?></td>
                            <td style="display: flex; flex-direction:row; gap:5px;">
                                <button type="button" class="btn btn-secondary" onclick="populateBookingForm(
                        <?= $res['id'] ?>,
                        '<?= htmlspecialchars($res['resource_name'], ENT_QUOTES) ?>',
                        <?= $res['quantity'] ?>)">
                                    Book
                                </button>
                                <button class="btn" style=" background-color:#14213D"><a
                                        href="manage.php">Manage</a></button>
                            </td>
                        </form>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="book" style="padding-left:50px;">
        <div class="form">
            <form action="" method="post">
                <div class="logo" style="font-family:'poppins'; font-size:20px; color:#FF6D1F">
                    Bookings <img src="../img/logoblack.png" alt="bee icon" width="30px" height="30px">
                </div>

                <label for="event_name">Event</label> <br>
                <select name="event_id" required class="form-select">
                    <option value="">-- Choose Event --</option>
                    <?php foreach ($events as $event): ?>
                    <option value="<?= $event['event_id'] ?>">
                        <?= htmlspecialchars($event['name']) ?> - <?= htmlspecialchars($event['date']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <br>
                <input type="hidden" name="resource_id" id="resource_id">

                <label for="resource_name">Resource</label>
                <input type="text" id="resource_name" name="resource_name_display" readonly class="form-control"
                    required>
                <label for="quantity_booked">Quantity</label>
                <input type="number" name="quantity" min="1" max="<? $available ?>" id="max_quantity"
                    class="form-control" required>
                <small id="max_info" class="text-muted"></small>
                <br>
                <button type="submit"
                    style="background-color:#FF6D1F; color: #fff; border:none; border-radius:10px ;padding:10px;">Book
                    resource</button>
            </form>
        </div>
    </div>
</div>
<script src="script.js"></script>
<script>
function populateBookingForm(id, name, maxQty) {
    document.getElementById('resource_id').value = id;
    document.getElementById('resource_name').value = name;
    document.getElementById('max_quantity').max = maxQty;
    document.getElementById('max_info').textContent = `Max available: ${maxQty}`;
}
</script>

</body>

</html>