<?php
require 'connection.php';
require_once 'logger.php';
session_start();

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_id = $_POST['event_id'];
    $decision = $_POST['decision']; // 1 = Approved, 0 = Rejected
    $comments = $_POST['comments'];
    $approved_by = $_SESSION['user_id']; // Logged-in admin

    try {
        // Check if the event has already been approved/rejected
        $checkStmt = $pdo->prepare("SELECT * FROM approvals WHERE event_id = ?");
        $checkStmt->execute([$event_id]);
        $existingApproval = $checkStmt->fetch();

        if ($existingApproval) {
            echo json_encode(["success" => false, "message" => "Error: Decision already made!"]);
            exit;
        }

        // Insert approval decision
        $stmt = $pdo->prepare("INSERT INTO approvals (event_id, approved_by, decision, comments) VALUES (?, ?, ?, ?)");
        if (!$stmt->execute([$event_id, $approved_by, $decision, $comments])) {
            throw new Exception("Failed to insert approval record.");
        }

        // Update event status
        $updateStmt = $pdo->prepare("UPDATE events SET status = ? WHERE event_id = ?");
        if (!$updateStmt->execute([$decision == 1 ? 'approved' : 'rejected', $event_id])) {
            throw new Exception("Failed to update event status.");
        }

        //user notification retrieve id
        $eventStmt = $pdo->prepare("SELECT created_by, name  FROM events WHERE event_id = ?");
        $eventStmt->execute([$event_id]);
        $event = $eventStmt->fetch();

        if (!$event) {
            throw new Exception("Event not found.");
        }

        $created_by_user_id = $event['created_by'];
        $event_name = $event['name'];

        //retrive comments
        $approvalStmt = $pdo->prepare("SELECT comments, decision FROM approvals WHERE event_id = ? AND approved_by = ?");
        $approvalStmt->execute([$event_id, $approved_by]);
        $approval = $approvalStmt->fetch();

        if (!$approval) {
            throw new Exception("Approval record not found.");
        }

        $admin_comments = $approval['comments'];
        $approval_status = $approval['decision'] == 1 ? 'approved' : 'rejected';

          // Create notification message
          $message = "Your event'" . $event_name ."' has been " . $approval_status . ".\n";
          $message .= "Admin's comments: " . $admin_comments;

          $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, event_id, message, status, created_at) VALUES (?, ?, ?, ?, NOW())");
          if (!$notifStmt->execute([$created_by_user_id, $event_id, $message, 'unread'])) {
              throw new Exception("Failed to create notification.");
          }
          
        $action = $approval_status == 'approved' ? 'Event Approved' : 'Event Rejected';
$description = "$action: '$event_name' (ID: $event_id). Comments: $admin_comments";
logActivity($pdo, $approved_by, $action, $description);

        echo json_encode(["success" => true, "message" => "Decision submitted successfully!"]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
}

?>