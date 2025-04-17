// Initialize Quill editor
const quill = new Quill('#editor', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['link', 'image'],
            ['clean']
        ]
    }
});

// Auto-save functionality
let autoSaveTimeout;
const autoSave = () => {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(() => {
        saveNote();
    }, 2000);
};

quill.on('text-change', autoSave);

// Save note function
const saveNote = async () => {
    const title = document.getElementById('note-title').value;
    const content = quill.getContents();
    const tags = Array.from(document.querySelectorAll('.tag')).map(tag => tag.textContent);
    
    try {
        const response = await fetch('/api/notes/save.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                title,
                content,
                tags
            })
        });
        
        if (!response.ok) throw new Error('Failed to save note');
        
        showNotification('Note saved successfully');
    } catch (error) {
        showNotification('Error saving note', 'error');
    }
};

// Theme Toggle Functionality
function initTheme() {
    const theme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', theme);
    updateThemeIcon(theme);
}

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeIcon(newTheme);
}

function updateThemeIcon(theme) {
    const icon = document.querySelector('.theme-toggle i');
    if (icon) {
        icon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
    }
}

// Main initialization
document.addEventListener('DOMContentLoaded', function() {
    // Initialize theme
    initTheme();
    
    // Initialize Quill editor
    const quill = new Quill('#quillEditor', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'header': 1 }, { 'header': 2 }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                ['link', 'image'],
                ['clean']
            ]
        },
        placeholder: 'Write your note here...'
    });

    // Theme toggle button
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }

    // Note editor functions
    function hideNoteEditor() {
        const noteEditor = document.getElementById('noteEditor');
        if (noteEditor) {
            noteEditor.style.display = 'none';
            const form = document.getElementById('noteForm');
            if (form) form.reset();
            if (quill) quill.setContents([]);
        }
    }
    window.hideNoteEditor = hideNoteEditor;

    // New Note button
    const newNoteBtn = document.getElementById('newNoteBtn');
    if (newNoteBtn) {
        newNoteBtn.addEventListener('click', function() {
            const noteEditor = document.getElementById('noteEditor');
            if (noteEditor) {
                noteEditor.style.display = 'block';
                noteEditor.style.zIndex = '9999'; // or any high value
                const form = document.getElementById('noteForm');
                if (form) {
                    form.reset();
                    const saveBtn = form.querySelector('.save-btn');
                    if (saveBtn) {
                        saveBtn.textContent = 'Save Note';
                        saveBtn.name = 'create_note';
                    }
                    const noteIdInput = form.querySelector('input[name="note_id"]');
                    if (noteIdInput) noteIdInput.remove();
                }
                if (quill) quill.setContents([]);
            }
        });
    }

    // Note form submission
    const noteForm = document.getElementById('noteForm');
    if (noteForm) {
        noteForm.addEventListener('submit', function(e) {
            const quillContent = document.getElementById('quillContent');
            if (quillContent && quill) {
                quillContent.value = quill.root.innerHTML;
            }
        });
    }

    // Search functionality
    const searchInput = document.getElementById('searchNotes');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.note-card').forEach(card => {
                const title = card.querySelector('h3')?.textContent.toLowerCase() || '';
                const content = card.querySelector('.note-content')?.textContent.toLowerCase() || '';
                card.style.display = (title.includes(searchTerm) || content.includes(searchTerm)) ? 'flex' : 'none';
            });
        });
    }

    // Edit note buttons
    document.querySelectorAll('.edit-note').forEach(button => {
        button.addEventListener('click', function() {
            const noteId = this.dataset.noteId;
            const noteTitle = this.dataset.noteTitle;
            const noteContent = this.dataset.noteContent;
            let noteTags = [];
            
            try {
                const tagData = this.dataset.noteTags;
                if (tagData) {
                    noteTags = JSON.parse(tagData);
                }
            } catch (e) {
                console.error('Error parsing tags:', e);
            }

            const noteEditor = document.getElementById('noteEditor');
            if (noteEditor) {
                noteEditor.style.display = 'block';
                
                const titleInput = document.querySelector('input[name="title"]');
                const tagsInput = document.querySelector('input[name="tags"]');
                
                if (titleInput) titleInput.value = noteTitle;
                if (tagsInput) tagsInput.value = noteTags.join(', ');
                if (quill) quill.root.innerHTML = noteContent;

                const form = document.getElementById('noteForm');
                if (form) {
                    let noteIdInput = form.querySelector('input[name="note_id"]');
                    if (!noteIdInput) {
                        noteIdInput = document.createElement('input');
                        noteIdInput.type = 'hidden';
                        noteIdInput.name = 'note_id';
                        form.appendChild(noteIdInput);
                    }
                    noteIdInput.value = noteId;

                    const saveBtn = form.querySelector('.save-btn');
                    if (saveBtn) {
                        saveBtn.textContent = 'Update Note';
                        saveBtn.name = 'update_note';
                    }
                }

                noteEditor.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // Profile popup functionality
    const profileBtn = document.getElementById('profileBtn');
    const profilePopup = document.getElementById('profilePopup');
    const closeProfileBtn = document.getElementById('closeProfileBtn');
    const closeProfileBtnBottom = document.getElementById('closeProfileBtnBottom');

    function closeProfilePopup() {
        if (profilePopup) {
            profilePopup.classList.remove('active');
            document.body.classList.remove('popup-open');
        }
    }

    if (profileBtn && profilePopup) {
        profileBtn.addEventListener('click', function() {
            profilePopup.classList.add('active');
            document.body.classList.add('popup-open');
        });

        if (closeProfileBtn) {
            closeProfileBtn.addEventListener('click', closeProfilePopup);
        }
        if (closeProfileBtnBottom) {
            closeProfileBtnBottom.addEventListener('click', closeProfilePopup);
        }

        profilePopup.addEventListener('click', function(e) {
            if (e.target === this) {
                closeProfilePopup();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && profilePopup.classList.contains('active')) {
                closeProfilePopup();
            }
        });
    }

    // Password visibility toggles
    document.querySelectorAll('.password-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            if (input) {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                const icon = this.querySelector('i');
                if (icon) {
                    icon.className = `fas fa-${type === 'password' ? 'eye' : 'eye-slash'}`;
                }
            }
        });
    });

    // Password update form
    const updatePasswordForm = document.getElementById('updatePasswordForm');
    const newPasswordInput = document.getElementById('newPassword');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const passwordUpdateMessage = document.getElementById('passwordUpdateMessage');

    if (updatePasswordForm && newPasswordInput && confirmPasswordInput && passwordUpdateMessage) {
        updatePasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (newPasswordInput.value !== confirmPasswordInput.value) {
                passwordUpdateMessage.textContent = 'Passwords do not match';
                passwordUpdateMessage.className = 'error-message';
                return;
            }
            
            if (newPasswordInput.value.length < 8) {
                passwordUpdateMessage.textContent = 'Password must be at least 8 characters long';
                passwordUpdateMessage.className = 'error-message';
                return;
            }
            
            const formData = new FormData();
            formData.append('update_password', true);
            formData.append('new_password', newPasswordInput.value);
            
            passwordUpdateMessage.textContent = 'Updating password...';
            passwordUpdateMessage.className = 'success-message';
            
            fetch('../views/update_password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    passwordUpdateMessage.textContent = 'Password updated successfully';
                    passwordUpdateMessage.className = 'success-message';
                    updatePasswordForm.reset();
                } else {
                    passwordUpdateMessage.textContent = data.message || 'Failed to update password';
                    passwordUpdateMessage.className = 'error-message';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                passwordUpdateMessage.textContent = 'An error occurred. Please try again.';
                passwordUpdateMessage.className = 'error-message';
            });
        });
    }
});

