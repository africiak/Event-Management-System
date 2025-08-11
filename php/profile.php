<?php
include 'connection.php';

session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'admin') {
    header("Location: unauthorized.php");
    exit();
}


$firstname = $lastname = $email = $phone = $role =  $gender = $nationality = $rank = $department = $profilepic = "";

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


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="../css/profile.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

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
        <!--
        <?php if (isset($connectionStatus)): ?>
        <div class="feedback <?php echo $connectionStatus === 'success' ? 'success' : 'fail'; ?>">
            <?php echo htmlspecialchars($connectionMessage); ?>
        </div>
        <?php endif; ?>
        -->
        <div class="profile-container">
            <div class="img" style=" width:100%; padding-right: 100px; padding-left: 100px;">
                <div class="title">
                    <a href="signup.html">Profile
                        <img src="../img/logoblack.png" alt="bee icon" width="20px" height="20px">
                    </a>
                </div>
                <?php if (!empty($profilepic)): ?>
                <img src="<?php echo htmlspecialchars($profilepic); ?>" alt="Profile Picture"
                    style="width:160px; height:150px;  border-radius:50%; object-fit:cover;">
                <?php else: ?>
                <img src="uploads/default-avatar.png" alt="Default Profile Picture"
                    style="width:150px; height:150px; border-radius:50%; object-fit:cover;">
                <?php endif; ?>
                <div class="buttons">
                    <a href="../php/edit_profile.php" id="edit">
                        Edit
                    </a>
                    <button type="button" id="deleteAccountBtn">
                        Delete
                    </button>
                </div>

            </div>
            <div class="profiledata" style="width: 100%;padding-right: 100px; padding-left: 50px;">
                <p style="font-size: large; font-weight:600;">Personal Information</p>

                <label>First Name:</label>
                <p><?php echo htmlspecialchars($firstname ?? ''); ?></p>
                <label>Last Name:</label>
                <p> <?php echo htmlspecialchars($lastname ?? ''); ?></p>

                <label>Email:</label>
                <p> <?php echo htmlspecialchars($email ?? ''); ?></p>
                <label>Gender:</label>
                <p> <?php echo htmlspecialchars($gender ?? ''); ?></p>
                <label>Contact:</label>
                <p> <?php echo htmlspecialchars($phone ?? ''); ?></p>

            </div>
            <div class="profiledata"
                style="width: 100%; padding-right: 100px; padding-bottom:20px; padding-left: 50px;">
                <p style="font-size: large; font-weight:600;">Academic Information</p>

                <label>Role:</label>
                <p> <?php echo htmlspecialchars($role ?? ''); ?></p>
                <label>Rank:</label>
                <p> <?php echo htmlspecialchars($rank ?? ''); ?></p>

                <label>Department:</label>
                <p> <?php echo htmlspecialchars($department ?? ''); ?></p>

                <label>Nationality:</label>
                <p> <?php echo htmlspecialchars($nationality ?? ''); ?></p>

            </div>

        </div>

    </div>
    <div id="deleteModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center;">
        <div style="background: white; padding: 20px; border-radius: 10px; text-align: center; font-family:'poppins';">
            <p>Are you sure you want to delete your account? <br>
                This action is permanent.</p>
            <form action="delete.php" method="POST">
                <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                <button type="submit" name="confirm_delete"
                    style="background-color: red; color: white; padding: 10px;">Yes,
                    Delete</button>
                <button type="button" id="cancelBtn"
                    style="background-color: gray; color: white; padding: 10px;">Cancel</button>
            </form>
        </div>
    </div>
    <script>
    document.getElementById("deleteAccountBtn").addEventListener("click", function() {
        document.getElementById("deleteModal").style.display = "flex";
    });

    document.getElementById("cancelBtn").addEventListener("click", function() {
        document.getElementById("deleteModal").style.display = "none";
    });
    </script>
    <script src="script.js"></script>

</body>

</html>