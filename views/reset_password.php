<?php
require_once '../config/config.php';
require_once '../models/User.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

// Check if reset was properly initiated
if (!isset($_SESSION['reset_user_id']) || !isset($_SESSION['reset_email'])) {
    $_SESSION['error'] = "Invalid reset request. Please restart the password reset process.";
    header('Location: forgot_password.php');
    exit;
}

$userId = $_SESSION['reset_user_id'];
$email = $_SESSION['reset_email'];

// Process password reset form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate password
    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        try {
            $user = new User();
            
            if ($user->resetPassword($userId, $password)) {
                // Clear reset session variables
                unset($_SESSION['reset_user_id']);
                unset($_SESSION['reset_email']);
                
                // Set success message
                $_SESSION['success'] = "Password has been reset successfully. You can now login with your new password.";
                
                // Redirect to login page
                header('Location: login.php');
                exit;
            } else {
                $error = "Failed to reset password. Please try again.";
            }
        } catch (Exception $e) {
            $error = "System error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth/common.css">
    <link rel="stylesheet" href="../assets/css/auth/signin.css">
    <style>
        .password-requirements {
            background-color: var(--glass-bg);
            border-radius: 8px;
            padding: 12px 15px;
            margin: 10px 0 20px;
            font-size: 0.85rem;
            border: 1px solid var(--border-color);
        }
        
        .password-requirements p {
            margin: 0 0 8px 0;
            color: var(--text-color);
        }
        
        .req {
            margin: 5px 0;
            display: flex;
            align-items: center;
            color: var(--text-muted-color);
        }
        
        .req i {
            margin-right: 8px;
            font-size: 12px;
        }
        
        .req.valid {
            color: #4CAF50;
        }
        
        .req.invalid {
            color: #F44336;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="landing-navbar">
        <div class="nav-container">
            <a href="../index.php" class="nav-brand">
                <div class="logo-container">
                    <div class="logo-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <?php echo APP_NAME; ?>
                </div>
            </a>
            <button class="theme-toggle">
                <i class="fas fa-moon"></i>
            </button>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="auth-container">
        <!-- 3D Floating Elements -->
        <div class="auth-3d-elements">
            <div class="floating-element element-1"></div>
            <div class="floating-element element-2"></div>
            <div class="floating-element element-3"></div>
            <div class="floating-element element-4"></div>
        </div>

        <!-- Auth Card -->
        <div class="auth-card" data-tilt data-tilt-max="5" data-tilt-speed="400" data-tilt-perspective="500">
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="fas fa-lock-open"></i>
                </div>
                <h2>Reset Password</h2>
                <p class="auth-subtitle">Create a new secure password for your account</p>
            </div>

            <?php if(isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form class="auth-form signin-form" action="" method="post" id="resetPasswordForm">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your new password" required minlength="8">
                        <button type="button" class="password-toggle">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="password-requirements">
                    <p>Your password must:</p>
                    <div class="req" id="length-req">
                        <i class="far fa-circle"></i>
                        <span>Be at least 8 characters long</span>
                    </div>
                    <div class="req" id="letter-req">
                        <i class="far fa-circle"></i>
                        <span>Include at least one letter</span>
                    </div>
                    <div class="req" id="number-req">
                        <i class="far fa-circle"></i>
                        <span>Include at least one number</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your new password" required minlength="8">
                        <button type="button" class="password-toggle">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    <span>Reset Password</span>
                    <i class="fas fa-check-circle"></i>
                </button>

                <div class="auth-footer">
                    <a href="login.php" class="back-to-login"><i class="fas fa-arrow-left"></i> Back to Login</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="auth-footer-bar">
        <div class="footer-container">
            <div class="footer-creator">
                Made with <i class="fas fa-heart beat"></i> by <a href="https://techycsr.me" target="_blank" class="creator-link">@TechyCSR</a>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://unpkg.com/vanilla-tilt@1.7.0/dist/vanilla-tilt.min.js"></script>
    <script>
        // Initialize AOS
        AOS.init();
        
        // Handle input focus effects
        const inputGroups = document.querySelectorAll('.input-group');
        inputGroups.forEach(group => {
            const input = group.querySelector('input');
            input.addEventListener('focus', () => {
                group.classList.add('input-focus');
            });
            input.addEventListener('blur', () => {
                group.classList.remove('input-focus');
            });
        });
        
        // Password toggle
        const passwordToggles = document.querySelectorAll('.password-toggle');
        passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const passwordInput = this.parentElement.querySelector('input');
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        });
        
        // Password validation
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const lengthReq = document.getElementById('length-req');
        const letterReq = document.getElementById('letter-req');
        const numberReq = document.getElementById('number-req');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            
            // Length validation
            if (password.length >= 8) {
                lengthReq.classList.add('valid');
                lengthReq.classList.remove('invalid');
                lengthReq.querySelector('i').className = 'fas fa-check-circle';
            } else {
                lengthReq.classList.add('invalid');
                lengthReq.classList.remove('valid');
                lengthReq.querySelector('i').className = 'far fa-circle';
            }
            
            // Letter validation
            if (/[a-zA-Z]/.test(password)) {
                letterReq.classList.add('valid');
                letterReq.classList.remove('invalid');
                letterReq.querySelector('i').className = 'fas fa-check-circle';
            } else {
                letterReq.classList.add('invalid');
                letterReq.classList.remove('valid');
                letterReq.querySelector('i').className = 'far fa-circle';
            }
            
            // Number validation
            if (/\d/.test(password)) {
                numberReq.classList.add('valid');
                numberReq.classList.remove('invalid');
                numberReq.querySelector('i').className = 'fas fa-check-circle';
            } else {
                numberReq.classList.add('invalid');
                numberReq.classList.remove('valid');
                numberReq.querySelector('i').className = 'far fa-circle';
            }
        });
        
        // Check password match
        document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
        
        // 3D Tilt Effect for auth card
        VanillaTilt.init(document.querySelector(".auth-card"), {
            max: 5,
            speed: 400,
            glare: true,
            "max-glare": 0.2,
        });
        
        // Theme Toggle
        const themeToggle = document.querySelector('.theme-toggle');
        const htmlElement = document.documentElement;
        const themeIcon = themeToggle.querySelector('i');
        
        // Check for saved theme preference or use default
        const savedTheme = localStorage.getItem('theme') || 'light';
        htmlElement.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);
        
        // Toggle theme on click
        themeToggle.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            htmlElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            updateThemeIcon(newTheme);
        });
        
        function updateThemeIcon(theme) {
            if (theme === 'dark') {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            } else {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
            }
        }
    </script>
</body>
</html> 