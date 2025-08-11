<?php
require 'connection.php';
session_start();

$task_id = $_GET['task_id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT tasks.*,
    CONCAT(u.firstname, ' ', u.lastname) AS assigned_to_name,
    (SELECT CONCAT(firstname, ' ', lastname) FROM users WHERE id = tasks.assigned_by) AS assigned_by_name,
    e.name AS event_name
    FROM tasks
    JOIN users u ON tasks.assigned_to = u.id
    JOIN events e ON tasks.event_id = e.event_id
    WHERE tasks.id = ?
");
$stmt->execute([$task_id]);
$task = $stmt->fetch();

if ($task) {
    echo json_encode([
        'id' => $task['id'],
        'title' => $task['title'],
        'description' => $task['description'],
        'assigned_by' => $task['assigned_by_name'],
        'event_name' => $task['event_name'],
        'due_date' => $task['due_date'],
        'priority' => $task['priority'],
        'status' => $task['status'],
        'can_edit' => $task['assigned_to'] == $_SESSION['user_id']  // restrict edit
    ]);
}