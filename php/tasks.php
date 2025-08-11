<?php
session_start();
require 'connection.php';
require 'logger.php';


$user_id = $_SESSION['user_id'];
$rank = $_SESSION['rank'];

if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'admin') {
    header("Location: unauthorized.php");
    exit();
}

if ($rank === 'President') {
    // Show all tasks
    $stmt = $pdo->query("SELECT * FROM tasks ORDER BY due_date ASC");
} else {
    // Show only tasks assigned to this user
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE assigned_to = :id ORDER BY due_date ASC");
    $stmt->execute([':id' => $user_id]);
}

$tasks = $stmt->fetchAll();

$stmt = $pdo->query("SELECT id, CONCAT(firstname, ' ', lastname) AS fullname, profilepic FROM users WHERE rank = 'committee' AND status = 'active'");
$users = $stmt->fetchAll();

$stmt = $pdo->query("SELECT event_id, name, date  FROM events WHERE status = 'Approved' and activity_status = 'active' ");
$events = $stmt->fetchAll();

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$success='';
$error='';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $event_id = $_POST['event_id'] ?? '';
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $assigned_to = $_POST['assigned_to'] ?? '';
        $due_date = $_POST['due_date'] ?? '';
        $priority = $_POST['priority'] ?? 'Low';
        $status = $_POST['status'] ?? 'Pending';
        $assigned_by = $_SESSION['user_id'] ?? null;

        if (!$assigned_by || !$title || !$description || !$assigned_to || !$due_date || !$priority || !$status) {
            throw new Exception("All fields are required.");
        }

        // Insert task
        $stmt = $pdo->prepare("INSERT INTO tasks (event_id, title, description, assigned_to, assigned_by, due_date, priority, status)
        VALUES (:event_id, :title, :description, :assigned_to, :assigned_by, :due_date, :priority, :status)");

        $stmt->execute([
            ':event_id' => $event_id,
            ':title' => htmlspecialchars($title),
            ':description' => htmlspecialchars($description),
            ':assigned_to' => $assigned_to,
            ':assigned_by' => $assigned_by,
            ':due_date' => $due_date,
            ':priority' => $priority,
            ':status' => $status
        ]);

        // ✅ Notification logic inside try block
        $notificationMessage = "You have been assigned a new task: " . htmlspecialchars($title);

        $notificationStmt = $pdo->prepare("INSERT INTO notifications (user_id, event_id, message, type, status, created_at)
            VALUES (:user_id, :event_id, :message, :type,  :status, NOW())");

        $notificationSuccess = $notificationStmt->execute([
            ':user_id' => $assigned_to,
            ':event_id' => $event_id,
            ':message' => $notificationMessage,
            ':type' => 'task',
            ':status' => 'unread'
        ]);

        if (!$notificationSuccess) {
            throw new Exception("Task added, but notification failed.");
        }
        $userStmt = $pdo->prepare("SELECT firstname, lastname FROM users WHERE id = :id");
        $userStmt->execute([':id' => $assigned_to]);
        $assignee = $userStmt->fetch(PDO::FETCH_ASSOC);
        $assigneeName = $assignee ? $assignee['firstname'] . ' ' . $assignee['lastname'] : "User ID $assigned_to";


        logActivity(
            $pdo,
            $assigned_by,
            'Assign Task',
            "Assigned task '$title' to $assigneeName (User ID: $assigned_to) for event ID $event_id"
        );

        echo "<script>alert('Task successfully assigned and user notified.'); window.location.href = '" . $_SERVER['PHP_SELF'] . "';</script>";

        exit();

    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}





try{
$stmt = $pdo->query("
    SELECT 
        tasks.*, 
        CONCAT(users.firstname, ' ', users.lastname) AS assigned_to_name,
        users.profilepic AS assigned_to_pic,
        events.name AS event_name
    FROM tasks
    JOIN users ON tasks.assigned_to = users.id
    JOIN events ON tasks.event_id = events.event_id
");
$tasks = $stmt->fetchAll();
}catch (PDOException $e) {
    echo 'Query error: ' . $e->getMessage();

}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = $_POST['task_id'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
    $stmt->execute([$status, $task_id]);

    echo "success";
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks</title>
    <link rel="stylesheet" href="../css/tasks.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

    <nav class="custom_nav">
        <div class="logo">
            <img src="../img/logowhite.png" alt="bee icon" width="30px" height="30px">
        </div>
        <div class="nav-links">
            <a href="dashboard.php" class="nav-item" data-text="My Events"><i class="fas fa-calendar-check"></i>
                <p style="font-size:13px; padding-left:5px;">Events</p>
            </a>
            <a href="eventform.php" class="nav-item" data-text="Create"><i class="fas fa-plus-circle"></i>
                <p style="font-size:13px; padding-left:5px;">create</p>
            </a>
            <a href="tasks.php" class="nav-item" data-text="Tasks"><i class="fas fa-tasks"></i>
                <p style="font-size:13px; padding-left:5px;">tasks</p>
            </a>
            <a href="resources.php" class="nav-item" data-text="Resources"><i class="fas fa-folder-open"></i>
                <p style="font-size:13px; padding-left:5px;">Utility</p>
            </a>
            <a href="calender.php" class="nav-item" data-text="Calendar"><i class="fas fa-calendar-alt"></i>
                <p style="font-size:13px; padding-left:5px;">agenda</p>
            </a>
            <a href="profile.php" class="nav-item" data-text="Profile"><i class="fas fa-user"></i>
                <p style="font-size:13px; padding-left:5px;">profile</p>
            </a>
            <a href="notifications.php" class="nav-item" data-text="Notifications"><i class="fas fa-bell"></i>
                <p style="font-size:13px; padding-left:5px;">alerts</p>
            </a>
        </div>
        <div class="exit">
            <a href="logout.php" onclick="confirmLogout(event)" id="logout"> <i class="fas fa-sign-out-alt"></i>
                <p>Logout</p>
            </a>
        </div>
    </nav>
    <div class="container">
        <div class="task-container">
            <?php if ($_SESSION['rank'] === 'President'): ?>
            <form id="assignTaskForm" class="form-section" method="POST" action="tasks.php">
                <div class="title">
                    <p>Assignment Form
                        <img src="../img/logoblack.png" alt="bee icon" width="20px" height="20px">
                    </p>
                </div>
                <label for="event_id">Event</label>
                <select id="event_id" name="event_id" required>
                    <option value="">Select Event</option>
                    <?php foreach ($events as $event): ?>
                    <option value="<?= htmlspecialchars($event['event_id']) ?>">
                        <?= htmlspecialchars($event['name']) ?>(<?= date('M j, Y', strtotime($event['date'])) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
                <label for="title">Task</label>
                <input type="text" id="title" name="title" placeholder="Task Title" required>
                <label for="description">Task Description</label>
                <textarea id="description" name="description" placeholder="Task Description" required></textarea>
                <label for="assigned_to">Assigned</label>
                <select id="assigned_to" name="assigned_to" required>
                    <option value="">Assign To</option>
                    <?php foreach ($users as $user): ?>
                    <option value="<?= htmlspecialchars($user['id']) ?>">
                        <?= htmlspecialchars($user['fullname']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <label for="due_date">Deadline</label>
                <input type="date" id="due_date" name="due_date" required>
                <label for="priority">Priority</label>
                <select id="priority" name="priority">
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                </select>
                <label for="status">status</label>
                <select id="status" name="status">
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                </select>
                <button type="submit">ASSIGN TASK</button>
                <?php if (!empty($success)): ?>
                <?php endif; ?>

            </form>
            <?php endif; ?>
            <!-- Task Display Section -->
            <div class="task-list-section">
                <div class="title">
                    <p style="font-size: 25px;"> <strong>Tasks List</strong>
                        <img src="../img/logoblack.png" alt="bee icon" width="20px" height="20px">
                    </p>
                </div>
                <div id="taskList">
                    <div class="task-list-minimal">
                        <?php foreach ($tasks as $task): ?>
                        <?php if ($_SESSION['rank'] === 'committee'): ?>
                        <div class="task-row clickable" data-task-id="<?= $task['id']?>">
                            <?php else: ?>
                            <div class="task-row">
                                <?php endif; ?>
                                <div class="task-user">
                                    <img src="<?= htmlspecialchars($task['assigned_to_pic']) ?>" alt="Profile"
                                        class="avatar-mini">
                                    <div>
                                        <strong><?= htmlspecialchars($task['title']) ?></strong><br>
                                        <small><?= htmlspecialchars($task['assigned_to_name']) ?></small>
                                    </div>
                                </div>
                                <div class="task-meta">
                                    <span class="badge <?= strtolower(str_replace(' ', '-', $task['status'])) ?>">
                                        <?= htmlspecialchars($task['status']) ?>
                                    </span>
                                    <span class="event-name"><?= htmlspecialchars($task['event_name']) ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                    </div>
                </div>
                <!--Task Modal-->
                <div class="taskModal" id="taskModal" style="display: none; width: 700px;">
                    <h3 id="modal-title"></h3>
                    <p id="modal-desc"></p>
                    <p><strong>Event:</strong> <span id="modal-event"></span></p>
                    <p><strong>Due Date:</strong> <span id="modal-due-date"></span></p>
                    <p><strong>Due Status:</strong> <span id="modal-due-status"></span></p>
                    <p><strong>Assigned By:</strong> <span id="modal-assigned-by"></span></p>
                    <p><strong>Priority:</strong> <span id="modal-priority"></span></p>
                    <p>Status: <span id="modal-status"></span></p>

                    <!-- Form (hidden unless assigned user) -->
                    <form id="statusForm" style="display: none;">
                        <input type="hidden" name="task_id" id="form-task-id">
                        <label for="status">Update Status:</label>
                        <select name="status" id="status-select">
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                        </select>
                        <button type="submit">Update</button>
                    </form>

                    <button onclick="closeModal()">Close</button>
                </div>
            </div>
        </div>
        <script src="script.js"></script>
        <script>
        function loadTask(taskId) {
            fetch('get_task.php?task_id=' + taskId)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('modal-title').textContent = data.title;
                    document.getElementById('modal-desc').textContent = data.description;
                    document.getElementById('modal-status').textContent = data.status;
                    document.getElementById('form-task-id').value = data.id;
                    document.getElementById('status-select').value = data.status;
                    document.getElementById('modal-event').textContent = data.event_name;
                    document.getElementById('modal-due-date').textContent = data.due_date;
                    document.getElementById('modal-assigned-by').textContent = data.assigned_by;
                    document.getElementById('modal-priority').textContent = data.priority;

                    const dueDate = new Date(data.due_date);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0); // normalize today's date
                    dueDate.setHours(0, 0, 0, 0); // normalize due date

                    let dueStatusText = '';
                    if (dueDate.getTime() === today.getTime()) {
                        dueStatusText = 'Today';
                    } else if (dueDate > today) {
                        dueStatusText = 'Upcoming';
                    } else {
                        dueStatusText = 'Past ';
                    }

                    document.getElementById('modal-due-status').textContent = dueStatusText;

                    // Show/hide status form
                    document.getElementById('statusForm').style.display = data.can_edit ? 'block' : 'none';

                    document.getElementById('taskModal').style.display = 'block';
                });
        }

        function closeModal() {
            document.getElementById('taskModal').style.display = 'none';
        }

        document.querySelectorAll('.clickable').forEach(row => {
            row.addEventListener('click', function() {
                loadTask(this.dataset.taskId);
            });
        });

        document.getElementById('statusForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('update_task.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(response => {
                    alert(response);
                    closeModal();
                    location.reload();
                });
        });
        </script>
</body>

</html>