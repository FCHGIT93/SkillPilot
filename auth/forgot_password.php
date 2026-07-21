<?php
session_start();

require_once "../includes/db.php";
require_once "../includes/mail_config.php";

$message = "";
$message_type = "";

if (isset($_GET["expired"])) {
    $message = "This reset link is invalid or expired. Please request a new one.";
    $message_type = "error";
}

if (isset($_POST["send_reset_link"])) {
    $email = trim($_POST["email"]);

    if ($email == "") {
        $message = "Please enter your email address.";
        $message_type = "error";
    } else {
        $stmt = $conn->prepare("SELECT id, fullname FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            $token = bin2hex(random_bytes(32));
            $expires_at = date("Y-m-d H:i:s", strtotime("+30 minutes"));

            $delete = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $delete->bind_param("s", $email);
            $delete->execute();

           $insert = $conn->prepare("
           INSERT INTO password_resets (email, token, expires_at)
           VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 2 HOUR))
        ");
            $insert->bind_param("ss", $email, $token);
            $insert->execute();
            $resetLink = "http://localhost/SkillPilot/auth/reset_password.php?token=" . urlencode($token);

            try {
                $mail = getMailer();
                $mail->addAddress($email, $user["fullname"]);
                $mail->isHTML(true);
                $mail->Subject = "Reset your SkillPilot password";

                $mail->Body = "
                    <div style='font-family:Arial;background:#f4f8ff;padding:30px;'>
                        <div style='max-width:560px;margin:auto;background:white;border-radius:18px;padding:30px;border:1px solid #dbeafe;'>
                            <h2 style='color:#0f172a;'>Reset your SkillPilot password</h2>
                            <p style='color:#475569;line-height:1.7;'>
                                Hi " . htmlspecialchars($user["fullname"]) . ",<br><br>
                                Click the button below to reset your password.
                            </p>
                            <a href='$resetLink'
                               style='display:inline-block;background:#2563eb;color:white;text-decoration:none;padding:14px 22px;border-radius:12px;font-weight:bold;'>
                                Reset Password
                            </a>
                            <p style='color:#64748b;margin-top:18px;'>This link expires in 30 minutes.</p>
                        </div>
                    </div>
                ";

                $mail->send();
            } catch (Exception $e) {
                $message = "Email could not be sent. Please try again.";
                $message_type = "error";
            }
        }

        if ($message == "") {
            $message = "If this email exists, a password reset link has been sent.";
            $message_type = "success";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillPilot | Forgot Password</title>

    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/reset_password.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="reset-body">

<header class="reset-topbar">
    <div class="reset-brand">
        <i class="fa-solid fa-graduation-cap"></i>
        <div>
            <h2>SkillPilot</h2>
            <p>AI Career Coach</p>
        </div>
    </div>

    <a href="signin.php">
        <i class="fa-solid fa-arrow-left"></i>
        Back to Sign In
    </a>
</header>

<main class="reset-wrapper">
    <section class="reset-card">

        <div class="reset-icon">
            <i class="fa-solid fa-key"></i>
        </div>

        <h1>Forgot Password?</h1>
        <p class="reset-subtitle">
            Enter your account email and we will send you a secure reset link.
        </p>

        <?php if ($message != "") { ?>
            <div class="reset-message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php } ?>

        <form method="POST">
            <div class="reset-group">
                <label>Email Address</label>
                <div class="reset-input">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" placeholder="Enter your email address" required>
                </div>
            </div>

            <button type="submit" name="send_reset_link" class="reset-btn">
                Send Reset Link
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <p class="reset-switch">
            Remember your password?
            <a href="signin.php">Back to Sign In</a>
        </p>

    </section>
</main>

</body>
</html>