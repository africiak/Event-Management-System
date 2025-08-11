<?php
include 'connection.php';

session_start();
$user_id = $_SESSION['user_id'] ?? null;

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
    <link rel="stylesheet" href="../css/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <style>
    @media screen and (max-width: 768px) {
        .container {
            padding-top: 560px;
        }
    }
    </style>
</head>

<body>
    <nav class="custom_nav">
        <div class="logo">
            <img src="../img/logowhite.png" alt="bee icon" width="30px" height="30px">
        </div>
        <div class="nav-links">
            <a href="admin.php" class="nav-item" data-text="Dashboard"><i class="fas fa-chart-line"></i>
                <p style="font-size:13px; padding-left:5px;">Home</p>
            </a>
            <a href="adminevents.php" class="nav-item" data-text="Events"><i class="fas fa-calendar-check"></i>
                <p style="font-size:13px; padding-left:5px;">Events</p>
            </a>
            <a href="adminusers.php" class="nav-item" data-text="Users"><i class="fas fa-users"></i>
                <p style="font-size:13px; padding-left:5px;">Users</p>
            </a>
            <a href="adminutil.php" class="nav-item" data-text="Resources"><i class="fas fa-cubes"></i>
                <p style="font-size:13px; padding-left:5px;">Utility</p>
            </a>
            <a href="reports.php" class="nav-item" data-text="Reports"><i class="fas fa-file-alt"></i>
                <p style="font-size:13px; padding-left:5px;">Reports</p>
            </a>
            <a href="adminlogs.php" class="nav-item" data-text="Analytics"><i class="fas fa-chart-bar"></i>
                <p style="font-size:13px; padding-left:5px;">Logs</p>
            </a>
            <a href="adminbudget.php" class="nav-item" data-text="Budget"><i class="fas fa-wallet"></i>
                <p style="font-size:13px; padding-left:5px;">Budget</p>
            </a>
            <a href="adminprofile.php" class="nav-item" data-text="Profile"><i class="fas fa-user-cog"></i>
                <p style="font-size:13px; padding-left:5px;">Profile</p>
            </a>
            <a href="adminalert.php" class="nav-item" data-text="Notifications"><i class="fas fa-bell"></i>
                <p style="font-size:13px; padding-left:5px;">Alerts</p>
            </a>
        </div>
        <div class="exit">
            <a href="logout.php" onclick="confirmLogout(event)" id="logout"> <i class="fas fa-sign-out-alt"></i>
                <p>logout</p>
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="profile-container">
            <div class="img" style=" width:100%; padding-right: 100px;">
                <div class="title">
                    <a href="">Profile
                        <img src="../img/logoblack.png" alt="bee icon" width="20px" height="20px">
                    </a>
                </div>
                <?php if (!empty($profilepic)): ?>
                <img src="<?php echo htmlspecialchars($profilepic); ?>" alt="Profile Picture"
                    style="width:160px; height:150px; border-radius:50%;">
                <?php else: ?>
                <img src="uploads/default-avatar.png" alt="Default Profile Picture"
                    style="width:150px; height:150px; border-radius:50%;">
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
            <div class="profiledata" style="width: 100%;padding-right: 100px;">
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
            <div class="profiledata" style="width: 100%; padding-right: 100px; padding-bottom:20px;">
                <p style="font-size: large; font-weight:600;">Academic Information</p>

                <label>Role:</label>
                <p> <?php echo htmlspecialchars($role ?? ''); ?></p>
                <label>Rank:</label>
                <p> <?php echo htmlspecialchars($rank ?? ''); ?></p>

                <label>Department:</label>
                <p> <?php echo htmlspecialchars($departmdiv ?? ''); ?></p>

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
    <script src="script.js"></script>

    <script>
    document.getElementById("deleteAccountBtn").addEventListener("click", function() {
        document.getElementById("deleteModal").style.display = "flex"; // Show the modal
    });

    document.getElementById("cancelBtn").addEventListener("click", function() {
        document.getElementById("deleteModal").style.display = "none"; // Hide the modal
    });
    </script>
</body>

</html>