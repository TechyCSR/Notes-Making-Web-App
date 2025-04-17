// Notes management
const NotesManager = {
    init() {
        this.initQuill();
        this.initTags();
        this.bindEvents();
    },

    initQuill() {
        const quillEditor = document.getElementById('quillEditor');
        if (quillEditor) {
            this.quill = new Quill('#quillEditor', {
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
        }
    },

    initTags() {
        this.selectedTags = new Set();
        this.updateTagsList();
    },

    updateTagsList() {
        const tagsList = document.querySelector('.tags-list');
        if (!tagsList) return;

        // Collect all unique tags and their counts
        const tagCounts = {};
        document.querySelectorAll('.note-card').forEach(card => {
            try {
                const tagsData = card.getAttribute('data-tags');
                if (!tagsData) return;
                
                const tags = JSON.parse(tagsData);
                if (Array.isArray(tags)) {
                    tags.forEach(tag => {
                        if (tag && typeof tag === 'string') {
                            tagCounts[tag] = (tagCounts[tag] || 0) + 1;
                        }
                    });
                }
            } catch (e) {
                console.error('Error parsing tags:', e);
            }
        });

        // Sort tags by count (descending)
        const sortedTags = Object.entries(tagCounts)
            .sort(([,a], [,b]) => b - a)
            .map(([tag, count]) => ({ tag, count }));

        // Update tags count in header
        const tagsHeader = document.querySelector('.tags-filter h3');
        if (tagsHeader) {
            tagsHeader.innerHTML = `Tags Search <span>${Object.keys(tagCounts).length} tags</span>`;
        }

        // Create tag elements
        if (sortedTags.length === 0) {
            tagsList.innerHTML = '<div class="no-tags">No tags found</div>';
            return;
        }

        tagsList.innerHTML = sortedTags.map(({ tag, count }) => `
            <div class="tag ${this.selectedTags.has(tag) ? 'active' : ''}" data-tag="${tag}">
                ${tag}
                <span class="count">${count}</span>
            </div>
        `).join('');

        // Add click handlers to tags
        tagsList.querySelectorAll('.tag').forEach(tagEl => {
            tagEl.addEventListener('click', () => this.toggleTag(tagEl.dataset.tag));
        });

        // Initial filtering
        this.filterNotesByTags();
    },

    toggleTag(tag) {
        if (this.selectedTags.has(tag)) {
            this.selectedTags.delete(tag);
        } else {
            this.selectedTags.add(tag);
        }
        this.updateTagsList();
    },

    filterNotesByTags() {
        const notes = document.querySelectorAll('.note-card');
        if (this.selectedTags.size === 0) {
            notes.forEach(note => note.style.display = 'flex');
            return;
        }

        notes.forEach(note => {
            const noteTags = JSON.parse(note.dataset.tags || '[]');
            const hasSelectedTag = [...this.selectedTags].some(tag => noteTags.includes(tag));
            note.style.display = hasSelectedTag ? 'flex' : 'none';
        });
    },

    hideNoteEditor() {
        const noteEditor = document.getElementById('noteEditor');
        if (noteEditor) {
            noteEditor.style.display = 'none';
            const form = document.getElementById('noteForm');
            if (form) form.reset();
            if (this.quill) this.quill.setContents([]);
        }
    },

    showNewNoteEditor() {
        const noteEditor = document.getElementById('noteEditor');
        if (noteEditor) {
            noteEditor.style.display = 'block';
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
            if (this.quill) this.quill.setContents([]);
        }
    },

    handleNoteSubmit(e) {
        const quillContent = document.getElementById('quillContent');
        if (quillContent && this.quill) {
            quillContent.value = this.quill.root.innerHTML;
        }
    },

    handleSearch(e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('.note-card').forEach(card => {
            if (card.style.display === 'none' && this.selectedTags.size > 0) return;
            
            const title = card.querySelector('h3')?.textContent.toLowerCase() || '';
            const content = card.querySelector('.note-content')?.textContent.toLowerCase() || '';
            card.style.display = (title.includes(searchTerm) || content.includes(searchTerm)) ? 'flex' : 'none';
        });
    },

    handleNoteEdit(button) {
        const noteId = button.dataset.noteId;
        const noteTitle = button.dataset.noteTitle;
        const noteContent = button.dataset.noteContent;
        let noteTags = [];
        
        try {
            const tagData = button.dataset.noteTags;
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
            if (this.quill) this.quill.root.innerHTML = noteContent;

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
    },

    bindEvents() {
        console.log('Binding events...');
        // Make hideNoteEditor globally accessible
        window.hideNoteEditor = () => this.hideNoteEditor();

        // New Note button
        const newNoteBtn = document.getElementById('newNoteBtn');
        if (newNoteBtn) {
            newNoteBtn.addEventListener('click', () => this.showNewNoteEditor());
        }

        // Note form submission
        const noteForm = document.getElementById('noteForm');
        if (noteForm) {
            noteForm.addEventListener('submit', (e) => this.handleNoteSubmit(e));
        }

        // Search functionality
        const searchInput = document.getElementById('searchNotes');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.handleSearch(e));
        }

        // Edit note buttons
        document.querySelectorAll('.edit-note').forEach(button => {
            button.addEventListener('click', () => this.handleNoteEdit(button));
        });
        
        // Share note buttons
        console.log('Share buttons found:', document.querySelectorAll('.share-note').length);
        document.querySelectorAll('.share-note').forEach(button => {
            console.log('Attaching share event to button:', button.dataset.noteId);
            button.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('Share button clicked directly');
                this.handleNoteShare(button);
            });
        });
        
        // Delete note buttons
        document.querySelectorAll('.delete-note-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const form = button.closest('.delete-note-form');
                if (form) {
                    this.handleNoteDelete(form);
                }
            });
        });
    },
    
    handleNoteShare(button) {
        console.log('Share button clicked:', button);
        const noteId = button.dataset.noteId;
        const isShared = button.dataset.isShared === 'true';
        
        console.log('Note ID:', noteId, 'Is shared:', isShared);
        
        if (isShared) {
            // Show 3D popup with options instead of confirm dialog
            PopupManager.showConfirmPopup(
                'Note Already Shared',
                'This note is already shared. What would you like to do?',
                'Copy Link',
                'Unshare Note',
                (result) => {
                    if (result) {
                        // User clicked "Copy Link"
                        this.getShareLink(noteId);
                    } else {
                        // User clicked "Unshare Note"
                        PopupManager.showUnshareConfirmPopup((confirmed) => {
                            if (confirmed) {
                                this.unshareNote(noteId);
                            }
                        });
                    }
                }
            );
        } else {
            // Note is not shared yet, show 3D confirmation popup
            PopupManager.showConfirmPopup(
                'Share Note',
                'Would you like to create a public share link for this note?\n\nAnyone with the link will be able to view this note.',
                'Share Note',
                'Cancel',
                (result) => {
                    if (result) {
                        this.shareNote(noteId);
                    }
                }
            );
        }
    },
    
    shareNote(noteId) {
        console.log('Sharing note:', noteId);
        const formData = new FormData();
        formData.append('note_id', noteId);
        
        fetch('../api/share_note.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Share API response:', data);
            if (data.success) {
                // Update the button state
                const shareButton = document.querySelector(`.share-note[data-note-id="${noteId}"]`);
                if (shareButton) {
                    shareButton.dataset.isShared = 'true';
                    // Update icon to show link instead of share
                    const icon = shareButton.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-share-alt');
                        icon.classList.add('fa-link');
                    }
                }
                
                // Show 3D popup with share URL instead of alert
                PopupManager.showSharePopup('Note Shared Successfully', data.share_url);
            } else {
                PopupManager.showConfirmPopup('Error', 'Error sharing note: ' + (data.error || 'Unknown error'), 'OK');
            }
        })
        .catch(error => {
            console.error('Share error:', error);
            PopupManager.showConfirmPopup('Error', 'Failed to share note. Please try again.', 'OK');
        });
    },
    
    getShareLink(noteId) {
        const formData = new FormData();
        formData.append('note_id', noteId);
        
        fetch('../api/share_note.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show 3D popup with share URL instead of alert
                PopupManager.showSharePopup('Share Link', data.share_url);
            } else {
                PopupManager.showConfirmPopup('Error', 'Error getting share link: ' + (data.error || 'Unknown error'), 'OK');
            }
        })
        .catch(error => {
            console.error('Share error:', error);
            PopupManager.showConfirmPopup('Error', 'Failed to get share link. Please try again.', 'OK');
        });
    },
    
    unshareNote(noteId) {
        console.log('Unsharing note:', noteId);
        const formData = new FormData();
        formData.append('note_id', noteId);
        
        fetch('../api/unshare_note.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Unshare API response:', data);
            if (data.success) {
                // Update the button state
                const shareButton = document.querySelector(`.share-note[data-note-id="${noteId}"]`);
                if (shareButton) {
                    shareButton.dataset.isShared = 'false';
                    // Update icon to show share instead of link
                    const icon = shareButton.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-link');
                        icon.classList.add('fa-share-alt');
                    }
                }
                
                // Show success message with 3D popup
                PopupManager.showConfirmPopup('Success', 'Note is no longer shared.', 'OK');
            } else {
                PopupManager.showConfirmPopup('Error', 'Error unsharing note: ' + (data.error || 'Unknown error'), 'OK');
            }
        })
        .catch(error => {
            console.error('Unshare error:', error);
            PopupManager.showConfirmPopup('Error', 'Failed to unshare note. Please try again.', 'OK');
        });
    },
    
    handleNoteDelete(form) {
        PopupManager.showConfirmPopup(
            'Delete Note',
            'Are you sure you want to delete this note? This action cannot be undone.',
            'Delete',
            'Cancel',
            (result) => {
                if (result) {
                    // User confirmed deletion, add the delete_note input and submit the form
                    const deleteInput = document.createElement('input');
                    deleteInput.type = 'hidden';
                    deleteInput.name = 'delete_note';
                    deleteInput.value = '1';
                    form.appendChild(deleteInput);
                    form.submit();
                }
            }
        );
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => NotesManager.init()); 