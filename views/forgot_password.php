<?php
require_once '../config/config.php';
require_once '../vendor/autoload.php';
require_once '../models/User.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $error = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        try {
            $user = new User();
            
            // First check if the email exists
            $userData = $user->findByEmail($email);
            
            if (!$userData) {
                // Email not found - show a clear message
                $error = "This email address is not registered in our system.";
            } else {
                // Generate a reset code
                $resetCode = sprintf("%04d", rand(0, 9999));
                
                // Store the reset code in the database
                if ($user->storeResetCode($email, $resetCode)) {
                    // Send the code via email
                    require_once '../includes/mailer.php';
                    $mailer = new Mailer();
                    
                    if ($mailer->sendPasswordResetCode($email, $resetCode)) {
                        // Redirect to the verification page
                        header("Location: reset_code.php?email=" . urlencode($email));
                        exit;
                    } else {
                        $error = "Failed to send verification code. Please try again.";
                    }
                } else {
                    $error = "Failed to process password reset. Please try again.";
                }
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
    <title>Forgot Password | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth/common.css">
    <link rel="stylesheet" href="../assets/css/auth/signin.css">
    <style>
        /* Loading overlay styles */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }
        
        .loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .loading-content {
            background-color: var(--card-bg, #fff);
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            max-width: 90%;
            width: 350px;
        }
        
        .loading-content p {
            margin: 15px 0 0;
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
        }
        
        .loading-subtext {
            font-size: 14px !important;
            font-weight: 400 !important;
            color: var(--text-muted) !important;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(var(--primary-color-rgb), 0.2);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            margin: 0 auto;
            animation: spinner 1s linear infinite;
        }
        
        @keyframes spinner {
            to {
                transform: rotate(360deg);
            }
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
                    <i class="fas fa-key"></i>
                </div>
                <h2>Forgot Password?</h2>
                <p class="auth-subtitle">Enter your email to reset your password</p>
            </div>

            <?php if(isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($noUser)): ?>
                <div class="info-message">
                    <i class="fas fa-info-circle"></i>
                    If your email is registered, you will receive a password reset code.
                </div>
            <?php endif; ?>

            <form class="auth-form signin-form" action="" method="post">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your registered email" required>
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    <span>Send Reset Code</span>
                    <i class="fas fa-paper-plane"></i>
                </button>

                <div class="auth-footer">
                    <a href="login.php" class="back-to-login"><i class="fas fa-arrow-left"></i> Back to Login</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <p>Sending reset code...</p>
            <p class="loading-subtext">Please wait, this may take a moment</p>
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
        
        // Show loading overlay on form submission
        document.querySelector('.signin-form').addEventListener('submit', function(e) {
            // Check if form is valid before showing loading
            if (this.checkValidity()) {
                document.getElementById('loadingOverlay').classList.add('active');
                document.querySelector('button[type="submit"]').disabled = true;
            }
        });
        
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