<?php
session_start();
require 'connection.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $user_id = $_POST['user_id'];

    try {
        $stmt = $pdo->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
        $stmt->execute([$user_id]);

        
        session_destroy();
        

        header("Location: goodbye.php"); 
        exit();
    } catch (PDOException $e) {
        echo "Error deactivating account: " . $e->getMessage();
    }
}else {
    header("Location: profile.php"); 
    exit();
}