/**
 * Theme Toggle Functionality
 * Handles the dark/light mode switching
 */

// Immediately set theme before DOM content loaded to prevent flickering
(function() {
    const savedTheme = localStorage.getItem('theme');
    // Always default to light theme if no saved preference
    const initialTheme = savedTheme || 'light';
    document.documentElement.setAttribute('data-theme', initialTheme);
})();

// Initialize theme on page load
document.addEventListener('DOMContentLoaded', () => {
    initializeTheme();
});

function initializeTheme() {
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
    
    // Update all theme toggle icons
    updateThemeIcons(theme);
    
    // Set up toggle handlers
    setupThemeToggleHandlers();
    
    return theme;
}

function showThemeChangeNotification(theme) {
    // Create notification element
    const notification = document.createElement('div');
    notification.style.position = 'fixed';
    notification.style.bottom = '20px';
    notification.style.right = '20px';
    notification.style.padding = '10px 20px';
    notification.style.borderRadius = '5px';
    notification.style.zIndex = '9999';
    notification.style.opacity = '0';
    
    // Set theme-specific styles
    if (theme === 'dark') {
        notification.style.backgroundColor = '#333';
        notification.style.color = '#fff';
        notification.textContent = '🌙 Dark mode enabled';
    } else {
        notification.style.backgroundColor = '#f5f5f5';
        notification.style.color = '#333';
        notification.textContent = '☀️ Light mode enabled';
    }
    
    // Add to document
    document.body.appendChild(notification);
    
    // Fade in
    setTimeout(() => {
        notification.style.transition = 'opacity 0.3s ease-in-out';
        notification.style.opacity = '1';
    }, 10);
    
    // Remove after delay
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 2000);
}

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    console.log('Current theme:', currentTheme);
    
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    console.log('Switching to theme:', newTheme);
    
    // Apply theme to document
    document.documentElement.setAttribute('data-theme', newTheme);
    
    // Force a DOM refresh
    document.documentElement.style.display = 'none';
    setTimeout(() => {
        document.documentElement.style.display = '';
    }, 1);
    
    // Save preference
    localStorage.setItem('theme', newTheme);
    
    // Update all theme toggle icons
    updateThemeIcons(newTheme);
    
    // Show notification
    showThemeChangeNotification(newTheme);
    
    console.log('Theme after toggle:', document.documentElement.getAttribute('data-theme'));
    return newTheme;
}

function updateThemeIcons(theme) {
    const icons = document.querySelectorAll('.theme-toggle i');
    icons.forEach(icon => {
        icon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
    });
}

function setupThemeToggleHandlers() {
    // Only set up event listeners for buttons that don't have onclick
    document.querySelectorAll('.theme-toggle').forEach(btn => {
        if (!btn.hasAttribute('onclick')) {
            btn.addEventListener('click', toggleTheme);
        }
    });
} 