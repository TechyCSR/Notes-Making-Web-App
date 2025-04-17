<?php
require_once '../config/config.php';
require_once '../models/User.php';
require_once '../includes/mailer.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    try {
        $user = new User();
        $userData = $user->findByEmail($email);

        if ($userData && $user->verifyPassword($userData, $password)) {
            $_SESSION['user'] = [
                'id' => $userData['id'],
                'name' => $userData['username'],
                'email' => $userData['email']
            ];
            
            // Get user's IP address - improved detection
            $ip_address = '';
            // Check for Cloudflare
            if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
                $ip_address = $_SERVER['HTTP_CF_CONNECTING_IP'];
            }
            // Check for regular proxy
            elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                // X-Forwarded-For can contain multiple IPs, get the first one
                $ip_parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $ip_address = trim($ip_parts[0]);
            }
            // Check for other proxy headers
            elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
                $ip_address = $_SERVER['HTTP_X_REAL_IP'];
            }
            elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip_address = $_SERVER['HTTP_CLIENT_IP'];
            }
            // Default to REMOTE_ADDR if no proxy detected
            else {
                $ip_address = $_SERVER['REMOTE_ADDR'];
            }
            
            // Validate IP address
            if (!filter_var($ip_address, FILTER_VALIDATE_IP)) {
                $ip_address = $_SERVER['REMOTE_ADDR']; // Fallback to REMOTE_ADDR
            }
            
            // Set timezone to IST (Indian Standard Time)
            date_default_timezone_set('Asia/Kolkata');
            
            // Get current date and time in IST
            $login_time = date('Y-m-d H:i:s');
            
            // Send login notification email
            try {
                $mailer = new Mailer();
                $mailer->sendLoginNotification(
                    $userData['email'],
                    $userData['username'],
                    $ip_address,
                    $login_time
                );
            } catch (Exception $e) {
                // Continue login process even if email fails
                // Email failure shouldn't block user access
            }
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Invalid email or password";
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
    <title>Login | Notes App</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth/common.css">
    <link rel="stylesheet" href="../assets/css/auth/signin.css">
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
                    Notes App
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
                    <i class="fas fa-lock"></i>
                </div>
                <h2>Welcome Back</h2>
                <p class="auth-subtitle">Sign in to continue to your notes</p>
            </div>

            <?php if(isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['success'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <form class="auth-form signin-form" action="" method="post">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <button type="button" class="password-toggle">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="remember-me">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember">
                        <div class="checkbox-custom"></div>
                        <span class="checkbox-text">Remember me</span>
                    </label>
                    <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" class="btn-primary">
                    <span>Sign In</span>
                    <i class="fas fa-arrow-right"></i>
                </button>

                <div class="auth-footer">
                    Don't have an account? <a href="signup.php">Sign Up</a>
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
        const passwordToggle = document.querySelector('.password-toggle');
        const passwordInput = document.querySelector('#password');
        
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            passwordToggle.querySelector('i').classList.toggle('fa-eye');
            passwordToggle.querySelector('i').classList.toggle('fa-eye-slash');
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