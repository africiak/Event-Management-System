<?php
require 'connection.php';
session_start();

// Get data from the AJAX request
$task_id = $_POST['task_id'] ?? null;
$new_status = $_POST['status'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

// Validate
if (!$task_id || !$new_status || !$user_id) {
    http_response_code(400);
    echo "Invalid data provided.";
    exit;
}

// Optional: ensure the user is assigned to this task (restrict editing)
$stmt = $pdo->prepare("SELECT assigned_to FROM tasks WHERE id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch();

if (!$task || $task['assigned_to'] != $user_id) {
    http_response_code(403);
    echo "Unauthorized.";
    exit;
}

// Update the task status
$update = $pdo->prepare("UPDATE tasks SET status = ?, updated_at = NOW() WHERE id = ?");
$update->execute([$new_status, $task_id]);

echo "Status updated successfully!";