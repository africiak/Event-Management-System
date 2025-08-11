<?php

include 'connection.php';
include 'logger.php';

session_start();
$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

$firstname = $lastname = $email = $phone = $role = "";

if ($user_id) {
    $sql = "SELECT firstname, lastname, email, phone, gender, nationality, rank, department, profilepic, role FROM users WHERE id = :user_id";
    $stmt = $pdo->prepare($sql);
    //bind parameter to sql query
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    //execute query
    $stmt->execute();

    
     // Fetch the data
     if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $firstname = $row['firstname'];
        $lastname = $row['lastname'];
        $email = $row['email'];
        $phone = $row['phone'];
        $role = $row['role'];
        $gender = $row['gender'];
        $nationality = $row['nationality'];
        $rank = $row['rank'];
        $department = $row['department'];
        $profilepic = $row['profilepic'];
    }

}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        // Collect and sanitize form data
        $firstname = htmlspecialchars(trim($_POST['firstname'] ?? ''));
        $lastname = htmlspecialchars(trim($_POST['lastname'] ?? ''));

        if (!preg_match("/^[a-zA-Z\s'-]+$/", $firstname)) {
    echo "<script>alert('Invalid first name. Only letters, spaces, apostrophes, and hyphens are allowed.'); window.history.back();</script>";
    exit;
}

if (!preg_match("/^[a-zA-Z\s'-]+$/", $lastname)) {
    echo "<script>alert('Invalid last name.'); window.history.back();</script>";
    exit;
}

        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $phone = preg_replace('/\D/', '', $_POST['phone'] ?? '');

        // Validate phone number
        if (strlen($phone) !== 10) {
            echo "<script>alert('Phone number must be exactly 10 digits.'); window.history.back();</script>";
            exit;
        }

        // Continue with other data
        $gender = htmlspecialchars(trim($_POST['gender'] ?? ''));
        $nationality = htmlspecialchars(trim($_POST['nationality'] ?? ''));
        $rank = htmlspecialchars(trim($_POST['rank'] ?? ''));
        $department = htmlspecialchars(trim($_POST['department'] ?? ''));
        $profilepic = null;

        // Handle profile picture upload
        if (!empty($_FILES['profile_picture']['name'])) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $filename = basename($_FILES['profile_picture']['name']);
            $profilepic = $target_dir . $filename;

            if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profilepic)) {
                echo "<script>alert('File upload failed! Please try again.');</script>";
                exit;
            }
        }

        // Prepare update query
        $query = "UPDATE users SET 
            firstname = :firstname,
            lastname = :lastname,
            email = :email,
            phone = :phone,
            gender = :gender,
            nationality = :nationality,
            rank = :rank,
            department = :department,
            profilepic = :profilepic
        WHERE id = :id";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':firstname', $firstname);
        $stmt->bindParam(':lastname', $lastname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':nationality', $nationality);
        $stmt->bindParam(':rank', $rank);
        $stmt->bindParam(':department', $department);
        $stmt->bindParam(':profilepic', $profilepic);
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);

        if ($role === 'student') {
            $redirectPage = 'student.php';
        } elseif ($role === 'admin') {
            $redirectPage = 'admin.php';
        } elseif ($role === 'lecturer') {
            $redirectPage = 'lecturer.php';
        } else {
            $redirectPage = 'login.php'; 
        }

        if ($stmt->execute()) {
            echo "<script>alert('Profile updated successfully!'); window.location.href='$redirectPage';</script>";
            logActivity($pdo, $_SESSION['user_id'], 'Profile Update', 'User updated their profile information.');

        } else {
            echo "<script>alert('Error updating profile. Please try again.');</script>";
                logActivity($pdo, $_SESSION['user_id'], 'Profile Update Failed', 'Failed attempt to update information.');

        }

    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

$redirectPage = ($_SESSION['role'] ?? '') === 'admin' ? 'adminprofile.php' : 'profile.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="../css/edit_profile.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

</head>

