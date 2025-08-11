<?php
require 'connection.php'; 

header('Content-Type: application/json');

try{
    $sql = "SELECT name, date FROM events WHERE status = 'approved'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$events) {
        echo json_encode(['error' => 'No approved events found']);
        exit;
    }

    $colors = ['#FF5733', '#33FF57', '#3357FF', '#FF33A1', '#FFD700', '#DC143C'];
    $formattedEvents = [];

    foreach ($events as $event) {
        $formattedEvents[] = [
            'title' => $event['name'],
            'start' => $event['date'],
            'color' => $colors[array_rand($colors)]
        ];
    }

    echo json_encode($formattedEvents);
}catch(PDOException $e){
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
    ?>