<?php
require_once '../config/config.php';
require_once '../includes/rate_limiter.php';
require_once '../models/User.php';
session_start();

if (!isset($_SESSION['temp_user'])) {
    header('Location: signup.php');
    exit;
}

$rateLimiter = new RateLimiter();
$ipAddress = $_SERVER['REMOTE_ADDR'];
$endpoint = 'verify_otp';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify_otp'])) {
        if (!$rateLimiter->checkLimit($ipAddress, $endpoint)) {
            $timeToReset = $rateLimiter->getTimeToReset($ipAddress, $endpoint);
            $error = "Too many attempts. Please try again in " . ceil($timeToReset / 60) . " minutes.";
        } else {
            $entered_otp = trim($_POST['otp']);
            $stored_otp = $_SESSION['temp_user']['otp'];

            if ($entered_otp === (string)$stored_otp) {
                try {
                    $user = new User();
                    
                    // Check if email already exists again (double check)
                    $existingUser = $user->findByEmail($_SESSION['temp_user']['email']);
                    if ($existingUser) {
                        $error = "Email already registered. Please use a different email.";
                    } else {
                        // Create new user
                        $userId = $user->create(
                            $_SESSION['temp_user']['name'],
                            $_SESSION['temp_user']['email'],
                            $_SESSION['temp_user']['password']
                        );

                        if ($userId) {
                            // Clear temporary session data
                            unset($_SESSION['temp_user']);
                            $_SESSION['success'] = "Registration successful! Please login to continue.";
                            $rateLimiter->resetLimit($ipAddress, $endpoint);
                            header('Location: login.php');
                            exit;
                        } else {
                            $error = "Failed to create account: " . $user->getLastError();
                        }
                    }
                } catch (Exception $e) {
                    $error = "Database error: " . $e->getMessage();
                }
            } else {
                try {
                    // Try to increment the rate limiter
                    $rateLimiter->increment($ipAddress, $endpoint);
                    $remainingAttempts = $rateLimiter->getRemainingAttempts($ipAddress, $endpoint);
                    $error = "Invalid verification code. " . $remainingAttempts . " attempts remaining.";
                } catch (Error $e) {
                    // If increment method doesn't exist, just show error message
                    $error = "Invalid verification code. Please try again.";
                }
            }
        }
    } elseif (isset($_POST['resend_otp'])) {
        // Generate new OTP
        $new_otp = rand(1000, 9999);
        $_SESSION['temp_user']['otp'] = $new_otp;
        
        try {
            require_once '../includes/mailer.php';
            $mailer = new Mailer();
            if ($mailer->sendOTP($_SESSION['temp_user']['email'], $new_otp)) {
                $_SESSION['success'] = "New verification code sent to your email.";
                $rateLimiter->resetLimit($ipAddress, $endpoint);
                header('Location: verify_otp.php');
                exit;
            } else {
                $error = "Failed to send new verification code. Please try again.";
            }
        } catch (Exception $e) {
            $error = "Email system error: " . $e->getMessage();
        }
    }
}

$remainingAttempts = $rateLimiter->getRemainingAttempts($ipAddress, $endpoint);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email | Notes App</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth/common.css">
    <link rel="stylesheet" href="../assets/css/auth/verify.css">
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
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <h2>Verify Your Email</h2>
                <p class="auth-subtitle">Enter the code sent to your email</p>
            </div>

            <?php if(isset($error) && !empty($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['success'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['rate_limited'])): ?>
                <div class="rate-limit-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <?php echo $_SESSION['rate_limited']; unset($_SESSION['rate_limited']); ?>
                        <div>Please try again in <span class="timer" id="timer">60</span> seconds.</div>
                    </div>
                </div>
            <?php endif; ?>

            <form class="auth-form verify-form" action="" method="post">
                <div class="otp-container">
                    <input type="text" 
                           class="otp-input" 
                           id="otp" 
                           name="otp" 
                           pattern="[0-9]{4}" 
                           title="Please enter a 4-digit code" 
                           maxlength="4" 
                           autocomplete="one-time-code" 
                           inputmode="numeric" 
                           required
                           autofocus>
                </div>

                <div class="attempts-remaining">
                    <i class="fas fa-info-circle"></i>
                    <span>Attempts remaining: <strong><?php echo $remainingAttempts; ?></strong></span>
                </div>

                <button type="submit" class="btn-primary" id="verifyButton" name="verify_otp">
                    <span>Verify Code</span>
                    <i class="fas fa-check-circle"></i>
                </button>

                <div class="verification-info">
                    <div class="verify-info-item">
                        <i class="fas fa-envelope"></i>
                        <span>We've sent a 4-digit verification code to <strong><?php echo htmlspecialchars($_SESSION['temp_user']['email']); ?></strong></span>
                    </div>
                    <div class="verify-info-item">
                        <i class="fas fa-clock"></i>
                        <span>The code is valid for 10 minutes.</span>
                    </div>
                    <div class="verify-info-item">
                        <i class="fas fa-redo-alt"></i>
                        <span>Didn't receive the code? 
                            <a href="signup.php" class="resend-link" id="resendLink">Resend Code</a>
                        </span>
                    </div>
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
        
        // OTP Input Focus
        const otpInput = document.getElementById('otp');
        otpInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length === 4) {
                document.getElementById('verifyButton').classList.add('ready');
                this.classList.add('has-value');
            } else {
                document.getElementById('verifyButton').classList.remove('ready');
                this.classList.remove('has-value');
            }
        });
        
        // Focus on OTP input on page load
        window.addEventListener('load', function() {
            otpInput.focus();
        });
        
        // 3D Tilt Effect for auth card
        VanillaTilt.init(document.querySelector(".auth-card"), {
            max: 5,
            speed: 400,
            glare: true,
            "max-glare": 0.2,
        });
        
        // Timer for resend code
        if(document.getElementById('timer')) {
            let timeLeft = 60;
            const timerElement = document.getElementById('timer');
            const resendLink = document.getElementById('resendLink');
            resendLink.style.pointerEvents = 'none';
            resendLink.style.opacity = '0.5';
            
            const timer = setInterval(function() {
                timeLeft--;
                timerElement.textContent = timeLeft;
                
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    resendLink.style.pointerEvents = 'auto';
                    resendLink.style.opacity = '1';
                    timerElement.parentElement.textContent = 'You can now resend the code.';
                }
            }, 1000);
        }
        
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