<body>

    <div class="container">
        <div class="signup_form">

            <!--
            <?php if (isset($connectionStatus)): ?>
            <div class="feedback <?php echo $connectionStatus === 'success' ? 'success' : 'fail'; ?>">
                <?php echo htmlspecialchars($connectionMessage); ?>
            </div>
            <?php endif; ?>
            -->

            <form action="" method="POST" id="signupForm" enctype="multipart/form-data">

                <div class="title">
                    <a href="signup.html">Edit Profile
                        <img src="../img/logowhite.png" alt="bee icon" width="20px" height="20px">
                    </a>
                </div>

                <?php if (!empty($profilepic)): ?>
                <img src="<?php echo htmlspecialchars($profilepic); ?>" alt="Profile Picture"
                    style="width:130px; height:130px; border-radius:100px; margin-left:120px; margin-bottom: 20px;" />
                <?php endif; ?>

                <div class="name">
                    <input type="text" name="firstname" placeholder="First name" style=" padding: 15px; width:150px;"
                        value="<?php echo htmlspecialchars($firstname ?? ''); ?>" required />
                    <?php if (!empty($firstnameError)): ?>
                    <span class="error-feedback"><?php echo htmlspecialchars($firstnameError); ?></span>
                    <?php endif; ?>

                    <input type="text" name="lastname" placeholder="Last name"
                        style="margin-left: 10px; padding:15px; width:150px;"
                        value="<?php echo htmlspecialchars($lastname ?? ''); ?>" required />
                    <?php if (!empty($lastnameError)): ?>
                    <span class="error-feedback"><?php echo htmlspecialchars($lastnameError); ?></span>
                    <?php endif; ?>
                </div>

                <input type="email" name="email" placeholder="Email" readonly style="width: 95%; padding:15px;"
                    value="<?php echo htmlspecialchars($email ?? ''); ?>" />
                <?php if (!empty($emailError)): ?>
                <span class="error-feedback"><?php echo htmlspecialchars($emailError); ?></span>
                <?php endif; ?>

                <input type="tel" name="phone" placeholder="Contact" style="width: 95%; padding:15px; "
                    value="<?php echo htmlspecialchars($phone ?? ''); ?>" />
                <?php if (!empty($phoneError)): ?>
                <span class="error-feedback"><?php echo htmlspecialchars($phoneError); ?></span>
                <?php endif; ?>

                <div class="name">
                    <input type="text" name="gender" placeholder="Gender" style=" padding: 15px; width:180px; "
                        value="<?php echo htmlspecialchars($gender ?? ''); ?>" required />
                    <?php if (!empty($firstnameError)): ?>
                    <span class="error-feedback"><?php echo htmlspecialchars($firstnameError); ?></span>
                    <?php endif; ?>

                    <input type="text" name="nationality" placeholder="Nationality" r
                        style="margin-left: 10px; padding:15px; width:150px;"
                        value="<?php echo htmlspecialchars($nationality ?? ''); ?>" required />
                    <?php if (!empty($lastnameError)): ?>
                    <span class="error-feedback"><?php echo htmlspecialchars($lastnameError); ?></span>
                    <?php endif; ?>
                </div>

                <input type="text" name="rank" placeholder="Rank" style="width: 95%; padding:15px;"
                    value="<?php echo htmlspecialchars($rank ?? ''); ?>" required />

                <?php if (!empty($passwordError)): ?>
                <span class="error-feedback"><?php echo htmlspecialchars($passwordError); ?></span>
                <?php endif; ?>

                <input type="text" name="department" placeholder="Department" style="width: 95%; padding:15px;"
                    value="<?php echo htmlspecialchars($department ?? ''); ?>" required />

                <?php if (!empty($passwordError)): ?>
                <span class="error-feedback"><?php echo htmlspecialchars($passwordError); ?></span>
                <?php endif; ?>



                <input type="file" name="profile_picture" id="profile_picture" accept="image/*"
                    placeholder="profile pic" style="width: 95%; padding:15px;" />

                <?php if (!empty($passwordError)): ?>
                <span class="error-feedback"><?php echo htmlspecialchars($passwordError); ?></span>
                <?php endif; ?>

                <button type="submit" id="edit" style="color: #ffffff;">Update</button>

            </form>
            <button id="edit" onclick="location.href='<?php echo $redirectPage; ?>'">Back</button>

        </div>
    </div>
    <script>
    document.getElementById("edit").addEventListener("click", function() {
        <?php if ($role == 'studentLeader'): ?>
        window.location.href = "student.php";
        <?php elseif ($role == 'faculty'): ?>
        window.location.href = "faculty.php";
        <?php elseif ($role == 'admin'): ?>
        window.location.href = "admin.php";
        <?php else: ?>
        window.location.href = "login.php"; // fallback
        <?php endif; ?>
    });
    </script>

</body>

</html>