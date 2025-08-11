<?php

session_start();
//db connection
include 'connection.php';
include 'logging.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

if(isset($_SESSION['user_id'])){
    if ($_SESSION['role'] == 'facultyMember') {
        header("Location: lecturer.php");
        exit(); 
    } elseif ($_SESSION['role'] === 'studentLeader') {
        header("Location: dashboard.php");
        exit();
    } elseif ($_SESSION['role'] === 'admin') {
        header("Location: admin.php");
        exit();
    } 
}


// Initialize variables to store error messages
$emailError = $passwordError = $message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $minPasswordLength = 8;

    if (empty($email)) {
        $emailError = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailError = "Invalid email.";
    }

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
    } 

    //proceed if no validation errors
    if ( empty($emailError) && empty($passwordError)) {
    try{
        $sql = "SELECT id, role, password, firstname, rank FROM users WHERE email = :email AND status = 'active'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user && password_verify($password,$user['password'])){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['firstname'] = $user['firstname'];
            $_SESSION['rank'] = $user['rank'];


            
        
            logActivity($pdo, $user['id'], 'Login Success', 'User logged in successfully');

        if($user['role'] == 'facultyMember'){
            header("Location: lecturer.php");
            exit;
        }elseif($user['role'] === 'studentLeader'){
            header("Location: dashboard.php");
            exit;
        }elseif($user['role'] === 'admin'){
            header("Location: admin.php");
            exit;
        }else{
            $message ="Unable to login. Contact support";

            logActivity($pdo, $user['id'], 'Login Success', 'User logged in successfully');

        }
 
        }else{
            logActivity($pdo, null, 'Login Failure', "Failed login attempt for email: $email");

            $message = "Invalid email or password";
        }
            
        }catch(PDOException $e){
            $message = "An error occurred. Please try again later.";
            error_log("Login error: " . $e->getMessage());

            logActivity($pdo, null, 'Login Error', $e->getMessage());

        }
    }
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/login-signup.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
    <title>Login</title>
</head>

<body>
    <div class="login">
        <div class="login_slide">
            <div class="logo">
                <a href="/">HiveFlow
                    <img src="../img/logoblack.png" alt="hive" width="20px" height="20px">
                </a>
            </div>
            <p> Stay organized, collaborate, and bring your events to life with ease—making every
                occasion
                a success!</p>
            <p>Dont have an account?<a href="auth.php" id="showSignup">Signup</a></p>
        </div>
        <div class="login_form">
            <!--
            <?php if (isset($connectionStatus)): ?>
            <div class="feedback <?php echo $connectionStatus === 'success' ? 'success' : 'fail'; ?>">
                <?php echo htmlspecialchars($connectionMessage); ?>
            </div>
            <?php endif; ?>
            -->
            <form id="signupForm" method="POST" action="" style="padding-top: 100px;">
                <div class="title">
                    <a href="signup.php">Login
                        <img src="../img/logowhite.png" alt="hive" width="20px" height="20px">
                    </a>
                </div>
                <?php if (!empty($message)): ?>
                <div class="error-message" style="color: red;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>
                <input type="email" name="email" placeholder="Email" style="width: 95%; padding:15px;"
                    value="<?php echo htmlspecialchars($email ?? ''); ?>" />
                <?php if (!empty($emailError)): ?>
                <span class="error-feedback"><?php echo htmlspecialchars($emailError); ?></span>
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

                <button type="submit" id="signup">Login</button>

            </form>
        </div>
    </div>
    <script src="slide.js"></script>
    <script src="../js/toggle.js"></script>
</body>

</html>