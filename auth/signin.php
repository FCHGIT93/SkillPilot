<?php
include("../includes/db.php");
session_start();

$message = "";
if (isset($_SESSION["reset_success"])) {
    $message = $_SESSION["reset_success"];
    $message_type = "success";
    unset($_SESSION["reset_success"]);
}

if (isset($_POST['signin'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT id, fullname, password FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            header("Location: ../pages/dashboard.php");
            exit();
        } else {
            $message = "Incorrect password.";
        }
    } else {
        $message = "No account found with this email.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillPilot | Sign In</title>
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="auth-bg">

    <div class="auth-form-area">
        <div class="auth-card signin-card">

            <h2>Welcome Back!</h2>
            <p class="subtitle">Sign in to continue to SkillPilot</p>

            <?php if ($message != "") { ?>
                <div class="message"><?php echo $message; ?></div>
            <?php } ?>

            <form method="POST">

                <label>Email Address</label>
                <div class="input-box">
                    <i class="fa-regular fa-envelope"></i>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>

                <label>Password</label>
                <div class="input-box">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>

               <a href="forgot_password.php">Forgot Password?</a>

                <button type="submit" name="signin">Sign In <i class="fa-solid fa-arrow-right"></i></button>

                <div class="divider">
                    <span></span>
                    <p>Or continue with</p>
                    <span></span>
                </div>

                <div class="social-buttons">
                    <button type="button" class="social" onclick="alert('Google sign in will be connected later using OAuth.');">
                        <i class="fa-brands fa-google"></i> Google
                    </button>

                    <button type="button" class="social" onclick="alert('LinkedIn sign in will be connected later using OAuth.');">
                        <i class="fa-brands fa-linkedin"></i> LinkedIn
                    </button>
                </div>

                <p class="switch-link">Don't have an account? <a href="signup.php">Sign up</a></p>

            </form>
        </div>
    </div>

</div>

</body>
</html>