// Add smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Navbar scroll effect with enhanced 3D
const navbar = document.querySelector('.navbar');
let lastScroll = 0;

window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;
    
    if (currentScroll <= 0) {
        navbar.classList.remove('scroll-up');
        navbar.style.transform = 'translateY(0) translateZ(0)';
        return;
    }
    
    if (currentScroll > lastScroll && !navbar.classList.contains('scroll-down')) {
        navbar.classList.remove('scroll-up');
        navbar.classList.add('scroll-down');
        navbar.style.transform = 'translateY(-100%) translateZ(0)';
    } else if (currentScroll < lastScroll && navbar.classList.contains('scroll-down')) {
        navbar.classList.remove('scroll-down');
        navbar.classList.add('scroll-up');
        navbar.style.transform = 'translateY(0) translateZ(10px)';
    }
    lastScroll = currentScroll;
});

// Add parallax effect to floating notes
document.addEventListener('mousemove', (e) => {
    const notes = document.querySelectorAll('.floating-note');
    const mouseX = e.clientX / window.innerWidth;
    const mouseY = e.clientY / window.innerHeight;

    notes.forEach((note, index) => {
        const depth = (index + 1) * 0.1;
        const moveX = (mouseX - 0.5) * depth * 50;
        const moveY = (mouseY - 0.5) * depth * 50;
        note.style.transform = `translate3d(${moveX}px, ${moveY}px, 0) rotate3d(${mouseY}, ${mouseX}, 0, ${depth * 10}deg)`;
    });
});

// Intersection Observer for fade-in animations
const observerOptions = {
    root: null,
    threshold: 0.1,
    rootMargin: '0px'
};

const observer = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('fade-in');
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

// Observe all feature cards
document.querySelectorAll('.feature-card').forEach(card => {
    observer.observe(card);
});

// Theme Toggle
function initTheme() {
    // Check for saved theme preference or default to light
    const savedTheme = localStorage.getItem('theme');
    
    let theme;
    if (savedTheme) {
        theme = savedTheme;
    } else {
        // Default to light theme instead of checking system preference
        theme = 'light';
    }
    
    // Apply theme to document
    document.documentElement.setAttribute('data-theme', theme);
    
    // Update theme icon
    updateThemeIcon(theme);
    return theme;
}

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    console.log('Current theme:', currentTheme);
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    console.log('Switching to theme:', newTheme);
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeIcon(newTheme);
    console.log('Theme after toggle:', document.documentElement.getAttribute('data-theme'));
}

function updateThemeIcon(theme) {
    const icon = document.querySelector('.theme-toggle i');
    if (icon) {
        icon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
    }
}

// Initialize theme
const currentTheme = initTheme();

// Initialize theme toggle buttons with correct icons
document.querySelectorAll('.theme-toggle').forEach(btn => {
    const icon = btn.querySelector('i');
    if (icon) {
        icon.className = currentTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
    }
    // Add event listener only for buttons that don't have onclick attribute
    if (!btn.hasAttribute('onclick')) {
        btn.addEventListener('click', toggleTheme);
    }
});

