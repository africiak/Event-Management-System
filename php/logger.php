<?php

function logActivity($pdo, $user_id, $action, $description) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Agent';

    $stmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at)
        VALUES (:user_id, :action, :description, :ip, :agent, NOW())
    ");
    $stmt->execute([
        ':user_id' => $user_id,
        ':action' => $action,
        ':description' => $description,
        ':ip' => $ip,
        ':agent' => $agent
    ]);
}