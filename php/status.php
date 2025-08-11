<?php
require 'connection.php';

if (isset($_GET['notif_id'])) {
    $notif_id = intval($_GET['notif_id']);

try{
    $sql = "UPDATE notifications SET status = 'read' WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$notif_id]);
    
    if ($stmt->execute()) {
        echo "success"; 
    } else {
        echo "error";
    }

}catch(PDOException $e){
    echo "Error: " . $e->getMessage();
}
} 

?>