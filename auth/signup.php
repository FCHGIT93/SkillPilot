<?php
include("../includes/db.php");

$message = "";

if (isset($_POST['signup'])) {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $major = trim($_POST['major']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
    } else {
        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $message = "Email already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare($conn, "INSERT INTO users(fullname, email, password, major) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $fullname, $email, $hashed_password, $major);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: signin.php");
                exit();
            } else {
                $message = "Error creating account.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillPilot | Sign Up</title>
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>

<div class="signup-page">

    <div class="signup-card-clean">

        <div class="brand-small">
            <i class="fa-solid fa-graduation-cap"></i>
            <span>Skill<span>Pilot</span></span>
        </div>

        <div class="icon-circle">
            <i class="fa-solid fa-user-plus"></i>
        </div>

        <h2>Create Your Account</h2>
        <p class="subtitle">Start your career journey with SkillPilot</p>

        <?php if ($message != "") { ?>
            <div class="message"><?php echo $message; ?></div>
        <?php } ?>

        <form method="POST">

            <div class="form-grid">

                <div>
                    <label>Full Name</label>
                    <div class="input-box">
                        <i class="fa-regular fa-user"></i>
                        <input type="text" name="fullname" placeholder="Enter your full name" required>
                    </div>
                </div>

                <div>
                    <label>Email Address</label>
                    <div class="input-box">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>

                <div>
                    <label>Major / Field of Study</label>
                    <div class="input-box">
                        <i class="fa-solid fa-book"></i>
                        <input type="text" name="major" placeholder="Enter your major" required>
                    </div>
                </div>

                <div>
                    <label>Password</label>
                    <div class="input-box">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" placeholder="Create a strong password" required>
                    </div>
                </div>

                <div>
                    <label>Confirm Password</label>
                    <div class="input-box">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="confirm_password" placeholder="Confirm your password" required>
                    </div>
                </div>

                <div class="password-rules">
                    <p>Password must contain:</p>
                    <span><i class="fa-solid fa-check"></i> At least 8 characters</span>
                    <span><i class="fa-solid fa-check"></i> One uppercase letter</span>
                    <span><i class="fa-solid fa-check"></i> One number</span>
                    <span><i class="fa-solid fa-check"></i> One special character</span>
                </div>

            </div>

            <button type="submit" name="signup">Sign Up <i class="fa-solid fa-arrow-right"></i></button>

            <p class="switch-link">Already have an account? <a href="signin.php">Sign in</a></p>

        </form>

    </div>

</div>

</body>
</html>