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

// Create or get share token
$shareToken = $note->createShareToken($noteId, $userId);

if (!$shareToken) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create share token: ' . $note->getLastError()]);
    exit;
}

// Generate the full URL for sharing
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
           "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI'], 2);
$shareUrl = $baseUrl . '/views/shared_note.php?token=' . $shareToken;

// Return success with share URL
echo json_encode([
    'success' => true,
    'share_url' => $shareUrl,
    'token' => $shareToken
]);
exit; 