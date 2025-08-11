<?php
include 'connection.php';



// Initialize variables to store error messages
$firstnameError = $lastnameError = $emailError = $roleError = $phoneError = $passwordError = $message = '';


// form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $firstname = trim($_POST['firstname']);
    if (empty($firstname)) {
        $firstnameError = "First name is required.";
    }

    $lastname = trim($_POST['lastname']);
    if (empty($lastname)) {
        $lastnameError = "Last name is required.";
    }

    $email = trim($_POST['email']);
    if (empty($email)) {
        $emailError = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailError = "Invalid email format.";
    }

    $role = trim($_POST['role']);
    if (empty($role)) {
        $roleError = "Role is required.";
    }
    
    $phone = trim($_POST['phone']);
    if (empty($phone)) {
        $phoneError = "Contact is required.";
    }elseif (!preg_match('/^\d{10}$/', $phone)) {
    $phoneError = "Phone number must be exactly 10 digits.";
    }elseif (!preg_match('/^0[17]\d{8}$/', $phone)) {
        $phoneError = "Phone number must start with 07 or 01 and be 10 digits long.";
    }

   
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);
    $minPasswordLength = 8;

    //password validation
    if (empty($password)) {
        $passwordError = "Password is required.";
    }elseif (strlen($password) < $minPasswordLength) {
        $passwordError = "Password must be at least $minPasswordLength characters long.";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $passwordError = "Password must contain at least one lowercase letter.";
    } elseif (!preg_match('/\d/', $password)) {
        $passwordError = "Password must contain at least one digit.";
    } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $passwordError = "Password must contain at least one special character.";
    } elseif ($password !== $confirmPassword){
        $passwordError = "Passwords do not match.";

    }

    if (empty($firstnameError) && empty($lastnameError) && empty($emailError) && 
    empty($roleError) && empty($phoneError) && empty($passwordError)) {
    
    //hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    if ($hashedPassword === false) {
        $message = "An error occurred. Please try again";
    } else {
        $message = "Signup successful! Redirecting to login page...";
         
    }


     $sql = "INSERT INTO users (firstname, lastname, email, role, phone, password) 
     VALUES (:firstname, :lastname, :email, :role, :phone, :password)";

     try {
        $stmt = $pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);
        $stmt->bindParam(':lastname', $lastname, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);


        // Execute the statement
        $stmt->execute();

        header("Location: login.php");
        exit; 
       
    } catch (PDOException $e) {
        die("<p>Error: " . htmlspecialchars($e->getMessage()) ."</p>");
    }
}
}
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/login-signup.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Signup</title>
    <style>
    .error-feedback {
        color: red;
    }

    .success {
        color: green;
    }

    .fail {
        color: red;
    }

    .slide-out {
        transform: translateX(-100%);
    }
    </style>
</head>

<body>
    <div class="signup">
        <div class="signup_form">

            <!--
            <?php if (isset($connectionStatus)): ?>
            <div class="feedback <?php echo $connectionStatus === 'success' ? 'success' : 'fail'; ?>">
                <?php echo htmlspecialchars($connectionMessage); ?>
            </div>
            <?php endif; ?>
            -->
            <form action="" method="POST" id="signupForm">
                <div class="title">
                    <a href="signup.html">Signup
                        <img src="../img/logowhite.png" alt="bee icon" width="20px" height="20px">
                    </a>
                </div>

                <div class="name">
                    <input type="text" name="firstname" placeholder="First name" style=" padding: 15px; width:180px;"
                        value="<?php echo htmlspecialchars($firstname ?? ''); ?>" required />
                    <?php if (!empty($firstnameError)): ?>
                    <span class="error-feedback"><?php echo htmlspecialchars($firstnameError); ?></span>
                    <?php endif; ?>

                    <input type="text" name="lastname" placeholder="Last name"
                        style="margin-left: 10px; padding:15px; width:190px;"
                        value="<?php echo htmlspecialchars($lastname ?? ''); ?>" required />
                    <?php if (!empty($lastnameError)): ?>
                    <span class="error-feedback"><?php echo htmlspecialchars($lastnameError); ?></span>
                    <?php endif; ?>
                </div>

                <input type="email" name="email" placeholder="Email" style="width: 95%; padding:15px;"
                    value="<?php echo htmlspecialchars($email ?? ''); ?>" />
                <?php if (!empty($emailError)): ?>
                <span class="error-feedback"><?php echo htmlspecialchars($emailError); ?></span>
                <?php endif; ?>

                <div id="roleSelection">
                    <label for="StudentLeader" class="radio-label">
                        <input type="radio" name="role" value="studentLeader"
                            value="<?php echo htmlspecialchars($role ?? ''); ?>" required />
                        Student
                    </label>
                    <label for="facultyMember" class="radio-label">
                        <input type="radio" name="role" value="facultyMember"
                            value="<?php echo htmlspecialchars($role ?? ''); ?>" required />
                        Faculty
                    </label>
                    <?php if (!empty($roleError)): ?>
                    <span class="error-feedback"><?php echo htmlspecialchars($roleError); ?></span>
                    <?php endif; ?>
                </div>

                <input type="tel" name="phone" placeholder="Contact" style="width: 95%; padding:15px; "
                    value="<?php echo htmlspecialchars($phone ?? ''); ?>" />
                <?php if (!empty($phoneError)): ?>
                <span class="error-feedback"><?php echo htmlspecialchars($phoneError); ?></span>
                <?php endif; ?>

                <div style="position: relative; ">
                    <input type="password" name="password" id="password" placeholder="Password"
                        style="width: 95%; padding:15px;" required />
                    <i class="fa-solid fa-eye-slash" id="togglePassword"
                        style="position: absolute; right: 30px; top: 50%; cursor: pointer; color: #fff;"></i>

                </div>
                <?php if (!empty($passwordError)): ?>
                <span class="error-feedback"><?php echo htmlspecialchars($passwordError); ?></span>
                <?php endif; ?>
                <div style="position: relative;">
                    <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm Password"
                        style="width: 95%; padding:15px" required />
                    <i class="fa-solid fa-eye-slash" id="toggleConfirm"
                        style="position: absolute; right: 30px; top: 50%; cursor: pointer; color: #fff;"></i>
                </div>
                <?php if (!empty($confirmPasswordError)): ?>
                <span class="error-feedback"><?php echo htmlspecialchars($confirmPasswordError); ?></span>
                <?php endif; ?>
                <button type="submit" id="signup">Sign Up</button>

            </form>


        </div>
        <div class="signup_slide">
            <div class="logo">
                <a href="/">HiveFlow
                    <img src="../img/logoblack.png" alt="bee icon" width="20px" height="20px">
                </a>
            </div>
            <p> Stay organized, collaborate, and bring your events to life with ease—making every
                occasion
                a success!</p>
            <p>Already have an account?<a href="login.php" id="showLogin">Login</a></p>
        </div>
    </div>
    <script src="slide.js"></script>
    <script src="../js/toggle.js"></script>


</body>

</html>