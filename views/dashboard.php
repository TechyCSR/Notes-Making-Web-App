<?php
require_once '../config/config.php';
require_once '../models/Note.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Handle note operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $note = new Note();

        // For debugging
        error_log('POST data: ' . print_r($_POST, true));

        if (isset($_POST['create_note']) || isset($_POST['update_note'])) {
            $title = $_POST['title'];
            $content = $_POST['content'];
            
            // Handle tags - ensure proper JSON encoding
            if (!empty($_POST['tags'])) {
                $tagArray = array_map('trim', explode(',', $_POST['tags']));
                $tagArray = array_filter($tagArray, function($tag) { return !empty($tag); });
                $tags = json_encode(array_values($tagArray));
            } else {
                $tags = '[]';
            }
            
            error_log('Processing note with title: ' . $title . ', has ID? ' . (isset($_POST['note_id']) ? 'Yes' : 'No'));
            
            if (isset($_POST['note_id']) && !empty($_POST['note_id'])) {
                // Update existing note
                $noteId = $_POST['note_id'];
                error_log('Updating note ID: ' . $noteId);
                
                $result = $note->update(
                    $noteId,
                    $_SESSION['user']['id'],
                    $title,
                    $content,
                    $tags
                );
                
                if (!$result) {
                    $error = "Failed to update note: " . $note->getLastError();
                    error_log($error);
                } else {
                    $success = "Note updated successfully!";
                    error_log($success);
                }
            } else {
                // Create new note
                error_log('Creating new note');
                
                $result = $note->create(
                    $_SESSION['user']['id'],
                    $title,
                    $content,
                    $tags
                );
                
                if (!$result) {
                    $error = "Failed to create note: " . $note->getLastError();
                    error_log($error);
                } else {
                    $success = "Note created successfully!";
                    error_log($success);
                }
            }
        } elseif (isset($_POST['delete_note'])) {
            $result = $note->delete(
                $_POST['note_id'],
                $_SESSION['user']['id']
            );
            if (!$result) {
                $error = "Failed to delete note: " . $note->getLastError();
            } else {
                $success = "Note deleted successfully!";
            }
        }
    } catch (Exception $e) {
        $error = "Database error: " . $e->getMessage();
        error_log('Exception: ' . $e->getMessage());
    }
    
    // Redirect to refresh the page after successful operation
    if (!isset($error)) {
        header('Location: dashboard.php');
        exit;
    }
}

