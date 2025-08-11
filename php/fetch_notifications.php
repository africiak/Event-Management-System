<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];

// Count unread notifications
$sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = :user_id AND status = 'unread'";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $user_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode(['unread_count' => $result['unread_count']]);
?>