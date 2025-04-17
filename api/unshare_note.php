<?php
require_once '../config/config.php';
require_once '../models/Note.php';
session_start();

// Ensure request is POST and user is logged in
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get note ID from request
$noteId = isset($_POST['note_id']) ? (int)$_POST['note_id'] : 0;
$userId = $_SESSION['user']['id'];

if ($noteId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid note ID']);
    exit;
}

// Initialize note model
$note = new Note();

// Check if the note belongs to the current user
$noteData = $note->findById($noteId, $userId);
if (!$noteData) {
    http_response_code(403);
    echo json_encode(['error' => 'You do not have permission to unshare this note']);
    exit;
}

// Remove share token
$success = $note->removeShareToken($noteId, $userId);

if (!$success) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to unshare note: ' . $note->getLastError()]);
    exit;
}

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Note unshared successfully'
]);
exit; 