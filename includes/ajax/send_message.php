<?php
session_start();
require_once '../config.php';
require_once '../db_connect.php';
require_once '../functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$receiverId = $_POST['receiver_id'] ?? '';
$message = $_POST['message'] ?? '';
$senderId = $_SESSION['user_id'];

if (!$receiverId || !$message) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

$conn = getDBConnection();

try {
    // Check if chat room exists, if not create one
    $stmt = $conn->prepare("
        SELECT id FROM chat_rooms 
        WHERE (user1_id = ? AND user2_id = ?) 
           OR (user1_id = ? AND user2_id = ?)
    ");
    $stmt->execute([$senderId, $receiverId, $receiverId, $senderId]);
    $chatRoom = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$chatRoom) {
        $stmt = $conn->prepare("INSERT INTO chat_rooms (user1_id, user2_id) VALUES (?, ?)");
        $stmt->execute([$senderId, $receiverId]);
    }
    
    // Insert message
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$senderId, $receiverId, $message]);
    
    echo json_encode(['success' => true, 'message' => 'Message sent']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>