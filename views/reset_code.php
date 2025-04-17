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

// Check if email is provided in the URL
if (!isset($_GET['email'])) {
    $_SESSION['error'] = "Invalid request. Please try the password reset again.";
    header('Location: forgot_password.php');
    exit;
}

$email = filter_input(INPUT_GET, 'email', FILTER_SANITIZE_EMAIL);

// Auto-generate reset code if arriving directly (not through POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    try {
        $user = new User();
        $userData = $user->findByEmail($email);
        
        // Check if user exists and if reset code is missing or expired
        if ($userData && (empty($userData['reset_code']) || strtotime($userData['reset_expires']) <= time())) {
            // Generate a new reset code
            $resetCode = sprintf("%04d", rand(0, 9999));
            error_log("Auto-generating new reset code for email: " . $email . ", Code: " . $resetCode);
            
            // Store the new code
            if ($user->storeResetCode($email, $resetCode)) {
                // Send the new code via email
                require_once '../includes/mailer.php';
                $mailer = new Mailer();
                
                if ($mailer->sendPasswordResetCode($email, $resetCode)) {
                    $success = "A verification code has been sent to your email.";
                    error_log("Auto-generated verification code sent successfully to: " . $email);
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error auto-generating reset code: " . $e->getMessage());
    }
}

// Verification code submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if this is a resend request
    if (isset($_POST['resend_code'])) {
        // Generate a new reset code
        $resetCode = sprintf("%04d", rand(0, 9999));
        
        try {
            $user = new User();
            
            // Debug logging to help troubleshoot
            error_log("Resending code to email: " . $email . ", New code: " . $resetCode);
            
            // Store the new code
            if ($user->storeResetCode($email, $resetCode)) {
                // Send the new code via email
                require_once '../includes/mailer.php';
                $mailer = new Mailer();
                
                if ($mailer->sendPasswordResetCode($email, $resetCode)) {
                    $success = "A new verification code has been sent to your email.";
                    error_log("New verification code sent successfully to: " . $email);
                } else {
                    $error = "Failed to send verification code. Please try again.";
                    error_log("Failed to send verification code to: " . $email);
                }
            } else {
                $error = "Failed to process reset request. Please try again.";
                error_log("Failed to store reset code for email: " . $email . ", Error: " . $user->getLastError());
            }
        } catch (Exception $e) {
            $error = "System error: " . $e->getMessage();
            error_log('Exception during resend: ' . $e->getMessage());
        }
    } 
    // Only process code verification if resend_code is NOT set
    elseif (isset($_POST['digit1']) && isset($_POST['digit2']) && isset($_POST['digit3']) && isset($_POST['digit4'])) {
        // Normal code verification
        // Get individual digits and sanitize them
        $digit1 = isset($_POST['digit1']) ? trim($_POST['digit1']) : '';
        $digit2 = isset($_POST['digit2']) ? trim($_POST['digit2']) : '';
        $digit3 = isset($_POST['digit3']) ? trim($_POST['digit3']) : '';
        $digit4 = isset($_POST['digit4']) ? trim($_POST['digit4']) : '';
        
        // Combine digits and ensure it's a valid 4-digit code
        $code = $digit1 . $digit2 . $digit3 . $digit4;
        $code = preg_replace('/\s+/', '', $code); // Remove any spaces
        
        if (strlen($code) === 4 && ctype_digit($code)) {
            try {
                $user = new User();
                
                // Debug info
                error_log("Reset code verification - Email: " . $email . ", Code entered: " . $code);
                
                // Ensure consistent formatting with leading zeros
                $code = sprintf("%04d", $code);
                
                // Use the verifyResetCode method to check the code
                $userData = $user->verifyResetCode($email, $code);
                
                if ($userData) {
                    // Success! Code is valid and not expired
                    $_SESSION['reset_user_id'] = $userData['id'];
                    $_SESSION['reset_email'] = $email;
                    
                    // Debug logging
                    error_log("Reset code verification successful, redirecting to reset_password.php");
                    
                    // Redirect to reset password page
                    header('Location: reset_password.php');
                    exit;
                } else {
                    $error = $user->getLastError() ?: "Invalid verification code. Please try again.";
                    error_log("Reset code verification failed: " . $error);
                }
            } catch (Exception $e) {
                $error = "System error: " . $e->getMessage();
                error_log("Reset code verification error: " . $e->getMessage());
            }
        } else {
            $error = "Please enter a valid 4-digit code.";
            error_log("Invalid code format: " . $code);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth/common.css">
    <link rel="stylesheet" href="../assets/css/auth/verify.css">
    <style>
        .otp-container {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin: 25px 0;
        }
        
        .otp-input {
            width: 55px;
            height: 65px;
            font-size: 28px;
            font-weight: 600;
            text-align: center;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            background-color: var(--input-bg, rgba(255, 255, 255, 0.9));
            color: var(--text-color);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            caret-color: var(--primary-color);
            margin: 0;
            padding: 0;
        }
        
        [data-theme="dark"] .otp-input {
            background-color: rgba(45, 45, 45, 0.8);
            color: #fff;
            border-color: #3a3a3a;
        }
        
        .otp-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(var(--primary-color-rgb), 0.25);
            outline: none;
            transform: translateY(-2px);
        }
        
        .verification-info {
            background-color: var(--glass-bg);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .verify-info-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            color: var(--text-color);
            font-size: 15px;
        }
        
        .verify-info-item i {
            color: var(--primary-color);
            margin-right: 12px;
            font-size: 16px;
        }
        
        .verification-email {
            font-weight: bold;
            word-break: break-all;
        }
        
        .resend-code {
            display: inline-block;
            margin-top: 15px;
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .resend-code:hover {
            text-decoration: underline;
            transform: translateY(-1px);
        }
        
        .auth-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        /* Enhanced resend button styling */
        .resend-button {
            background: none;
            border: none;
            color: var(--primary-color);
            font-size: 0.9rem;
            font-weight: 600;
            padding: 8px 12px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .resend-button i {
            margin-right: 6px;
        }
        
        .resend-button:hover {
            background-color: rgba(var(--primary-color-rgb), 0.1);
            transform: translateY(-2px);
        }
        
        .success-message {
            display: flex;
            align-items: center;
            background-color: rgba(40, 167, 69, 0.1);
            border-left: 4px solid #28a745;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 16px;
            color: var(--text-color);
        }
        
        .success-message i {
            color: #28a745;
            font-size: 18px;
            margin-right: 10px;
        }
        
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
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h2>Verify Your Email</h2>
                <p class="auth-subtitle">Enter the 4-digit code sent to your email</p>
            </div>

            <?php if(isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if(isset($success)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <div class="verification-info">
                <div class="verify-info-item">
                    <i class="fas fa-envelope"></i>
                    <span>Code sent to: <span class="verification-email"><?php echo htmlspecialchars($email); ?></span></span>
                </div>
                <div class="verify-info-item">
                    <i class="fas fa-clock"></i>
                    <span>Code expires in 30 minutes</span>
                </div>
            </div>

            <form class="auth-form verify-form" action="" method="post">
                <div class="otp-container">
                    <input type="text" name="digit1" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" required autofocus>
                    <input type="text" name="digit2" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" required>
                    <input type="text" name="digit3" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" required>
                    <input type="text" name="digit4" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" required>
                </div>

                <button type="submit" class="btn-primary" id="verifyBtn">
                    <span>Verify Code</span>
                    <i class="fas fa-check-circle"></i>
                </button>
            </form>

            <div class="auth-footer">
                <a href="forgot_password.php" class="back-to-login"><i class="fas fa-arrow-left"></i> Back</a>
                <form method="post" action="" style="display: inline;" id="resendForm">
                    <input type="hidden" name="resend_code" value="1">
                    <button type="submit" class="resend-button" id="resendBtn">
                        <i class="fas fa-redo-alt"></i> Resend Code
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <p>Sending new verification code...</p>
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
        document.querySelector('.verify-form').addEventListener('submit', function(e) {
            // Check if form is valid before showing loading
            if (this.checkValidity()) {
                document.getElementById('loadingOverlay').classList.add('active');
                document.getElementById('verifyBtn').disabled = true;
            }
        });
        
        // Resend cooldown timer setup
        let resendTimer = 0;
        const resendBtn = document.getElementById('resendBtn');
        
        // Function to start resend cooldown
        function startResendCooldown(seconds = 60) {
            resendTimer = seconds;
            resendBtn.disabled = true;
            
            const interval = setInterval(() => {
                resendTimer--;
                resendBtn.innerHTML = `<i class="fas fa-clock"></i> Resend in ${resendTimer}s`;
                
                if (resendTimer <= 0) {
                    clearInterval(interval);
                    resendBtn.disabled = false;
                    resendBtn.innerHTML = `<i class="fas fa-redo-alt"></i> Resend Code`;
                }
            }, 1000);
        }
        
        <?php if(isset($success) && strpos($success, 'verification code has been sent') !== false): ?>
        // Start cooldown when a code has been sent
        startResendCooldown();
        <?php endif; ?>
        
        // Show loading overlay when resending code
        document.getElementById('resendForm').addEventListener('submit', function(e) {
            // Prevent any validation errors from stopping the resend
            e.preventDefault();
            
            // Show loading overlay
            document.getElementById('loadingOverlay').classList.add('active');
            document.getElementById('resendBtn').disabled = true;
            
            // Submit the form manually to bypass validation
            this.submit();
        });
        
        // Handle OTP input auto-focus next field
        const otpInputs = document.querySelectorAll('.otp-input');
        
        // Clear inputs on page load and focus first input
        otpInputs.forEach((input, index) => {
            input.value = "";
            
            // Focus first input after page load
            if (index === 0) {
                setTimeout(() => {
                    input.focus();
                }, 500);
            }
            
            // Prevent spaces and non-numeric characters
            input.addEventListener('beforeinput', function(e) {
                // Prevent any non-numeric characters, especially spaces
                if (!e.data || !/^[0-9]$/.test(e.data)) {
                    e.preventDefault();
                    return false;
                }
            });
            
            // Handle input events with better sanitization
            input.addEventListener('input', function(e) {
                // Remove any non-numeric characters
                let sanitized = this.value.replace(/\D/g, '');
                
                // Only keep the first digit if multiple were somehow entered
                if (sanitized.length > 1) {
                    sanitized = sanitized.charAt(0);
                }
                
                // Set the sanitized value
                this.value = sanitized;
                
                // Move to next input if we have a value
                if (sanitized && otpInputs[index + 1]) {
                    otpInputs[index + 1].focus();
                    otpInputs[index + 1].select();
                }
            });
            
            // Backspace handling - focus previous input when current is empty
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace') {
                    if (this.value === '' && index > 0) {
                        otpInputs[index - 1].focus();
                        otpInputs[index - 1].select();
                    } else {
                        // Clear current input if it has a value
                        this.value = '';
                    }
                    e.stopPropagation(); // Stop event bubbling
                }
                
                // Prevent spaces from being entered
                if (e.key === ' ') {
                    e.preventDefault();
                    return false;
                }
            });
            
            // Select all content on focus
            input.addEventListener('focus', function() {
                setTimeout(() => this.select(), 0);
            });
            
            // Additional cleanup for any pasted content
            input.addEventListener('paste', function(e) {
                e.stopPropagation();
            });
        });
        
        // Handle paste event for the container (not individual inputs)
        document.querySelector('.otp-container').addEventListener('paste', e => {
            e.preventDefault();
            const data = e.clipboardData.getData('text');
            const digits = data.replace(/\D/g, '').split('').slice(0, 4);
            
            if (digits.length) {
                // Fill in as many inputs as we have digits
                digits.forEach((digit, i) => {
                    if (otpInputs[i]) {
                        otpInputs[i].value = digit;
                    }
                });
                
                // Focus the next empty input or the last one
                const nextIndex = Math.min(digits.length, otpInputs.length - 1);
                otpInputs[nextIndex].focus();
                otpInputs[nextIndex].select();
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