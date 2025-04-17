<?php
require_once '../config/config.php';
require_once '../models/Note.php';

// Check if a share token is provided in the URL
if (!isset($_GET['token']) || empty($_GET['token'])) {
    die('Invalid request. No share token provided.');
}

$shareToken = $_GET['token'];

// Get the shared note using the token
$note = new Note();
$sharedNote = $note->getNoteByShareToken($shareToken);

if (!$sharedNote) {
    die('This note does not exist or is no longer shared.');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($sharedNote['title']); ?> - Shared Note</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.bubble.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth/common.css">
    <link rel="stylesheet" href="../assets/css/popup.css">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --background-color: #f5f6fa;
            --text-color: #2c3e50;
            --text-muted-color: #666;
            --card-bg: white;
            --border-color: #e0e0e0;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        [data-theme="dark"] {
            --primary-color: #60a5fa;
            --secondary-color: #e2e8f0;
            --accent-color: #f87171;
            --background-color: #1a1a1a;
            --text-color: #e2e8f0;
            --text-muted-color: #a1a1a1;
            --card-bg: #2d2d2d;
            --border-color: #404040;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--background-color);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: var(--card-bg);
            padding: 0.75rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--card-shadow);
            z-index: 1000;
            border-bottom: 1px solid var(--border-color);
        }
        
        .nav-brand {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 700;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateZ(5px);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            transform-style: preserve-3d;
            margin-right: 10px;
        }
        
        .nav-brand:hover .logo-icon {
            transform: translateZ(8px) rotateY(10deg);
        }
        
        .theme-toggle {
            background: none;
            border: none;
            color: var(--text-color);
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            border-radius: 50%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .theme-toggle:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.05);
            border-radius: 50%;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .theme-toggle:hover:before {
            opacity: 1;
        }
        
        .theme-toggle:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .dashboard-link {
            display: flex;
            align-items: center;
            gap: 6px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .dashboard-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(74, 144, 226, 0.2);
        }
        
        .container {
            max-width: 900px;
            margin: 90px auto 40px;
            padding: 0 20px;
            flex: 1;
        }
        
        .shared-note-card {
            background-color: var(--card-bg);
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            padding: 30px;
            margin-bottom: 30px;
            transform-style: preserve-3d;
            transform: perspective(1000px) rotateX(2deg);
            transition: transform 0.3s ease;
            border: 1px solid var(--border-color);
        }
        
        .shared-note-card:hover {
            transform: perspective(1000px) rotateX(0deg);
        }
        
        .shared-note-header {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .shared-note-title {
            font-size: 2.2rem;
            margin: 0 0 10px;
            color: var(--primary-color);
            font-weight: 700;
        }
        
        .shared-note-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            font-size: 0.9rem;
            color: var(--text-muted-color);
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .meta-item i {
            color: var(--primary-color);
        }
        
        .shared-note-content {
            line-height: 1.7;
            font-size: 1.05rem;
            margin-bottom: 30px;
        }
        
        .shared-note-content p {
            margin-bottom: 15px;
        }
        
        .shared-note-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            display: block;
            margin: 20px auto;
        }
        
        .shared-note-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }
        
        .tag {
            background-color: var(--accent-color);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            opacity: 0.8;
        }
        
        .tag:hover {
            opacity: 1;
            transform: translateY(-2px);
        }
        
        .note-3d-elements {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
            pointer-events: none;
            z-index: -1;
        }
        
        .floating-element {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.3;
            animation: float 12s infinite ease-in-out;
        }
        
        .element-1 {
            width: 300px;
            height: 300px;
            background: var(--primary-color);
            top: -100px;
            left: 10%;
            animation-delay: 0s;
        }
        
        .element-2 {
            width: 250px;
            height: 250px;
            background: var(--accent-color);
            bottom: -50px;
            right: 15%;
            animation-delay: -3s;
        }
        
        .element-3 {
            width: 200px;
            height: 200px;
            background: var(--primary-color);
            top: 50%;
            left: -50px;
            animation-delay: -6s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0);
            }
            25% {
                transform: translateY(-20px) translateX(10px);
            }
            50% {
                transform: translateY(0) translateX(20px);
            }
            75% {
                transform: translateY(20px) translateX(10px);
            }
        }
        
        .share-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transform-style: preserve-3d;
            transform: translateZ(10px);
            position: relative;
        }
        
        .share-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(rgba(255,255,255,0.15), rgba(255,255,255,0));
            border-radius: 8px;
            z-index: 1;
            pointer-events: none;
        }
        
        .share-button:hover {
            transform: translateZ(15px) translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        /* Footer */
        .auth-footer-bar {
            background-color: var(--card-bg);
            border-top: 1px solid var(--border-color);
            padding: 1rem 0;
            text-align: center;
            margin-top: auto;
            box-shadow: 0 -5px 10px rgba(0, 0, 0, 0.05);
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .footer-creator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            color: var(--text-muted-color);
            font-size: 0.9rem;
        }

        .footer-creator i {
            color: #e74c3c;
        }

        .creator-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            position: relative;
            padding-bottom: 2px;
        }

        .creator-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: width 0.3s ease;
        }

        .creator-link:hover::after {
            width: 100%;
        }

        @keyframes heartBeat {
            0% { transform: scale(1); }
            14% { transform: scale(1.3); }
            28% { transform: scale(1); }
            42% { transform: scale(1.3); }
            70% { transform: scale(1); }
        }

        .fa-heart.beat {
            animation: heartBeat 1.5s infinite;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                padding: 0.75rem 1rem;
            }
            
            .container {
                padding: 0 15px;
                margin-top: 80px;
            }
            
            .shared-note-card {
                padding: 20px;
            }
            
            .shared-note-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- 3D Background Elements -->
    <div class="note-3d-elements">
        <div class="floating-element element-1"></div>
        <div class="floating-element element-2"></div>
        <div class="floating-element element-3"></div>
    </div>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <a href="../index.php" class="nav-brand">
            <div class="logo-icon">
                <i class="fas fa-book-open"></i>
            </div>
            <span><?php echo APP_NAME; ?></span>
        </a>
        <div class="nav-actions">
            <?php if (isset($_SESSION['user'])): ?>
            <a href="dashboard.php" class="dashboard-link">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <?php endif; ?>
            <button class="theme-toggle" id="themeToggle" onclick="toggleTheme()">
                <i class="fas fa-moon"></i>
            </button>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <!-- Shared Note Card -->
        <div class="shared-note-card" data-aos="fade-up" data-aos-duration="800">
            <div class="shared-note-header">
                <h1 class="shared-note-title"><?php echo htmlspecialchars($sharedNote['title']); ?></h1>
                <div class="shared-note-meta">
                    <div class="meta-item">
                        <i class="fas fa-user"></i>
                        <span>Shared by <?php echo htmlspecialchars($sharedNote['owner_name']); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Created <?php echo (new DateTime($sharedNote['created_at']))->format('M d, Y'); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-share-alt"></i>
                        <span>Shared <?php echo (new DateTime($sharedNote['shared_at']))->format('M d, Y'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="shared-note-content">
                <?php 
                // Allow only safe HTML with images resized
                $content = $sharedNote['content'];
                // Make sure images are responsive
                $content = preg_replace('/<img(.*?)>/i', '<img$1 style="max-width:100%; height:auto;">', $content);
                echo $content; 
                ?>
            </div>
            
            <?php if (!empty($sharedNote['tags'])): ?>
            <div class="shared-note-tags">
                <?php 
                $tags = json_decode($sharedNote['tags'], true);
                if (!empty($tags)): 
                    foreach ($tags as $tag): ?>
                        <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                    <?php endforeach;
                endif; ?>
            </div>
            <?php endif; ?>
            
            <button class="share-button" id="copyShareLink">
                <i class="fas fa-link"></i>
                <span>Copy Share Link</span>
            </button>
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
    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/popup.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
        
        // Copy share link functionality
        document.getElementById('copyShareLink').addEventListener('click', function() {
            const url = window.location.href;
            
            // Use PopupManager for 3D popup instead of direct clipboard API
            PopupManager.showSharePopup('Share Note Link', url);
        });
    </script>
</body>
</html> 