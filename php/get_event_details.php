<?php
require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $eventId = $_POST['event_id'];

    // Fetch event details
    $stmt = $pdo->prepare(
        "SELECT 
            e.*,
            v.name AS venue_name,
            u.firstname,
            u.lastname
         FROM events e
         LEFT JOIN venues v ON e.location_id = v.location_id
         LEFT JOIN users u ON e.organiser_id = u.id
         WHERE e.event_id = ?"
    );
    $stmt->execute([$eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($event) {
        echo "<div style='display: flex; gap: 20px; align-items: flex-start; margin-top: 50px; padding: 50px;'>";

        // Image section
        if (!empty($event['file_path'])) {
            echo "<div style='margin-top:50px;'><img src='" . htmlspecialchars($event['file_path']) . "' alt='Event Image' style='width: 300px; height: 300px; object-fit: cover; border-radius: 8px;'></div>";
        } else {
            echo "<div><em>No image available.</em></div>";
        }

        // Event info section
        echo "<div>";
        echo "<h3>" . htmlspecialchars($event['name']) . "</h3>";
        echo "<p><strong>Date:</strong> " . htmlspecialchars($event['date']) . "</p>";
        $eventDate = new DateTime($event['date']);
$today = new DateTime();
$today->setTime(0, 0, 0); // normalize time

if ($eventDate < $today) {
    $label = "<span style='color: gray; font-weight: bold;'>Past Event</span>";
} elseif ($eventDate == $today) {
    $label = "<span style='color: green; font-weight: bold;'>Happening Today</span>";
} else {
    $label = "<span style='color: orange; font-weight: bold;'>Upcoming Event</span>";
}
echo "<p><strong>Status:</strong> $label</p>";
        echo "<p><strong>Category:</strong> " . htmlspecialchars($event['category']) . "</p>";
        echo "<p><strong>Description:</strong> " . nl2br(htmlspecialchars($event['description'])) . "</p>";
        echo "<p><strong>Organizer:</strong> " . htmlspecialchars($event['firstname'] . ' ' . $event['lastname']) . "</p>";
        echo "<p><strong>Location:</strong> " . htmlspecialchars($event['venue_name']) . "</p>";

        // Booked resources
        $stmtRes = $pdo->prepare("
            SELECT r.resource_name AS resource_name, b.quantity_booked
            FROM bookings b
            JOIN resources r ON b.resource_id = r.id
            WHERE b.event_id = ? AND b.status = 'booked'
        ");
        $stmtRes->execute([$eventId]);
        $resources = $stmtRes->fetchAll(PDO::FETCH_ASSOC);

        echo "<h4>Booked Resources</h4>";
        if ($resources) {
            echo "<ul>";
            foreach ($resources as $res) {
                echo "<li>" . htmlspecialchars($res['resource_name']) . " (Qty: " . htmlspecialchars($res['quantity_booked']) . ")</li>";
            }
            echo "</ul>";
        } else {
            echo "<p><em>No booked resources.</em></p>";
        }

        // Tasks
        $stmtTasks = $pdo->prepare("
            SELECT title, status
            FROM tasks
            WHERE event_id = ?
        ");
        $stmtTasks->execute([$eventId]);
        $tasks = $stmtTasks->fetchAll(PDO::FETCH_ASSOC);

        echo "<h4>Tasks</h4>";
        if ($tasks) {
            echo "<ul>";
            foreach ($tasks as $task) {
                echo "<li>" . htmlspecialchars($task['title']) . " - <strong>" . htmlspecialchars($task['status']) . "</strong></li>";
            }
            echo "</ul>";
        } else {
            echo "<p><em>No tasks added yet.</em></p>";
        }

        echo "</div>"; 
        echo "</div>"; 
    } else {
        echo "<p>Event not found.</p>";
    }
} else {
    echo "<p>Invalid request.</p>";
}
?>