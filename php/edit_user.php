<?php
require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $firstname = trim(htmlspecialchars($_POST['firstname'] ?? ''));
    $lastname = trim(htmlspecialchars($_POST['lastname'] ?? ''));
    if (!preg_match("/^[a-zA-Z\s'-]+$/", $firstname)) {
    echo "<script>alert('Invalid first name. Only letters.'); window.history.back();</script>";
    exit;
}

if (!preg_match("/^[a-zA-Z\s'-]+$/", $lastname)) {
    echo "<script>alert('Invalid last name.'); window.history.back();</script>";
    exit;
}

    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $phone = preg_replace('/\D/', '', $_POST['phone'] ?? ''); 
    $role = trim($_POST['role']);

    if (strlen($phone) !== 10) {
        echo "<script>alert('Phone number must be exactly 10 digits.'); window.history.back();</script>";
        exit;
    }

    // Check if all required fields are valid
    if ($id && $firstname && $lastname && $email && $phone && $role) {
        try {
            // Prepare the update statement
            $stmt = $pdo->prepare("
                UPDATE users SET
                    firstname = :firstname,
                    lastname = :lastname,
                    email = :email,
                    phone = :phone,
                    role = :role,
                    updated_at = NOW()
                WHERE id = :id
            ");

            // Execute with bound values
            $stmt->execute([
                ':firstname' => $firstname,
                ':lastname' => $lastname,
                ':email' => $email,
                ':phone' => $phone,
                ':role' => $role,
                ':id' => $id
            ]);

            // Redirect after successful update
            echo "<script>alert('user updated successfully'); window.location.href='edit_user.php?id=$id';</script>";
            exit;
        } catch (PDOException $e) {
            die("Error updating user: " . $e->getMessage());
        }
    } else {
        echo "<script>alert('Invalid input. Please fill all fields correctly.');</script>";
    }
} 

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("No user ID provided.");
}

$userId = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}




?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit user</title>
    <link rel="stylesheet" href="../css/edit_user.css">
</head>

<body>
    <div class="container">
        <div class="logo" style="font-family:'poppins'; font-size:20px; color:#FF6D1F; padding-bottom: 20px;">
            <strong>Edit user</strong> <img src="../img/logoblack.png" alt="bee icon" width="30px" height="30px">
        </div>
        <form method="POST" action="edit_user.php" class="form">
            <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">

            <label>First Name</label>
            <input type="text" name="firstname" value="<?= htmlspecialchars($user['firstname']) ?>" required>

            <label>Last Name</label>
            <input type="text" name="lastname" value="<?= htmlspecialchars($user['lastname']) ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>

            <label>Phone</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>

            <label>Role</label>
            <select name="role">
                <option value="studentLeader" <?= $user['role'] === 'student' ? 'selected' : '' ?>>StudentLeader
                </option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="faculty" <?= $user['role'] === 'faculty' ? 'selected' : '' ?>>Faculty</option>
            </select>

            <label>gender</label>
            <input type="text" name="gender" value="<?= htmlspecialchars($user['gender']) ?>" required>

            <label>nationality</label>
            <input type="text" name="nationality" value="<?= htmlspecialchars($user['nationality']) ?>" required>

            <label>rank</label>
            <input type="text" name="rank" value="<?= htmlspecialchars($user['rank']) ?>" required>

            <label>department</label>
            <input type="text" name="department" value="<?= htmlspecialchars($user['department']) ?>" required>


            <button type="submit">Update User</button>
        </form>
        <button class="back"><a href="adminusers.php">Back </a></button>
    </div>
</body>

</html>