<?php
require 'connection.php';

if (isset($_GET['event_id']) && is_numeric($_GET['event_id'])) {
    $event_id = (int) $_GET['event_id'];

    try {
        $sql = "
        SELECT 
            e.event_id,
            e.name AS event_name,
            e.description,
            DATE_FORMAT(e.date, '%M %d, %Y') AS date,
            e.category, 
            v.name AS location,
            u1.firstname AS organiser, 
            u2.firstname AS created_by,
            e.budget,
            e.status
        FROM events e
        LEFT JOIN venues v ON e.location_id = v.location_id
        LEFT JOIN users u1 ON e.organiser_id = u1.id
        LEFT JOIN users u2 ON e.created_by = u2.id
        WHERE e.event_id = :event_id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
        $stmt->execute();
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($event) {
            echo json_encode(["success" => true, "data" => $event]);
        } else {
            echo json_encode(["success" => false, "message" => "Event not found."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid Event ID"]);
}
?>