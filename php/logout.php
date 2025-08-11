<?php

session_start();
require_once 'connection.php';
require_once 'logger.php';

$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    logActivity($pdo, $user_id, 'Logout', 'User logged out successfully.');
}

session_unset();
session_destroy();

header("Location: login.php");
exit;
?>