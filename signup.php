<?php
include 'php/auth.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/login-signup.css">
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
            <form action="php/auth.php" method="POST" id="signupForm">
                <div class="title">
                    <a href="signup.html">Signup
                        <img src="img/logo2.png" alt="bee icon">
                    </a>
                </div>

                <div class="name">
                    <input type="text" name="firstname" placeholder="First name" style=" padding: 15px; width:180px;"
                        value="<?php echo htmlspecialchars($firstname ?? ''); ?>" required />
                    <?php if (!empty($firstnameError)): ?>
                    <span class="error-feedback"><?php echo htmlspecialchars($firstnameError); ?></span>
                    <?php endif; ?>

                    <input type="text" name="lastname" placeholder="Last name"
                        style="margin-left: 10px; padding:15px; width:180px;"
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

                <input type="password" name="password" placeholder="Password" style="width: 95%; padding:15px;"
                    required />
                <?php if (!empty($passwordError)): ?>
                <span class="error-feedback"><?php echo htmlspecialchars($passwordError); ?></span>
                <?php endif; ?>

                <input type="password" name="confirmPassword" placeholder="Confirm Password"
                    style="width: 95%; padding:15px" required />
                <?php if (!empty($confirmPasswordError)): ?>
                <span class="error-feedback"><?php echo htmlspecialchars($confirmPasswordError); ?></span>
                <?php endif; ?>
                <button type="submit" id="signup">Sign Up</button>

                <div class="register">
                    <hr>
                    <h5>or register with</h5>
                    <hr>
                </div>

                <button type="button" id="google" style="width:100%"><img src="img/googleicon.png" alt="google logo">
                    Google</button>

            </form>


        </div>
        <div class="signup_slide">
            <div class="logo">
                <a href="/">HiveFlow
                    <img src="img/logo2.png" alt="bee icon">
                </a>
            </div>
            <p> Stay organized, collaborate, and bring your events to life with ease—making every
                occasion
                a success!</p>
            <p>Already have an account?<a href="login.php">Login</a></p>

        </div>
    </div>
</body>

</html>