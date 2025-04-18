<?php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/auth/common.css">
    <style>
        /* Modern 3D Effects and Animation */
        .error-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            perspective: 1200px;
            min-height: calc(100vh - 70px - 60px); /* Subtract header and footer heights */
            position: relative;
            overflow: hidden;
        }
        
        .error-scene {
            position: relative;
            width: 100%;
            max-width: 1200px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            transform-style: preserve-3d;
        }
        
        .error-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 30px;
            padding: 4rem;
            width: 100%;
            max-width: 600px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 10;
            transform-style: preserve-3d;
            transform: translateZ(20px);
            animation: card-float 6s ease-in-out infinite;
        }
        
        [data-theme="dark"] .error-card {
            background: rgba(30, 30, 30, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.3);
        }
        
        .error-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
            background: radial-gradient(circle at center, rgba(74, 144, 226, 0.1) 0%, rgba(0, 0, 0, 0) 70%);
        }
        
        [data-theme="dark"] .error-bg {
            background: radial-gradient(circle at center, rgba(74, 144, 226, 0.15) 0%, rgba(0, 0, 0, 0) 70%);
        }
        
        .error-code {
            font-size: 180px;
            font-weight: 900;
            background: linear-gradient(135deg, #4a90e2, #833ab4, #fd1d1d, #fcb045);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
            margin: 0 0 20px;
            text-shadow: 0 10px 20px rgba(0,0,0,0.1);
            position: relative;
            transform-style: preserve-3d;
            transform: translateZ(40px);
            animation: float 6s ease-in-out infinite alternate;
        }
        
        .error-code::after {
            content: '404';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #4a90e2, #833ab4, #fd1d1d, #fcb045);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: blur(30px);
            opacity: 0.5;
            z-index: -1;
        }
        
        .error-message {
            color: var(--auth-text-dark);
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 20px;
            transform: translateZ(30px);
            text-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .error-subtitle {
            color: var(--auth-text-muted);
            font-size: 16px;
            max-width: 80%;
            margin: 0 auto 40px;
            transform: translateZ(20px);
            line-height: 1.6;
        }
        
        .back-home {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #4a90e2, #833ab4);
            color: white;
            border: none;
            border-radius: 15px;
            padding: 16px 32px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 10px 25px rgba(74, 144, 226, 0.5);
            position: relative;
            overflow: hidden;
            margin-top: 20px;
            transform: translateZ(25px);
        }
        
        .back-home span {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .back-home::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.8s ease;
        }
        
        .back-home:hover {
            transform: translateY(-5px) translateZ(25px);
            box-shadow: 0 15px 35px rgba(74, 144, 226, 0.6);
        }
        
        .back-home:hover::before {
            left: 100%;
        }
        
        /* 3D Objects */
        .object-3d {
            position: absolute;
            transform-style: preserve-3d;
            pointer-events: none;
        }
        
        .cube {
            width: 60px;
            height: 60px;
            animation: rotate3D 20s linear infinite;
            transform-style: preserve-3d;
        }
        
        .cube-face {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 2px solid rgba(74, 144, 226, 0.5);
            background: rgba(74, 144, 226, 0.1);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }
        
        .cube-front  { transform: translateZ(30px); }
        .cube-back   { transform: rotateY(180deg) translateZ(30px); }
        .cube-right  { transform: rotateY(90deg) translateZ(30px); }
        .cube-left   { transform: rotateY(-90deg) translateZ(30px); }
        .cube-top    { transform: rotateX(90deg) translateZ(30px); }
        .cube-bottom { transform: rotateX(-90deg) translateZ(30px); }
        
        .cube-1 {
            top: 20%;
            left: 10%;
            animation-duration: 25s;
        }
        
        .cube-2 {
            bottom: 15%;
            right: 15%;
            animation-duration: 30s;
            animation-delay: -5s;
            transform: scale(1.5);
        }
        
        .cube-3 {
            top: 15%;
            right: 10%;
            animation-duration: 20s;
            animation-delay: -10s;
            transform: scale(0.8);
        }
        
        .sphere {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.8), rgba(74, 144, 226, 0.5));
            box-shadow: 0 0 30px rgba(74, 144, 226, 0.5);
            animation: float 8s ease-in-out infinite alternate;
        }
        
        .sphere-1 {
            bottom: 20%;
            left: 15%;
            animation-duration: 12s;
        }
        
        .sphere-2 {
            top: 25%;
            right: 20%;
            transform: scale(0.6);
            animation-duration: 10s;
            animation-delay: -3s;
        }
        
        /* Particle Effects */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }
        
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(74, 144, 226, 0.5);
            border-radius: 50%;
            animation: particle-float 10s linear infinite;
        }
        
        @keyframes particle-float {
            0% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-800px) translateX(var(--tx));
                opacity: 0;
            }
        }
        
        /* Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0) translateZ(40px); }
            50% { transform: translateY(-20px) translateZ(40px); }
        }
        
        @keyframes card-float {
            0%, 100% { transform: translateZ(20px) rotateX(0deg) rotateY(0deg); }
            25% { transform: translateZ(20px) rotateX(2deg) rotateY(-2deg); }
            50% { transform: translateZ(20px) rotateX(0deg) rotateY(3deg); }
            75% { transform: translateZ(20px) rotateX(-2deg) rotateY(1deg); }
        }
        
        @keyframes rotate3D {
            0% { transform: rotateX(0deg) rotateY(0deg) rotateZ(0deg); }
            100% { transform: rotateX(360deg) rotateY(360deg) rotateZ(360deg); }
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .error-card {
                padding: 3rem 2rem;
                max-width: 90%;
            }
            
            .error-code {
                font-size: 120px;
            }
            
            .error-message {
                font-size: 28px;
            }
            
            .cube-1, .cube-3, .sphere-2 {
                display: none;
            }
        }
        
        @media (max-width: 480px) {
            .error-code {
                font-size: 90px;
            }
            
            .error-message {
                font-size: 24px;
            }
            
            .object-3d {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="landing-navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">
                <div class="logo-container">
                    <div class="logo-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <?php echo APP_NAME; ?>
                </div>
            </a>
            <button class="theme-toggle" id="themeToggle">
                <i class="fas fa-moon"></i>
            </button>
        </div>
    </nav>

    <!-- Main Content - 3D Error Scene -->
    <div class="error-container">
        <div class="error-bg"></div>
        
        <!-- 3D Objects -->
        <div class="object-3d cube cube-1">
            <div class="cube-face cube-front"></div>
            <div class="cube-face cube-back"></div>
            <div class="cube-face cube-right"></div>
            <div class="cube-face cube-left"></div>
            <div class="cube-face cube-top"></div>
            <div class="cube-face cube-bottom"></div>
        </div>
        
        <div class="object-3d cube cube-2">
            <div class="cube-face cube-front"></div>
            <div class="cube-face cube-back"></div>
            <div class="cube-face cube-right"></div>
            <div class="cube-face cube-left"></div>
            <div class="cube-face cube-top"></div>
            <div class="cube-face cube-bottom"></div>
        </div>
        
        <div class="object-3d cube cube-3">
            <div class="cube-face cube-front"></div>
            <div class="cube-face cube-back"></div>
            <div class="cube-face cube-right"></div>
            <div class="cube-face cube-left"></div>
            <div class="cube-face cube-top"></div>
            <div class="cube-face cube-bottom"></div>
        </div>
        
        <div class="object-3d sphere sphere-1"></div>
        <div class="object-3d sphere sphere-2"></div>
        
        <!-- Particles -->
        <div class="particles" id="particles"></div>
        
        <!-- Error Card -->
        <div class="error-scene">
            <div class="error-card" data-aos="zoom-in" data-aos-duration="800">
                <div class="error-code">404</div>
                <h2 class="error-message">Page Not Found</h2>
                <p class="error-subtitle">The page you're looking for doesn't exist or has been moved to another location.</p>
                <a href="/index.php" class="back-home">
                    <span>
                        <i class="fas fa-home"></i>
                        Return to Home
                    </span>
                </a>
            </div>
        </div>
    </div>

    <!-- Footer - Keep the same -->
    <footer class="auth-footer-bar">
        <div class="footer-container">
            <div class="footer-creator">
                Made with <i class="fas fa-heart beat"></i> by <a href="https://techycsr.me" target="_blank" class="creator-link">@TechyCSR</a>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true
        });
        
        // Theme Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('themeToggle');
            const htmlElement = document.documentElement;
            const themeIcon = themeToggle.querySelector('i');
            
            // Check for saved theme preference or use default
            const savedTheme = localStorage.getItem('theme') || 'light';
            htmlElement.setAttribute('data-theme', savedTheme);
            
            // Update icon based on theme
            if (savedTheme === 'dark') {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            } else {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
            }
            
            // Toggle theme on click
            themeToggle.addEventListener('click', function() {
                const currentTheme = htmlElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                
                htmlElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                
                if (newTheme === 'dark') {
                    themeIcon.classList.remove('fa-moon');
                    themeIcon.classList.add('fa-sun');
                } else {
                    themeIcon.classList.remove('fa-sun');
                    themeIcon.classList.add('fa-moon');
                }
            });
            
            // Generate particles
            createParticles();
            
            // 3D mouse effect for error card
            const errorScene = document.querySelector('.error-scene');
            const errorCard = document.querySelector('.error-card');
            
            if (errorScene && errorCard) {
                errorScene.addEventListener('mousemove', (e) => {
                    const rect = errorScene.getBoundingClientRect();
                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;
                    const mouseX = e.clientX - rect.left;
                    const mouseY = e.clientY - rect.top;
                    
                    // Calculate rotation values (max ±5 degrees)
                    const rotateY = ((mouseX - centerX) / centerX) * 5;
                    const rotateX = -((mouseY - centerY) / centerY) * 5;
                    
                    // Apply rotation with transition
                    errorCard.style.transition = 'transform 0.1s ease-out';
                    errorCard.style.transform = `rotateY(${rotateY}deg) rotateX(${rotateX}deg) translateZ(20px)`;
                });
                
                // Reset on mouse leave
                errorScene.addEventListener('mouseleave', () => {
                    errorCard.style.transition = 'transform 0.5s ease-out';
                    errorCard.style.transform = 'rotateY(0deg) rotateX(0deg) translateZ(20px)';
                });
            }
        });
        
        // Create particle effect
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            if (!particlesContainer) return;
            
            const particleCount = window.innerWidth < 768 ? 30 : 50;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                // Random position
                const posX = Math.random() * 100;
                const posY = Math.random() * 100 + 100; // Start below the visible area
                
                // Random size
                const size = Math.random() * 3 + 2;
                
                // Random opacity
                const opacity = Math.random() * 0.5 + 0.3;
                
                // Random horizontal movement
                const tx = Math.random() * 200 - 100;
                
                // Random animation duration
                const duration = Math.random() * 15 + 10;
                
                // Set styles
                particle.style.cssText = `
                    left: ${posX}%;
                    top: ${posY}px;
                    width: ${size}px;
                    height: ${size}px;
                    opacity: ${opacity};
                    --tx: ${tx}px;
                    animation-duration: ${duration}s;
                    animation-delay: ${Math.random() * 10}s;
                `;
                
                particlesContainer.appendChild(particle);
            }
        }
    </script>
</body>
</html> 