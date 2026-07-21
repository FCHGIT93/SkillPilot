<?php
session_start();
require_once "../includes/db.php";

$message = "";
$message_type = "";

$token = $_GET["token"] ?? $_POST["token"] ?? "";

if ($token == "") {
    die("Invalid reset link. Token is missing.");
}

$stmt = $conn->prepare("
    SELECT email 
    FROM password_resets 
    WHERE token = ? 
    AND expires_at > NOW()
    LIMIT 1
");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Reset link is invalid or expired. Please request a new reset link.");
}

$reset = $result->fetch_assoc();
$email = $reset["email"];

if (isset($_POST["reset_password"])) {

    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($password == "" || $confirm_password == "") {
        $message = "Please fill all fields.";
        $message_type = "error";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "error";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
        $message_type = "error";
    } else {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update->bind_param("ss", $hashed_password, $email);

        if ($update->execute()) {

            $delete = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $delete->bind_param("s", $email);
            $delete->execute();

            $_SESSION["reset_success"] = "Password updated successfully. You can now sign in.";
            header("Location: signin.php");
            exit();

        } else {
            $message = "Database update failed: " . $conn->error;
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillPilot | Reset Password</title>

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
    <section class="reset-card large">

        <div class="reset-icon">
            <i class="fa-solid fa-lock"></i>
        </div>

        <h1>Reset Your Password</h1>
        <p class="reset-subtitle">
            Enter your new password below to securely recover your account.
        </p>

        <?php if ($message != "") { ?>
            <div class="reset-message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php } ?>

        <form method="POST" action="reset_password.php">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

            <div class="reset-group">
                <label>New Password</label>
                <div class="reset-input">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" placeholder="Enter your new password" required>
                </div>
            </div>

            <div class="reset-group">
                <label>Confirm New Password</label>
                <div class="reset-input">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="confirm_password" placeholder="Confirm your new password" required>
                </div>
            </div>

            <button type="submit" name="reset_password" class="reset-btn">
                Reset Password
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

    </section>
</main>

</body>
</html>