// Initialize 3D background
const initBackground = () => {
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
    const renderer = new THREE.WebGLRenderer({ alpha: true });
    
    renderer.setSize(window.innerWidth, window.innerHeight);
    document.querySelector('.dashboard-container').appendChild(renderer.domElement);
    
    // Add floating particles
    const particles = new THREE.Group();
    const particleGeometry = new THREE.SphereGeometry(0.1, 8, 8);
    const particleMaterial = new THREE.MeshPhongMaterial({
        color: 0x00ff00,
        transparent: true,
        opacity: 0.3
    });
    
    for (let i = 0; i < 50; i++) {
        const particle = new THREE.Mesh(particleGeometry, particleMaterial);
        particle.position.set(
            Math.random() * 20 - 10,
            Math.random() * 20 - 10,
            Math.random() * 20 - 10
        );
        particles.add(particle);
    }
    
    scene.add(particles);
    
    // Add lighting
    const light = new THREE.PointLight(0xffffff, 1, 100);
    light.position.set(10, 10, 10);
    scene.add(light);
    
    camera.position.z = 5;
    
    const animate = () => {
        requestAnimationFrame(animate);
        particles.rotation.y += 0.001;
        renderer.render(scene, camera);
    };
    
    animate();
};

// Initialize the application
document.addEventListener('DOMContentLoaded', () => {
    initBackground();
    
    // Load saved theme
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
    } else {
        document.body.classList.remove('dark-mode');
    }
}); 