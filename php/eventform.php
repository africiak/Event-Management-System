<?php
include 'connection.php';
require_once 'logger.php';
session_start();

$created_by =$_SESSION['user_id'];
$user_id = $_SESSION['user_id']; 

//fetch locations from the db
$sql = "SELECT location_id, name FROM venues";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

//fetch users 
$sql = "SELECT id, role, CONCAT(firstname, ' ', lastname) AS full_name FROM users WHERE role != 'admin' AND status = 'active' ";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

try{
    $sql = "CREATE TABLE IF NOT EXISTS events (
        event_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        location_id INT NOT NULL,
        organiser_id INT NOT NULL,
        created_by INT NOT NULL,
        budget DECIMAL(10, 2),
        status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
        approval_by INT DEFAULT NULL,
        approval_date DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (location_id) REFERENCES venues(location_id),
        FOREIGN KEY (organiser_id) REFERENCES users(id),
        FOREIGN KEY (created_by) REFERENCES users(id),
        FOREIGN KEY (approval_by) REFERENCES users(id)
    );";

    $pdo->exec($sql);
   // echo "<script>alert('Table \"events\" created successfully or already exists.');</script>";
}catch(PDOException $e) {
    echo "Error creating table:" .$e->getMessage();
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    //collect form data
    try{
        $name = $_POST['name'];
        $description = $_POST['description'];
        $date = $_POST['date'];
        $end_date = $_POST['end_date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $category = $_POST['category'];
        $budget = $_POST['budget'];
        $organiser_id = $_POST['organiser_id'];
        $location_id = $_POST['location_id'];
        $status = $_POST['status'];
        //handle attachment
        $file_path = null; 

        //check if file was uploded 
        if (isset($_FILES['event_file']) && $_FILES['event_file']['error'] === UPLOAD_ERR_OK) {
            $event_file = $_FILES['event_file'];
            $file_name = $event_file['name'];
            $file_tmp = $event_file['tmp_name'];
            $file_size = $event_file['size'];
            $file_type = $event_file['type'];

            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

            $allowed_types = ['jpg','jpeg','png','pdf'];
            if(!in_array(strtolower($file_ext),$allowed_types)){
                throw new Exception('Invalid file type. Only JPG, JPEG, PNG, and PDF are allowed.');
            }

            if ($file_size > 5 * 1024 * 1024) { // 5MB limit
                throw new Exception('File size exceeds the 5MB limit.');
            }

            $upload_dir = 'uploads/events/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $new_file_name = uniqid('event_', true) . '.' . $file_ext;
            $file_path = $upload_dir . $new_file_name;

        }

        $pdo->beginTransaction(); 

        $sql = "INSERT INTO events (name, description, date, end_date, start_time, end_time, category, location_id, organiser_id, created_by, budget, file_path)
        VALUES (:name, :description, :date, :end_date, :start_time, :end_time, :category, :location_id, :organiser_id, :created_by, :budget, :file_path)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':date' => $date,
            ':end_date' => $end_date,
            ':start_time' => $start_time,
            ':end_time' => $end_time,
            ':category' => $category,
            ':location_id' => $location_id,
            ':organiser_id' => $organiser_id,
            ':created_by' => $created_by,
            ':budget' => $budget,
            ':file_path' => $file_path
            
        ]);


        $event_id = $pdo->lastInsertId();
        if (!$event_id) {
            throw new Exception("Failed to retrieve event ID.");
        }

        $admin_sql = "SELECT id FROM users WHERE role = 'admin'"; 
        $admin_stmt = $pdo->prepare($admin_sql);
        $admin_stmt->execute();
        $admins = $admin_stmt->fetchAll(PDO::FETCH_COLUMN);
    
        if (empty($admins)) {
            throw new Exception("No admins found in the database.");
        }

         // Insert notifications for each admin
    foreach ($admins as $admin_id) {
        $notification_sql = "INSERT INTO notifications (user_id, event_id, message, status, created_at) 
                             VALUES (:admin_id, :event_id, :message, 'unread', NOW())";
        $notif_stmt = $pdo->prepare($notification_sql);
        $notif_stmt->execute([
            ':admin_id' => $admin_id,
            ':event_id' => $event_id,
            ':message' => "A new event proposal '$name' has been submitted and needs approval."
        ]);
    }

    $pdo->commit();
    if ($file_path !== null && isset($file_tmp)) {
    if (!move_uploaded_file($file_tmp, $file_path)) {
        throw new Exception('Failed to move uploaded file.');
    }
    }
        echo "<script>alert('Event created successfully!'); window.location.href='eventform.php';</script>";
        logActivity($pdo, $created_by, 'Create Event', "New event '$event_name' created with status: Pending.");

    }catch(PDOException $e){
        $pdo->rollBack(); 
        die("Error: " . $e->getMessage());
        
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/eventform.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
    .flatpickr-calendar {
        font-family: 'Poppins', sans-serif !important;
    }

    .flatpickr-calendar {
        font-size: 13px;
        width: 330px !important;
    }

    .flatpickr-day {
        height: 28px !important;
        line-height: 28px !important;
        width: 28px !important;
    }

    .flatpickr-innerContainer,
    .flatpickr-days {
        padding: 5px;
    }
    </style>

    <title>Event form</title>
</head>

<body>
    <nav class="nav">
        <div class="logo">
            <a href="/">
                <img src="../img/logowhite.png" alt="bee icon" width="30px" height="30px">
            </a>
        </div>
        <div class="nav-links">
            <a href="dashboard.php" class="nav-item" data-text="My Events"><i class="fas fa-calendar-check"></i>
                <p style="font-size:13px; padding-left:5px;">Events</p>
            </a>
            <a href="eventform.php" class="nav-item" data-text="Create"><i class="fas fa-plus-circle"></i>
                <p style="font-size:13px; padding-left:5px;">create</p>
            </a>
            <?php if ($_SESSION['role'] != 'facultyMember'): ?>
            <a href="tasks.php" class="nav-item" data-text="Tasks">
                <i class="fas fa-tasks"></i>
                <p style="font-size:13px; padding-left:5px;">Tasks</p>
            </a>

            <a href="resources.php" class="nav-item" data-text="Resources">
                <i class="fas fa-folder-open"></i>
                <p style="font-size:13px; padding-left:5px;">Utility</p>
            </a>
            <?php endif; ?>
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
        <div class="eventform">
            <div class="title">
                <p>Event Proposal
                    <img src="../img/logoblack.png" alt="bee icon" width="20px" height="20px">
                </p>
            </div>
            <form action="" method="POST" class="form-container" id="form" enctype="multipart/form-data">
                <div class="form-column">
                    <label for="name">Event Name:</label><br>
                    <input type="text" name="name" required> <br>
                    <label for="description">Event Description:</label><br>
                    <textarea name="description" required></textarea><br>
                    <label for="date">Event Date:</label><br>
                    <input type="text" id="date" name="date" required><br>
                    <label for="end_date">End Date:</label><br>
                    <input type="text" id="end_date" name="end_date" required><br>

                </div>
                <div class="form-column">
                    <label for="start_time">Start Time:</label><br>
                    <input type="time" name="start_time" required><br>
                    <label for="end_time">End Time:</label><br>
                    <input type="time" name="end_time" required><br>
                    <label for="category">Event type:</label><br>
                    <select name="category" required>
                        <option value="seminar">Seminar</option>
                        <option value="workshop">Workshop</option>
                        <option value="sports">Sports</option>
                        <option value="cultural">Cultural</option>
                        <option value="other">Other</option>
                    </select><br>

                    <label for="budget">proposed Budget:</label> <br>
                    <input type="number" id="budget" name="budget" min="1000" max="100000" step="100" value="50000">

                    <br>
                </div>
                <div class="form-column">
                    <label for="organiser_id">Organized_by:</label> <br>
                    <select name="organiser_id" id="organizer" required>
                        <option value="">Select Organizer</option>
                        <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id']; ?>"><?= htmlspecialchars($user['full_name']); ?> -
                            <?= htmlspecialchars($user['role']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="location_id">Event Location:</label> <br>
                    <select name="location_id" id="location_id" required>
                        <option value="">Select Location</option>
                        <?php foreach ($locations as $location): ?>
                        <option value="<?= $location['location_id']; ?>"><?= htmlspecialchars($location['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select> <br>
                    <label for="event_file">Attached files:</label><br>
                    <input type="file" name="event_file" accept="image/*"> <br>

                    <button type="submit" id="event">CREATE</button>
                </div>

            </form>
        </div>
    </div>
    <script src="script.js"></script>
    <script>
    const today = new Date();

    flatpickr("#date", {
        minDate: today, // Allow selecting today or any future date
        dateFormat: "Y-m-d",
        onChange: function(selectedDates) {
            const startDate = selectedDates[0];
            const endDatePicker = document.getElementById("end_date")._flatpickr;

            if (startDate && endDatePicker) {
                const maxEndDate = new Date(startDate);
                maxEndDate.setDate(startDate.getDate() + 7);

                endDatePicker.set("minDate", startDate);
                endDatePicker.set("maxDate", maxEndDate);
                endDatePicker.clear();
            }
        }
    });

    flatpickr("#end_date", {
        minDate: today,
        dateFormat: "Y-m-d"
    });
    </script>


</body>

</html>