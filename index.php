<?php
require_once 'config/config.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Smart Note Taking Redefined</title>
    <link rel="stylesheet" href="assets/css/landing.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        @media (max-width: 480px) {
          .button-div{
            display: flex;
            flex-direction: row;
          }
          .nav-btn{
            padding: 0px;
            margin: 1px;
          }
          .nav-btn-signup{
            display: none;
          }
          .mockup-container{
            display: none;
          }
          .hero-wave{
            display: none;
          }
        }
      </style>
</head>
<body class="landing-page">
    <!-- Navigation Bar -->
    <nav class="landing-navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <div class="logo-container">
                    <div class="logo-icon"><i class="fas fa-book-open"></i></div>
                    <span><?php echo APP_NAME; ?></span>
                </div>
            </div>
            <div class="nav-links">
                <div class="button-div">
                    <a href="views/login.php" class="nav-btn nav-btn-login">Login</a>
                    <a style="margin-left: 15px;" href="views/signup.php" class="nav-btn nav-btn-signup">Sign Up</a>
                </div>
                <button class="theme-toggle" id="themeToggle" onclick="toggleTheme()">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-container">
            <div class="hero-content" data-aos="fade-right" data-aos-duration="1000">
                <h1 class="hero-title">Transform Your Ideas Into <span class="gradient-text">Organized Brilliance</span></h1>
                <p class="hero-subtitle">Experience note-taking reimagined with powerful organization, rich editing, and intelligent features that adapt to your workflow.</p>
                <div class="hero-actions">
                    <a href="views/signup.php" class="btn btn-primary btn-hero">
                        <span>Get Started Free</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <a href="#features" class="btn btn-secondary btn-hero">
                        <i class="fas fa-lightbulb"></i>
                        <span>Explore Features</span>
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-number">25K+</div>
                        <div class="stat-label">Active Users</div>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <div class="stat-number">4.8</div>
                        <div class="stat-label">User Rating</div>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <div class="stat-number">99.9%</div>
                        <div class="stat-label">Uptime</div>
                    </div>
                </div>
            </div>
            <div class="hero-visual" data-aos="fade-left" data-aos-duration="1000">
                <div class="mockup-container">
                    <div class="note-app-mockup">
                        <div class="mockup-header">
                            <div class="mockup-actions">
                                <span class="mockup-action red"></span>
                                <span class="mockup-action yellow"></span>
                                <span class="mockup-action green"></span>
                            </div>
                            <div class="mockup-title">My Notes</div>
                        </div>
                        <div class="mockup-body">
                            <div class="mockup-note" style="transform: translateZ(10px);">
                                <div class="note-title">Project Ideas <span class="note-tag">Work</span></div>
                                <div class="note-content">Integrate AI features to enhance user workflow and productivity...</div>
                                <div class="note-date">2 hours ago</div>
                            </div>
                            <div class="mockup-note active" style="transform: translateZ(20px);">
                                <div class="note-title">Meeting Notes <span class="note-tag">Important</span></div>
                                <div class="note-content">Discuss new feature rollout with team and set milestone dates...</div>
                                <div class="note-date">Yesterday</div>
                            </div>
                            <div class="mockup-note" style="transform: translateZ(10px);">
                                <div class="note-title">Reading List <span class="note-tag">Personal</span></div>
                                <div class="note-content">1. Clean Architecture by Robert Martin 2. Deep Work by Cal Newport...</div>
                                <div class="note-date">3 days ago</div>
                            </div>
                        </div>
                    </div>
                    <div class="floating-elements">
                        <div class="floating-element element-1"><i class="fas fa-lightbulb"></i></div>
                        <div class="floating-element element-2"><i class="fas fa-tag"></i></div>
                        <div class="floating-element element-3"><i class="fas fa-search"></i></div>
                        <div class="floating-element element-4"><i class="fas fa-clock"></i></div>
                        <div class="floating-element element-5"><i class="fas fa-image"></i></div>
                        <div class="floating-element element-6"><i class="fas fa-share-alt"></i></div>
                        <div class="floating-element element-7"><i class="fas fa-palette"></i></div>
                    </div>
                    <div class="mockup-reflection"></div>
                </div>
            </div>
        </div>
        <div class="hero-wave">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="var(--card-bg)" fill-opacity="1" d="M0,192L48,176C96,160,192,128,288,128C384,128,480,160,576,154.7C672,149,768,107,864,90.7C960,75,1056,85,1152,117.3C1248,149,1344,203,1392,229.3L1440,256L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>
        </div>
        <div class="hero-particles"></div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section">
        <div class="section-container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title">Intelligent Features <span class="gradient-text">That Inspire</span></h2>
                <p class="section-subtitle">Discover how our advanced tools make note-taking effortless and powerful</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card" data-aos="zoom-in" data-aos-delay="100">
                    <div class="feature-icon">
                        <i class="fas fa-pen-fancy"></i>
                    </div>
                    <div class="feature-content">
                        <h3>Rich Text Editor</h3>
                        <p>Craft beautiful notes with advanced formatting, inline images, code blocks, and more—powered by modern rich text technology.</p>
                    </div>
                </div>
                
                <div class="feature-card" data-aos="zoom-in" data-aos-delay="200">
                    <div class="feature-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <div class="feature-content">
                        <h3>Smart Organization</h3>
                        <p>AI-powered tag suggestions, automatic categorization, and intelligent sorting help keep your notes perfectly organized.</p>
                    </div>
                </div>
                
                <div class="feature-card" data-aos="zoom-in" data-aos-delay="300">
                    <div class="feature-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="feature-content">
                        <h3>Semantic Search</h3>
                        <p>Find exactly what you're looking for with our advanced search that understands context and natural language queries.</p>
                    </div>
                </div>
                
                <div class="feature-card" data-aos="zoom-in" data-aos-delay="400">
                    <div class="feature-icon">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <div class="feature-content">
                        <h3>Real-time Sync</h3>
                        <p>Your notes sync instantly across all your devices, ensuring your latest thoughts are always at your fingertips.</p>
                    </div>
                </div>
                
                <div class="feature-card" data-aos="zoom-in" data-aos-delay="500">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="feature-content">
                        <h3>End-to-End Encryption</h3>
                        <p>Your private thoughts remain private with strong encryption that protects your notes from prying eyes.</p>
                    </div>
                </div>
                
                <div class="feature-card" data-aos="zoom-in" data-aos-delay="600">
                    <div class="feature-icon">
                        <i class="fas fa-link"></i>
                    </div>
                    <div class="feature-content">
                        <h3>Networked Thinking</h3>
                        <p>Connect related ideas with bidirectional linking that helps you build a personal knowledge database.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Workflow Section -->
    <section class="workflow-section">
        <div class="section-container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title">Designed for <span class="gradient-text">Your Workflow</span></h2>
                <p class="section-subtitle">See how <?php echo APP_NAME; ?> adapts to your unique way of thinking</p>
            </div>
            
            <div class="workflow-tabs">
                <div class="tabs-nav" data-aos="fade-up">
                    <button class="tab-btn active" data-tab="students">
                        <!-- <i class="fas fa-graduation-cap"></i> -->
                        <!-- <i class="fa-solid fa-graduation-cap"></i> -->
                        <!-- <i class="fa-solid fa-graduation-cap fa-2xl"></i> -->
                        <i class="fa-solid fa-building-columns fa-2xl" style="color: #74C0FC;"></i>
                        <span>Students</span>
                    </button>
                    <button class="tab-btn" data-tab="professionals">
                        <i class="fas fa-briefcase"></i>
                        <span>Professionals</span>
                    </button>
                    <button class="tab-btn" data-tab="creatives">
                        <i class="fas fa-paint-brush"></i>
                        <span>Creatives</span>
                    </button>
                    <button class="tab-btn" data-tab="researchers">
                        <i class="fas fa-flask"></i>
                        <span>Researchers</span>
                    </button>
                </div>
                
                <div class="tabs-content" data-aos="fade-up">
                    <div class="tab-panel active" id="students-panel">
                        <div class="panel-content">
                            <div class="panel-text">
                                <h3>Study Smarter, Not Harder</h3>
                                <p>Organize class notes, track assignments, and prepare for exams with our dedicated student-focused features:</p>
                                <ul class="feature-list">
                                    <li><i class="fas fa-check"></i> Flashcard generation from your notes</li>
                                    <li><i class="fas fa-check"></i> Syllabus and assignment tracking</li>
                                    <li><i class="fas fa-check"></i> Study timer with smart breaks</li>
                                    <li><i class="fas fa-check"></i> Citation generator and bibliography management</li>
                                </ul>
                            </div>
                            <div class="panel-image student-img"></div>
                        </div>
                    </div>
                    
                    <div class="tab-panel" id="professionals-panel">
                        <div class="panel-content">
                            <div class="panel-text">
                                <h3>Boost Your Productivity</h3>
                                <p>Stay organized and on top of your work with business-ready tools:</p>
                                <ul class="feature-list">
                                    <li><i class="fas fa-check"></i> Meeting notes with action item tracking</li>
                                    <li><i class="fas fa-check"></i> Project management integration</li>
                                    <li><i class="fas fa-check"></i> Client information management</li>
                                    <li><i class="fas fa-check"></i> One-click sharing with colleagues</li>
                                </ul>
                            </div>
                            <div class="panel-image professional-img"></div>
                        </div>
                    </div>
                    
                    <div class="tab-panel" id="creatives-panel">
                        <div class="panel-content">
                            <div class="panel-text">
                                <h3>Capture Your Creative Spark</h3>
                                <p>Nurture your creative process from inspiration to finished work:</p>
                                <ul class="feature-list">
                                    <li><i class="fas fa-check"></i> Mood boards and visual collections</li>
                                    <li><i class="fas fa-check"></i> Audio note recording and transcription</li>
                                    <li><i class="fas fa-check"></i> Sketch and drawing support</li>
                                    <li><i class="fas fa-check"></i> Inspiration tracker with source links</li>
                                </ul>
                            </div>
                            <div class="panel-image creative-img"></div>
                        </div>
                    </div>
                    
                    <div class="tab-panel" id="researchers-panel">
                        <div class="panel-content">
                            <div class="panel-text">
                                <h3>Organize Complex Information</h3>
                                <p>Structure your research with powerful knowledge management tools:</p>
                                <ul class="feature-list">
                                    <li><i class="fas fa-check"></i> Literature review organization</li>
                                    <li><i class="fas fa-check"></i> Citation management and formatting</li>
                                    <li><i class="fas fa-check"></i> Data table support and visualization</li>
                                    <li><i class="fas fa-check"></i> Concept mapping and relationship diagrams</li>
                                </ul>
                            </div>
                            <div class="panel-image researcher-img"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="section-container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title">Loved by <span class="gradient-text">Thousands</span></h2>
                <p class="section-subtitle">See what our users have to say about their experience</p>
            </div>
            
            <div class="testimonials-slider" data-aos="fade-up">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="testimonial-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p>"This app completely transformed how I take notes for my studies. The organization system is intuitive and the search function has saved me hours of time when studying for exams. The UI is easy to use."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">NR</div>
                        <div class="author-info">
                            <div class="author-name">Nilesh Rana</div>
                            <div class="author-title">CSE Student</div>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="testimonial-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p>"As a project manager, I need to keep track of countless details across multiple projects. This note-taking app has become my second brain, helping me stay organized and never miss important information."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">CSR</div>
                        <div class="author-info">
                            <div class="author-name">Chandan Singh</div>
                            <div class="author-title">CSE Student</div>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="testimonial-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <p>"I love how this app adapts to my creative workflow. I can capture ideas whenever inspiration strikes and easily organize them into collections. The visual organization tools are perfect for my design work."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">AS</div>
                        <div class="author-info">
                            <div class="author-name">Anmol</div>
                            <div class="author-title">CSE Student</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </section>
    
    <!-- CTA Section -->
    <section class="cta-section">
        <div class="section-container">
            <div class="cta-card" data-aos="zoom-in">
                <div class="cta-content">
                    <h2>Ready to Transform Your Note-Taking?</h2>
                    <p>Join thousands of satisfied users who have elevated their productivity with <?php echo APP_NAME; ?>.</p>
                    <div class="cta-buttons">
                        <a href="views/signup.php" class="btn btn-primary btn-large">
                            <span>Get Started Free</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="views/login.php" class="btn btn-outline">
                            <span>Already have an account?</span>
                        </a>
                    </div>
                </div>
                <div class="cta-decoration">
                    <div class="floating-shape shape-1"></div>
                    <div class="floating-shape shape-2"></div>
                    <div class="floating-shape shape-3"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-3d">
        <div class="footer-container">
            <div class="footer-creator">
                Made with <i class="fas fa-heart"></i> by <a href="https://techycsr.dev" target="_blank" class="creator-link">@TechyCSR</a>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 
