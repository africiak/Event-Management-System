<?php
require 'connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_item_id'])) {
    $id = (int)$_POST['delete_item_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM budget_items WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $_SESSION['deleted'] = true; // Save message to session
        header("Location: budget.php");
        exit();

    } catch (PDOException $e) {
        die("Error deleting item: " . $e->getMessage());
    }
} else {
    header("Location: budget.php");
    exit();
}