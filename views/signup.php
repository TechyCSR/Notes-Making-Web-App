<?php
require_once '../config/config.php';
require_once '../vendor/autoload.php';
require_once '../models/User.php';
require_once '../includes/mailer.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    if (empty($errors)) {
        try {
            $user = new User();
            
            // Check if email already exists
            $existingUser = $user->findByEmail($email);
            if ($existingUser) {
                $errors[] = "Email already registered";
            } else {
                // Generate OTP
                $otp = rand(1000, 9999);
                
                // Store user data in session
                $_SESSION['temp_user'] = [
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'otp' => $otp
                ];

                // Send OTP via email using Mailer class
                try {
                    $mailer = new Mailer();
                    if ($mailer->sendOTP($email, $otp)) {
                        header('Location: verify_otp.php');
                        exit;
                    } else {
                        $errors[] = "Failed to send verification email. Please try again.";
                    }
                } catch (Exception $e) {
                    $errors[] = "Email system error: " . $e->getMessage();
                }
            }
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Notes App</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth/common.css">
    <link rel="stylesheet" href="../assets/css/auth/signup.css">
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
                    <i class="fas fa-user-plus"></i>
                </div>
                <h2>Create Account</h2>
                <p class="auth-subtitle">Join us and start creating your notes</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo $error; ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form class="auth-form signup-form" action="" method="post">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" id="name" name="name" placeholder="Enter your full name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               placeholder="Create a strong password" 
                               pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$"
                               title="Password must be at least 8 characters and include uppercase, lowercase, and numbers"
                               required>
                        <button type="button" class="password-toggle">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength">Your password is too weak</div>
                    <div class="password-requirements">
                        <div class="req" data-req="length"><i class="fas fa-circle"></i> At least 8 characters</div>
                        <div class="req" data-req="uppercase"><i class="fas fa-circle"></i> Uppercase letter</div>
                        <div class="req" data-req="lowercase"><i class="fas fa-circle"></i> Lowercase letter</div>
                        <div class="req" data-req="number"><i class="fas fa-circle"></i> Number</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                        <button type="button" class="password-toggle">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary" id="submitBtn">
                    <span>Create Account</span>
                    <i class="fas fa-arrow-right"></i>
                </button>

                <div class="auth-footer">
                    Already have an account? <a href="login.php">Sign In</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <p>Sending verification code...</p>
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
        document.querySelector('.signup-form').addEventListener('submit', function(e) {
            // First check if password meets all criteria
            const password = document.getElementById('password').value;
            const lengthValid = password.length >= 8;
            const uppercaseValid = /[A-Z]/.test(password);
            const lowercaseValid = /[a-z]/.test(password);
            const numberValid = /[0-9]/.test(password);
            
            // If any criteria not met, prevent form submission
            if (!(lengthValid && uppercaseValid && lowercaseValid && numberValid)) {
                e.preventDefault();
                
                // Show error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Please create a strong password that meets all criteria.';
                
                // Remove any existing error messages first
                const existingErrors = document.querySelectorAll('.error-message');
                existingErrors.forEach(error => error.remove());
                
                // Add the error message before the form
                const form = document.querySelector('.signup-form');
                form.parentNode.insertBefore(errorDiv, form);
                
                // Scroll to error message
                errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Focus password field
                document.getElementById('password').focus();
                return;
            }
            
            // Check if form is valid before showing loading
            if (this.checkValidity()) {
                document.getElementById('loadingOverlay').classList.add('active');
                document.getElementById('submitBtn').disabled = true;
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
        
        // Password toggle
        const passwordToggles = document.querySelectorAll('.password-toggle');
        passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        });
        
        // Password strength checker
        const passwordInput = document.getElementById('password');
        const passwordStrength = document.querySelector('.password-strength');
        const requirements = document.querySelectorAll('.req');
        let passwordMeetsRequirements = false;
        
        passwordInput.addEventListener('input', function() {
            const value = this.value;
            
            if (value.length > 0) {
                passwordStrength.classList.add('active');
            } else {
                passwordStrength.classList.remove('active');
            }
            
            // Check requirements
            const lengthValid = value.length >= 8;
            const uppercaseValid = /[A-Z]/.test(value);
            const lowercaseValid = /[a-z]/.test(value);
            const numberValid = /[0-9]/.test(value);
            
            document.querySelector('[data-req="length"]').classList.toggle('valid', lengthValid);
            document.querySelector('[data-req="uppercase"]').classList.toggle('valid', uppercaseValid);
            document.querySelector('[data-req="lowercase"]').classList.toggle('valid', lowercaseValid);
            document.querySelector('[data-req="number"]').classList.toggle('valid', numberValid);
            
            // Store whether all requirements are met
            passwordMeetsRequirements = lengthValid && uppercaseValid && lowercaseValid && numberValid;
            
            // Update strength message
            if (passwordMeetsRequirements) {
                passwordStrength.textContent = 'Your password is strong';
                passwordStrength.className = 'password-strength active strong';
                this.classList.remove('invalid-password');
                this.setCustomValidity('');
            } else if ((lengthValid && uppercaseValid) || (lengthValid && lowercaseValid) || (lengthValid && numberValid)) {
                passwordStrength.textContent = 'Your password is medium strength';
                passwordStrength.className = 'password-strength active medium';
                this.classList.add('invalid-password');
                this.setCustomValidity('Password does not meet all requirements');
            } else {
                passwordStrength.textContent = 'Your password is too weak';
                passwordStrength.className = 'password-strength active weak';
                this.classList.add('invalid-password');
                this.setCustomValidity('Password does not meet all requirements');
            }
        });
        
        // Confirm password validation
        const confirmPasswordInput = document.getElementById('confirm_password');
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value !== passwordInput.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
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