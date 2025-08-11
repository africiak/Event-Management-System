<?php
session_start();
require 'connection.php';
$user_id = $_SESSION['user_id'];
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'studentLeader') {
    header("Location: unauthorized.php");
    exit();
}

$stmt= $pdo->prepare(
    "
    SELECT 
        b.id,
        b.quantity_booked,
        b.status,
        e.name AS event_name,
        r.resource_name AS resource_name,
        u.firstname,
        u.lastname
    FROM bookings b
    JOIN events e ON b.event_id = e.event_id
    JOIN resources r ON b.resource_id = r.id
    JOIN users u ON b.created_by = u.id
    ORDER BY b.id DESC
    ");
  $stmt->execute();
  $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);  


 if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['id'] ?? null;
    $newStatus = $_POST['status'] ?? '';

    $allowedStatuses = ['booked', 'in_use', 'released'];

    if ($bookingId && in_array($newStatus, $allowedStatuses)) {
        // Fetch current status from DB
        $stmt = $pdo->prepare("SELECT status FROM bookings WHERE id = ?");
        $stmt->execute([$bookingId]);
        $currentStatus = $stmt->fetchColumn();

        // Define allowed transitions
        $validTransitions = [
            'booked' => ['in_use'],
            'in_use' => ['released'],
            'released' => [] // No further updates allowed
        ];

        // Check if the transition is valid
        if (in_array($newStatus, $validTransitions[$currentStatus])) {
            $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $bookingId]);
            echo "<script>alert('Status updated successfully'); window.location.href='manage.php';</script>";
            exit;
        } else {
            echo "<script>alert('Invalid status transition'); window.location.href='manage.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Invalid status or booking ID'); window.location.href='manage.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/manage.css">
</head>

<body>
    <div class="container">
        <div class="logo" style="font-family:'poppins'; font-size:20px; color:#FF6D1F; padding-bottom: 20px;">
            <strong>Bookings</strong> <img src="../img/logoblack.png" alt="bee icon" width="30px" height="30px">
        </div>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">Booking ID</th>
                    <th scope="col">Event Name</th>
                    <th scope="col">Resource Name</th>
                    <th scope="col">Booked By</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?= htmlspecialchars($booking['id']) ?></td>
                    <td><?= htmlspecialchars($booking['event_name']) ?></td>
                    <td><?= htmlspecialchars($booking['resource_name']) ?></td>
                    <td><?= htmlspecialchars($booking['firstname'] . ' ' . $booking['lastname']) ?></td>
                    <td><?= htmlspecialchars($booking['quantity_booked']) ?></td>
                    <td>
                        <form method="post" action="manage.php" class="form">
                            <input type="hidden" name="id" value="<?= $booking['id'] ?>">
                            <select name="status">
                                <option value="booked" <?= $booking['status'] === 'booked' ? 'selected' : '' ?>>Booked
                                </option>
                                <option value="in_use" <?= $booking['status'] === 'in_use' ? 'selected' : '' ?>>In Use
                                </option>
                                <option value="released" <?= $booking['status'] === 'released' ? 'selected' : '' ?>>
                                    Released
                                </option>
                            </select>
                            <button type="submit">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <button><a href="resources.php">Back </a></button>
</body>

</html>