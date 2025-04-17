// Profile management
const ProfileManager = {
    init() {
        this.initElements();
        this.bindEvents();
    },

    initElements() {
        this.profileBtn = document.getElementById('profileBtn');
        this.profilePopup = document.getElementById('profilePopup');
        this.closeProfileBtn = document.getElementById('closeProfileBtn');
        this.closeProfileBtnBottom = document.getElementById('closeProfileBtnBottom');
        this.updatePasswordForm = document.getElementById('updatePasswordForm');
        this.passwordToggles = document.querySelectorAll('.password-toggle');
    },

    showProfilePopup() {
        if (this.profilePopup) {
            this.profilePopup.classList.add('active');
        }
    },

    hideProfilePopup() {
        if (this.profilePopup) {
            this.profilePopup.classList.remove('active');
        }
    },

    togglePasswordVisibility(button) {
        const input = button.parentElement.querySelector('input');
        const icon = button.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fas fa-eye';
        }
    },

    handlePasswordUpdate(e) {
        e.preventDefault();
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (newPassword !== confirmPassword) {
            this.showPasswordMessage('Passwords do not match!', 'error');
            return;
        }

        if (newPassword.length < 8) {
            this.showPasswordMessage('Password must be at least 8 characters long!', 'error');
            return;
        }

        // Show loading state
        this.showPasswordMessage('Updating password...', 'info');

        // Send password update request
        fetch('../views/update_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `new_password=${encodeURIComponent(newPassword)}`
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Failed to update password');
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                this.showPasswordMessage(data.message || 'Password updated successfully!', 'success');
                this.updatePasswordForm.reset();
                // Hide the profile popup after successful update
                setTimeout(() => this.hideProfilePopup(), 2000);
            } else {
                this.showPasswordMessage(data.message || 'Failed to update password!', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showPasswordMessage(error.message || 'An error occurred while updating password!', 'error');
        });
    },

    showPasswordMessage(message, type) {
        const messageDiv = document.getElementById('passwordUpdateMessage');
        if (messageDiv) {
            messageDiv.textContent = message;
            messageDiv.className = `message ${type}`;
            // Clear the message after 3 seconds only if it's a success message
            if (type === 'success') {
                setTimeout(() => {
                    messageDiv.textContent = '';
                    messageDiv.className = '';
                }, 3000);
            }
        }
    },

    bindEvents() {
        if (this.profileBtn) {
            this.profileBtn.addEventListener('click', () => this.showProfilePopup());
        }

        if (this.closeProfileBtn) {
            this.closeProfileBtn.addEventListener('click', () => this.hideProfilePopup());
        }

        if (this.closeProfileBtnBottom) {
            this.closeProfileBtnBottom.addEventListener('click', () => this.hideProfilePopup());
        }

        if (this.updatePasswordForm) {
            this.updatePasswordForm.addEventListener('submit', (e) => this.handlePasswordUpdate(e));
        }

        this.passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', () => this.togglePasswordVisibility(toggle));
        });
    }
};

document.addEventListener('DOMContentLoaded', () => ProfileManager.init()); 