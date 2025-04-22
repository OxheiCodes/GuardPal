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

$receiverId = $_GET['receiver_id'] ?? '';
$currentUserId = $_SESSION['user_id'];

if (!$receiverId) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

$conn = getDBConnection();

try {
    $stmt = $conn->prepare("
        SELECT m.*, u.username as sender_name
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE (m.sender_id = ? AND m.receiver_id = ?) 
           OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at ASC
    ");
    
    $stmt->execute([$currentUserId, $receiverId, $receiverId, $currentUserId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format messages
    $formattedMessages = array_map(function($message) use ($currentUserId) {
        return [
            'message' => $message['message'],
            'is_sent' => $message['sender_id'] == $currentUserId,
            'formatted_time' => date('M j, g:i a', strtotime($message['created_at']))
        ];
    }, $messages);
    
    echo json_encode(['success' => true, 'messages' => $formattedMessages]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>