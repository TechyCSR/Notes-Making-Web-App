// 3D Popup Manager
const PopupManager = {
    init() {
        this.createPopupElements();
        this.bindEvents();
    },

    createPopupElements() {
        // Create main popup overlay
        this.overlay = document.createElement('div');
        this.overlay.className = 'popup-overlay';
        
        // Create popup container with 3D effect
        this.container = document.createElement('div');
        this.container.className = 'popup-container';
        
        this.overlay.appendChild(this.container);
        document.body.appendChild(this.overlay);

        // Initialize state
        this.isOpen = false;
        this.currentCallback = null;
    },

    bindEvents() {
        // Close popup when clicking on overlay
        this.overlay.addEventListener('click', (e) => {
            if (e.target === this.overlay) {
                this.close();
            }
        });
        
        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });
    },

    // Share popup with options to copy link
    showSharePopup(title, shareUrl) {
        // Popup header
        let header = `
            <div class="popup-header">
                <h3 class="popup-title">${title}</h3>
                <button class="popup-close" id="popupClose"><i class="fas fa-times"></i></button>
            </div>
        `;
        
        // Popup content with copy link functionality
        let content = `
            <div class="popup-content">
                <p>You can copy the link below to share this note:</p>
                <div class="popup-copy-link">
                    <input type="text" class="popup-copy-input" id="shareUrlInput" value="${shareUrl}" readonly>
                    <button class="popup-copy-btn" id="copyLinkBtn">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
            <div class="popup-footer">
                <button class="popup-btn popup-btn-primary" id="popupDone">Done</button>
            </div>
        `;
        
        // Set container content
        this.container.innerHTML = header + content;
        
        // Add event listeners
        this.container.querySelector('#popupClose').addEventListener('click', () => this.close());
        this.container.querySelector('#popupDone').addEventListener('click', () => this.close());
        
        // Copy functionality
        const copyBtn = this.container.querySelector('#copyLinkBtn');
        const urlInput = this.container.querySelector('#shareUrlInput');
        const successMsg = this.container.querySelector('#copySuccess');
        
        copyBtn.addEventListener('click', () => {
            this.copyToClipboard(urlInput.value)
                .then(() => {
                    // Show success message
                    successMsg.classList.add('show');
                    setTimeout(() => {
                        successMsg.classList.remove('show');
                    }, 2000);
                })
                .catch(err => {
                    console.error('Copy failed', err);
                    // Fallback: select text for manual copy
                    urlInput.select();
                });
        });
        
        // Open the popup
        this.open();
    },

    // Helper method to copy text to clipboard
    copyToClipboard(text) {
        // Try to use the newer Clipboard API if available
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        } else {
            // Fallback for older browsers
            return new Promise((resolve, reject) => {
                try {
                    const textArea = document.createElement('textarea');
                    textArea.value = text;
                    textArea.style.position = 'fixed';
                    textArea.style.opacity = '0';
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();
                    
                    const successful = document.execCommand('copy');
                    document.body.removeChild(textArea);
                    
                    if (successful) {
                        resolve();
                    } else {
                        reject(new Error('Unable to copy'));
                    }
                } catch (err) {
                    reject(err);
                }
            });
        }
    },

    // Confirmation popup with callback
    showConfirmPopup(title, message, okText, cancelText, callback) {
        // Popup header
        let header = `
            <div class="popup-header">
                <h3 class="popup-title">${title}</h3>
                <button class="popup-close" id="popupClose"><i class="fas fa-times"></i></button>
            </div>
        `;
        
        // Popup content with confirmation message
        let content = `
            <div class="popup-content">
                <p>${message}</p>
            </div>
            <div class="popup-footer">
                <button class="popup-btn popup-btn-secondary" id="popupCancel">${cancelText || 'Cancel'}</button>
                <button class="popup-btn popup-btn-primary" id="popupOk">${okText || 'OK'}</button>
            </div>
        `;
        
        // Set container content
        this.container.innerHTML = header + content;
        
        // Store callback
        this.currentCallback = callback;
        
        // Add event listeners
        this.container.querySelector('#popupClose').addEventListener('click', () => {
            this.close();
            if (callback) callback(false);
        });
        
        this.container.querySelector('#popupCancel').addEventListener('click', () => {
            this.close();
            if (callback) callback(false);
        });
        
        this.container.querySelector('#popupOk').addEventListener('click', () => {
            this.close();
            if (callback) callback(true);
        });
        
        // Open the popup
        this.open();
    },
    
    // Unshare confirmation popup
    showUnshareConfirmPopup(callback) {
        this.showConfirmPopup(
            'Unshare Note',
            'Are you sure you want to stop sharing this note? The current share link will no longer work.',
            'Unshare',
            'Cancel',
            callback
        );
    },

    open() {
        this.overlay.classList.add('active');
        this.isOpen = true;
        
        // Apply 3D animation effect to elements
        const animateElements = this.container.querySelectorAll('.popup-header, .popup-content, .popup-footer');
        animateElements.forEach(el => {
            el.style.animation = 'float 6s ease-in-out infinite';
            // Randomize animation delay for more natural feel
            el.style.animationDelay = `${Math.random() * 0.5}s`;
        });
    },

    close() {
        this.overlay.classList.remove('active');
        this.isOpen = false;
        this.currentCallback = null;
        
        // Clean up container after animation
        setTimeout(() => {
            if (!this.isOpen) {
                this.container.innerHTML = '';
            }
        }, 500);
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => PopupManager.init()); 