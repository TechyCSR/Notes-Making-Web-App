<?php
require_once '../config/config.php';
require_once '../models/User.php';
session_start();

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Get POST data
$postData = file_get_contents('php://input');
parse_str($postData, $data);

// Check if new_password is set
if (!isset($data['new_password']) || empty($data['new_password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'New password is required']);
    exit;
}

$newPassword = $data['new_password'];

// Validate password
if (strlen($newPassword) < 8) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
    exit;
}

try {
    $user = new User();
    $userId = $_SESSION['user']['id'];
    
    // Update the user's password
    $result = $user->updatePassword($userId, $newPassword);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update password: ' . $user->getLastError()]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
} 