// Fetch user's notes
try {
    $note = new Note();
    $userNotes = $note->getAllByUser($_SESSION['user']['id']);
    if ($userNotes === false) {
        $error = "Failed to fetch notes: " . $note->getLastError();
        $userNotes = [];
    }
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
    $userNotes = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard/dashboard.css">
    <link rel="stylesheet" href="../assets/css/dashboard/profile.css">
    <link rel="stylesheet" href="../assets/css/popup.css">
</head>
<body class="dashboard-body">
    <nav class="navbar">
        <a href="dashboard.php" class="nav-brand">
            <div class="logo-icon">
                <i class="fas fa-book-open"></i>
            </div>
            <span><?php echo APP_NAME; ?></span>
        </a>
        <div class="nav-links">
            <span class="user-name">Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?></span>
            <button class="theme-toggle" id="themeToggle" title="Toggle theme">
                <i class="fas fa-moon"></i>
            </button>
            <button id="profileBtn" class="btn-icon profile-nav-btn" title="Your Profile">
                <i class="fas fa-user-circle"></i>
            </button>
            <a href="logout.php" class="btn btn-secondary">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <button id="newNoteBtn" class="btn btn-primary btn-block">
                <i class="fas fa-plus"></i> New Note
            </button>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchNotes" placeholder="Search notes...">
            </div>
            <div class="tags-filter">
                <h3>Tags<span>0 tags</span></h3>
                <div class="tags-list">
                    <!-- Tags will be populated by JavaScript -->
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <!-- Note Editor -->
            <div id="noteEditor" class="note-editor" style="display: none;">
                <form id="noteForm" method="POST">
                    <input type="text" name="title" placeholder="Note Title" required class="note-title">
                    <div id="quillEditor"></div>
                    <input style="width: 100%;" type="hidden" name="content" id="quillContent">
                    <!-- This hidden input will be added programmatically when editing -->
                    <!-- <input type="hidden" name="note_id" id="noteIdField"> -->
                    <div class="note-tags">
                        <input type="text" name="tags" placeholder="Add tags (comma-separated)" class="tags-input">
                    </div>
                    <div class="note-actions">
                        <button type="submit" name="create_note" class="btn btn-primary save-btn">Save Note</button>
                        <button type="button" class="btn btn-secondary" onclick="hideNoteEditor()">Cancel</button>
                        <div id="autoSaveStatus" class="auto-save-status"></div>
                    </div>
                </form>
            </div>

            <!-- Notes Grid -->
            <div class="notes-grid">
                <?php foreach ($userNotes as $note): ?>
                <div class="note-card" data-tags='<?php echo htmlspecialchars(json_encode(!empty($note['tags']) ? json_decode($note['tags'], true) : [])); ?>'>
                    <h3><?php echo htmlspecialchars($note['title']); ?></h3>
                    <div class="note-content">
                        <?php 
                        // Allow only safe HTML with images resized
                        $content = $note['content'];
                        // Make sure images are responsive
                        $content = preg_replace('/<img(.*?)>/i', '<img$1 style="max-width:100%; height:auto;">', $content);
                        echo $content; 
                        ?>
                    </div>
                    <div class="note-tags">
                        <?php 
                        $tags = !empty($note['tags']) ? json_decode($note['tags'], true) : [];
                        if (!empty($tags)): 
                            foreach ($tags as $tag): ?>
                                <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                            <?php endforeach;
                        endif; ?>
                    </div>
                    <div class="note-footer">
                        <span class="note-date">
                            <?php echo (new DateTime($note['created_at']))->format('M d, Y'); ?>
                        </span>
                        <div class="note-actions">
                            <button type="button" class="btn btn-primary btn-sm edit-note" 
                                    data-note-id="<?php echo $note['id']; ?>"
                                    data-note-title="<?php echo htmlspecialchars($note['title']); ?>"
                                    data-note-content="<?php echo htmlspecialchars($note['content']); ?>"
                                    data-note-tags='<?php echo htmlspecialchars(!empty($note['tags']) ? $note['tags'] : '[]'); ?>'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-info btn-sm share-note"
                                    data-note-id="<?php echo $note['id']; ?>"
                                    data-is-shared="<?php 
                                        $noteObj = new Note();
                                        $isShared = $noteObj->isNoteShared($note['id']);
                                        echo $isShared ? 'true' : 'false'; 
                                    ?>">
                                <i class="fas <?php echo $isShared ? 'fa-link' : 'fa-share-alt'; ?>"></i>
                            </button>
                            <form method="POST" style="display: inline;" class="delete-note-form">
                                <input type="hidden" name="note_id" value="<?php echo $note['id']; ?>">
                                <button type="button" class="btn btn-danger btn-sm delete-note-btn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
        </div>
        
    <!-- Profile Popup -->
    <div id="profilePopup" class="profile-popup">
        <div class="profile-card">
            <div class="profile-header">
                <h2>User Profile</h2>
                <button class="close-btn" id="closeProfileBtn"><i class="fas fa-times"></i></button>
            </div>
            
            <div class="profile-info">
                <div class="profile-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="profile-details">
                    <h3><?php echo htmlspecialchars($_SESSION['user']['name']); ?></h3>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($_SESSION['user']['email']); ?></p>
                    <p><i class="fas fa-sticky-note"></i> <?php echo count($userNotes); ?> Notes</p>
                </div>
            </div>
            
            <div class="profile-section">
                <h3>Update Password</h3>
                <div id="passwordUpdateMessage"></div>
                <form id="updatePasswordForm" class="profile-form">
                    <div class="form-group">
                        <label for="newPassword">New Password</label>
                        <div class="input-group">
                            <div class="i1">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input type="password" id="newPassword" name="newPassword" required minlength="8"
                                   placeholder="Enter new password">
                            <button type="button" class="password-toggle" tabindex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmPassword">Confirm New Password</label>
                        <div class="input-group">
                            <div class="i1">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input type="password" id="confirmPassword" name="confirmPassword" required minlength="8"
                                   placeholder="Confirm new password">
                            <button type="button" class="password-toggle i2" tabindex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            </div>
            
            <div class="profile-footer">
                <button class="btn btn-secondary" id="closeProfileBtnBottom">Close</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/profile.js"></script>
    <script src="../assets/js/popup.js"></script>
    <script src="../assets/js/notes.js"></script>
</body>
</html> 