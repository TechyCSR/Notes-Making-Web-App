<?php
require_once '../config/config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $mongo = new MongoDB\Client(MONGODB_URI);
        $db = $mongo->selectDatabase(MONGODB_DB);
        $users = $db->users;

        if (isset($_POST['update_profile'])) {
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

            // Check if email is being changed
            if ($email !== $_SESSION['user']['email']) {
                // Generate OTP for email verification
                $otp = rand(1000, 9999);
                $_SESSION['temp_email'] = [
                    'email' => $email,
                    'otp' => $otp
                ];

                // Send OTP via email
                require '../vendor/autoload.php';
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host = SMTP_HOST;
                    $mail->SMTPAuth = true;
                    $mail->Username = SMTP_EMAIL;
                    $mail->Password = SMTP_PASSWORD;
                    $mail->SMTPSecure = SMTP_SECURE;
                    $mail->Port = SMTP_PORT;

                    $mail->setFrom(SMTP_EMAIL, APP_NAME);
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = 'Email Verification - ' . APP_NAME;
                    $mail->Body = "Your OTP for email verification is: <b>{$otp}</b>";

                    $mail->send();
                    $_SESSION['verify_email'] = true;
                    header('Location: verify_email.php');
                    exit;
                } catch (Exception $e) {
                    $error = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                // Update name only
                $users->updateOne(
                    ['_id' => new MongoDB\BSON\ObjectId($_SESSION['user']['id'])],
                    ['$set' => [
                        'name' => $name,
                        'updated_at' => new MongoDB\BSON\UTCDateTime()
                    ]]
                );
                $_SESSION['user']['name'] = $name;
                $success = "Profile updated successfully!";
            }
        } elseif (isset($_POST['change_password'])) {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            $user = $users->findOne(['_id' => new MongoDB\BSON\ObjectId($_SESSION['user']['id'])]);

            if (password_verify($current_password, $user->password)) {
                if ($new_password === $confirm_password) {
                    $users->updateOne(
                        ['_id' => new MongoDB\BSON\ObjectId($_SESSION['user']['id'])],
                        ['$set' => [
                            'password' => password_hash($new_password, PASSWORD_DEFAULT),
                            'updated_at' => new MongoDB\BSON\UTCDateTime()
                        ]]
                    );
                    $success = "Password changed successfully!";
                } else {
                    $error = "New passwords do not match!";
                }
            } else {
                $error = "Current password is incorrect!";
            }
        }
    } catch (Exception $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="dashboard-body">
    <nav class="navbar">
        <a href="dashboard.php" class="nav-brand"><?php echo APP_NAME; ?></a>
        <div class="nav-links">
            <button class="theme-toggle" title="Toggle theme">
                <i class="fas fa-moon"></i>
            </button>
            <span class="user-name">Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?></span>
            <a href="logout.php" class="nav-btn">Logout</a>
        </div>
    </nav>

    <div class="profile-container">
        <div class="profile-card">
            <h2>Profile Settings</h2>
            <?php if (isset($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Profile Information -->
            <form method="POST" class="profile-form">
                <h3>Personal Information</h3>
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_SESSION['user']['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['user']['email']); ?>" required>
                </div>
                <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
            </form>

            <!-- Change Password -->
            <form method="POST" class="profile-form">
                <h3>Change Password</h3>
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html> 