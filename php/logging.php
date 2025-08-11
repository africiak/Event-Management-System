<?php
function logActivity($pdo, $userId, $action, $description = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent)
                           VALUES (:user_id, :action, :description, :ip_address, :user_agent)");
    $stmt->execute([
        ':user_id' => $userId,
        ':action' => $action,
        ':description' => $description,
        ':ip_address' => $ip,
        ':user_agent' => $agent
    ]);
}

?>