// Authentication page enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Detect authentication pages
    const authCard = document.querySelector('.auth-card');
    if (authCard) {
        // Add subtle movement to auth card on mouse move
        document.addEventListener('mousemove', (e) => {
            const mouseX = (e.clientX / window.innerWidth - 0.5) * 5;
            const mouseY = (e.clientY / window.innerHeight - 0.5) * 5;
            
            authCard.style.transform = `perspective(1000px) rotateY(${mouseX}deg) rotateX(${-mouseY}deg) translateY(-5px)`;
        });
        
        // Reset transform when mouse leaves
        document.addEventListener('mouseleave', () => {
            authCard.style.transform = 'perspective(1000px) rotateY(0deg) rotateX(0deg) translateY(-5px)';
        });
        
        // Apply focus effect to input fields
        const inputs = document.querySelectorAll('.input-group input');
        inputs.forEach(input => {
            // Auto-focus first input field
            if (inputs[0] && !inputs[0].value) {
                setTimeout(() => {
                    inputs[0].focus();
                }, 500);
            }
            
            // Label animation
            const label = input.closest('.form-group').querySelector('label');
            if (label) {
                input.addEventListener('focus', () => {
                    label.style.color = 'var(--primary-color)';
                });
                
                input.addEventListener('blur', () => {
                    label.style.color = '';
                });
            }
        });
        
        // Show error messages with animation
        const errorMessage = document.querySelector('.error-message');
        if (errorMessage) {
            errorMessage.style.animation = 'none';
            setTimeout(() => {
                errorMessage.style.animation = 'messageSlideDown 0.3s ease';
            }, 100);
        }
        
        // Password toggle buttons
        const passwordToggles = document.querySelectorAll('.password-toggle');
        passwordToggles.forEach(toggle => {
            toggle.addEventListener('mousedown', (e) => {
                e.preventDefault(); // Prevent focus loss on the input
            });
        });
    }
    
    // Initialize AOS animations
    AOS.init({
        once: true,
        offset: 100,
        duration: 800
    });
    
    // 3D effect for the mockup container
    const mockupContainer = document.querySelector('.mockup-container');
    const mockup = document.querySelector('.note-app-mockup');
    const mockupReflection = document.querySelector('.mockup-reflection');
    const floatingElements = document.querySelectorAll('.floating-element');
    
    if (mockupContainer && mockup) {
        // Variables for 3D effect
        let containerWidth = mockupContainer.offsetWidth;
        let containerHeight = mockupContainer.offsetHeight;
        
        // Handle mouse movement
        mockupContainer.addEventListener('mousemove', (e) => {
            // Get mouse position relative to container
            const rect = mockupContainer.getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;
            
            // Calculate rotation based on mouse position
            // Convert to percentage and then to degrees (max 20 degrees)
            const rotateY = ((mouseX / containerWidth - 0.5) * 20).toFixed(2);
            const rotateX = ((0.5 - mouseY / containerHeight) * 10).toFixed(2);
            
            // Apply transform to mockup with smooth animation
            mockup.style.transition = 'transform 0.1s ease-out';
            mockup.style.transform = `
                rotateY(${rotateY}deg) 
                rotateX(${rotateX}deg) 
                translateZ(30px)
            `;
            
            // Update reflection position
            if (mockupReflection) {
                mockupReflection.style.transform = `
                    rotateX(60deg) 
                    rotateY(${rotateY * 0.5}deg) 
                    scaleY(${0.2 + Math.abs(rotateX) * 0.01})
                `;
                mockupReflection.style.opacity = 0.6 + Math.abs(rotateX) * 0.01;
            }
            
            // Apply parallax effect to mockup notes
            document.querySelectorAll('.mockup-note').forEach((note, index) => {
                const depth = (index + 1) * 0.5;
                note.style.transform = `
                    translateX(${rotateY * depth}px) 
                    translateY(${-rotateX * depth}px) 
                    translateZ(${10 + index * 5}px)
                `;
            });
            
            // Apply transform to floating elements with different intensities
            floatingElements.forEach((element, index) => {
                const intensity = 1 + (index % 3) * 0.7;
                const moveX = rotateY * intensity;
                const moveY = -rotateX * intensity;
                const moveZ = 20 + index * 8;
                
                // Calculate distance from mouse to element center for glow effect
                const elRect = element.getBoundingClientRect();
                const elCenterX = elRect.left + elRect.width / 2;
                const elCenterY = elRect.top + elRect.height / 2;
                const distX = e.clientX - elCenterX;
                const distY = e.clientY - elCenterY;
                const distance = Math.sqrt(distX * distX + distY * distY);
                const maxDistance = 300;
                const glowIntensity = Math.max(0, 1 - distance / maxDistance);
                
                element.style.transform = `
                    translateX(${moveX}px) 
                    translateY(${moveY}px) 
                    translateZ(${moveZ}px)
                `;
                
                // Add glow effect when mouse is near
                element.style.boxShadow = `
                    0 10px 30px rgba(0, 0, 0, 0.15),
                    0 0 ${glowIntensity * 30}px rgba(255, 255, 255, ${glowIntensity * 0.8})
                `;
                element.style.background = `rgba(255, 255, 255, ${0.2 + glowIntensity * 0.3})`;
            });
        });
        
        // Reset transform on mouse leave
        mockupContainer.addEventListener('mouseleave', () => {
            mockup.style.transition = 'transform 0.5s ease-out';
            mockup.style.transform = 'rotateY(-15deg) rotateX(10deg) translateZ(20px)';
            
            // Reset reflection
            if (mockupReflection) {
                mockupReflection.style.transform = 'rotateX(60deg) rotateY(-15deg) scaleY(0.2)';
                mockupReflection.style.opacity = '0.6';
            }
            
            // Reset mockup notes
            document.querySelectorAll('.mockup-note').forEach((note, index) => {
                if (note.classList.contains('active')) {
                    note.style.transform = 'translateZ(20px)';
                } else {
                    note.style.transform = 'translateZ(10px)';
                }
            });
            
            // Reset floating elements
            floatingElements.forEach(element => {
                element.style.transform = '';
                element.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.15)';
                element.style.background = 'rgba(255, 255, 255, 0.2)';
            });
        });
        
        // Handle window resize
        window.addEventListener('resize', () => {
            containerWidth = mockupContainer.offsetWidth;
            containerHeight = mockupContainer.offsetHeight;
        });
    }
    
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanels = document.querySelectorAll('.tab-panel');

    if (tabButtons.length > 0 && tabPanels.length > 0) {
        tabButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                // Remove active class from all buttons
                tabButtons.forEach(button => button.classList.remove('active'));
                
                // Add active class to clicked button
                btn.classList.add('active');
                
                // Get the tab to show
                const tabToShow = btn.getAttribute('data-tab');
                
                // Hide all tab panels
                tabPanels.forEach(panel => panel.classList.remove('active'));
                
                // Show the correct panel
                document.getElementById(`${tabToShow}-panel`).classList.add('active');
            });
        });
    }
    
    // Initialize AOS animations
    if (typeof AOS !== 'undefined') {
        AOS.init({
            once: true,
            offset: 50,
            duration: 800,
            easing: 'ease-out-cubic'
        });
    }
});

// Add advanced 3D effects to hero elements
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced 3D button effects
    const heroSection = document.querySelector('.hero-section');
    const heroButtons = document.querySelectorAll('.btn-hero');
    const heroStats = document.querySelector('.hero-stats');
    const heroTitle = document.querySelector('.hero-title');
    const gradientText = document.querySelector('.gradient-text');
    
    if (heroSection) {
        // Mouse move effect for the entire hero section
        heroSection.addEventListener('mousemove', (e) => {
            const { left, top, width, height } = heroSection.getBoundingClientRect();
            const mouseX = (e.clientX - left) / width - 0.5;
            const mouseY = (e.clientY - top) / height - 0.5;
            
            // Apply subtle movement to hero elements
            if (gradientText) {
                gradientText.style.transform = `translateZ(30px) translate(${mouseX * 10}px, ${mouseY * 10}px)`;
            }
            
            // 3D lighting effect on buttons
            heroButtons.forEach(button => {
                const btnRect = button.getBoundingClientRect();
                const btnCenterX = btnRect.left + btnRect.width / 2;
                const btnCenterY = btnRect.top + btnRect.height / 2;
                
                // Calculate distance from mouse to button center
                const distanceX = e.clientX - btnCenterX;
                const distanceY = e.clientY - btnCenterY;
                
                // Calculate the angle to create a light source effect
                const angle = Math.atan2(distanceY, distanceX);
                const distance = Math.sqrt(distanceX * distanceX + distanceY * distanceY);
                const maxDistance = Math.max(window.innerWidth, window.innerHeight) / 2;
                const intensity = (1 - Math.min(distance / maxDistance, 1)) * 0.6;
                
                // Create light effect
                button.style.boxShadow = `
                    0 10px 25px rgba(0, 0, 0, 0.1),
                    0 5px 10px rgba(0, 0, 0, 0.05),
                    inset 0 0 0 1px rgba(255, 255, 255, 0.2),
                    ${Math.cos(angle) * 20 * intensity}px ${Math.sin(angle) * 20 * intensity}px 30px rgba(255, 255, 255, ${intensity})
                `;
                
                // Add subtle movement
                button.style.transform = `
                    translateY(-${intensity * 5}px) 
                    rotateX(${mouseY * 10}deg) 
                    rotateY(${-mouseX * 10}deg)
                `;
            });
            
            // Apply effect to stats
            if (heroStats) {
                heroStats.style.transform = `
                    perspective(1000px) 
                    translateZ(10px) 
                    rotateX(${mouseY * 5}deg) 
                    rotateY(${-mouseX * 5}deg)
                `;
            }
        });
        
        // Reset effects when mouse leaves hero section
        heroSection.addEventListener('mouseleave', () => {
            heroButtons.forEach(button => {
                button.style.boxShadow = '';
                button.style.transform = '';
            });
            
            if (heroStats) {
                heroStats.style.transform = 'perspective(1000px) translateZ(10px)';
            }
            
            if (gradientText) {
                gradientText.style.transform = 'translateZ(30px)';
            }
        });
    }
    
    // Enhanced 3D effect for the mockup container
    const mockupContainer = document.querySelector('.mockup-container');
    const mockup = document.querySelector('.note-app-mockup');
    const mockupReflection = document.querySelector('.mockup-reflection');
    const floatingElements = document.querySelectorAll('.floating-element');
    
    if (mockupContainer && mockup) {
        // Variables for 3D effect
        let containerWidth = mockupContainer.offsetWidth;
        let containerHeight = mockupContainer.offsetHeight;
        
        // Handle mouse movement
        mockupContainer.addEventListener('mousemove', (e) => {
            // Get mouse position relative to container
            const rect = mockupContainer.getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;
            
            // Calculate rotation based on mouse position
            // Convert to percentage and then to degrees (max 20 degrees)
            const rotateY = ((mouseX / containerWidth - 0.5) * 20).toFixed(2);
            const rotateX = ((0.5 - mouseY / containerHeight) * 10).toFixed(2);
            
            // Apply transform to mockup with smooth animation
            mockup.style.transition = 'transform 0.1s ease-out';
            mockup.style.transform = `
                rotateY(${rotateY}deg) 
                rotateX(${rotateX}deg) 
                translateZ(30px)
            `;
            
            // Update reflection position
            if (mockupReflection) {
                mockupReflection.style.transform = `
                    rotateX(60deg) 
                    rotateY(${rotateY * 0.5}deg) 
                    scaleY(${0.2 + Math.abs(rotateX) * 0.01})
                `;
                mockupReflection.style.opacity = 0.6 + Math.abs(rotateX) * 0.01;
            }
            
            // Apply parallax effect to mockup notes
            document.querySelectorAll('.mockup-note').forEach((note, index) => {
                const depth = (index + 1) * 0.5;
                note.style.transform = `
                    translateX(${rotateY * depth}px) 
                    translateY(${-rotateX * depth}px) 
                    translateZ(${10 + index * 5}px)
                `;
            });
            
            // Apply transform to floating elements with different intensities
            floatingElements.forEach((element, index) => {
                const intensity = 1 + (index % 3) * 0.7;
                const moveX = rotateY * intensity;
                const moveY = -rotateX * intensity;
                const moveZ = 20 + index * 8;
                
                // Calculate distance from mouse to element center for glow effect
                const elRect = element.getBoundingClientRect();
                const elCenterX = elRect.left + elRect.width / 2;
                const elCenterY = elRect.top + elRect.height / 2;
                const distX = e.clientX - elCenterX;
                const distY = e.clientY - elCenterY;
                const distance = Math.sqrt(distX * distX + distY * distY);
                const maxDistance = 300;
                const glowIntensity = Math.max(0, 1 - distance / maxDistance);
                
                element.style.transform = `
                    translateX(${moveX}px) 
                    translateY(${moveY}px) 
                    translateZ(${moveZ}px)
                `;
                
                // Add glow effect when mouse is near
                element.style.boxShadow = `
                    0 10px 30px rgba(0, 0, 0, 0.15),
                    0 0 ${glowIntensity * 30}px rgba(255, 255, 255, ${glowIntensity * 0.8})
                `;
                element.style.background = `rgba(255, 255, 255, ${0.2 + glowIntensity * 0.3})`;
            });
        });
        
        // Reset transform on mouse leave
        mockupContainer.addEventListener('mouseleave', () => {
            mockup.style.transition = 'transform 0.5s ease-out';
            mockup.style.transform = 'rotateY(-15deg) rotateX(10deg) translateZ(20px)';
            
            // Reset reflection
            if (mockupReflection) {
                mockupReflection.style.transform = 'rotateX(60deg) rotateY(-15deg) scaleY(0.2)';
                mockupReflection.style.opacity = '0.6';
            }
            
            // Reset mockup notes
            document.querySelectorAll('.mockup-note').forEach((note, index) => {
                if (note.classList.contains('active')) {
                    note.style.transform = 'translateZ(20px)';
                } else {
                    note.style.transform = 'translateZ(10px)';
                }
            });
            
            // Reset floating elements
            floatingElements.forEach(element => {
                element.style.transform = '';
                element.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.15)';
                element.style.background = 'rgba(255, 255, 255, 0.2)';
            });
        });
        
        // Handle window resize
        window.addEventListener('resize', () => {
            containerWidth = mockupContainer.offsetWidth;
            containerHeight = mockupContainer.offsetHeight;
        });
    }
    
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanels = document.querySelectorAll('.tab-panel');

    if (tabButtons.length > 0 && tabPanels.length > 0) {
        tabButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                // Remove active class from all buttons
                tabButtons.forEach(button => button.classList.remove('active'));
                
                // Add active class to clicked button
                btn.classList.add('active');
                
                // Get the tab to show
                const tabToShow = btn.getAttribute('data-tab');
                
                // Hide all tab panels
                tabPanels.forEach(panel => panel.classList.remove('active'));
                
                // Show the correct panel
                document.getElementById(`${tabToShow}-panel`).classList.add('active');
            });
        });
    }
    
    // Initialize AOS animations
    if (typeof AOS !== 'undefined') {
        AOS.init({
            once: true,
            offset: 50,
            duration: 800,
            easing: 'ease-out-cubic'
        });
    }
}); 