<?php
session_start();
require_once '../config.php';
require_once '../db_connect.php';
require_once '../functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../pages/login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$conn = getDBConnection();
$response = ['success' => false, 'message' => 'Invalid action'];

try {
    // Process connection request actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        // Connect (send request)
        if ($action === 'connect' && isset($_POST['receiver_id'])) {
            $receiverId = (int)$_POST['receiver_id'];
            
            // Check if connection already exists
            $stmt = $conn->prepare("
                SELECT id, status FROM connections 
                WHERE (requester_id = ? AND receiver_id = ?) 
                   OR (requester_id = ? AND receiver_id = ?)
            ");
            $stmt->execute([$userId, $receiverId, $receiverId, $userId]);
            $existingConnection = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existingConnection) {
                // Create new connection request
                $stmt = $conn->prepare("INSERT INTO connections (requester_id, receiver_id, status) VALUES (?, ?, 'pending')");
                $stmt->execute([$userId, $receiverId]);
                
                $response = ['success' => true, 'message' => 'Connection request sent'];
            } else {
                $response = ['success' => false, 'message' => 'Connection request already exists'];
            }
        }
        
        // Accept request
        elseif ($action === 'accept' && isset($_POST['requester_id'])) {
            $requesterId = (int)$_POST['requester_id'];
            
            $stmt = $conn->prepare("
                UPDATE connections 
                SET status = 'accepted' 
                WHERE requester_id = ? AND receiver_id = ? AND status = 'pending'
            ");
            $stmt->execute([$requesterId, $userId]);
            
            if ($stmt->rowCount() > 0) {
                $response = ['success' => true, 'message' => 'Connection request accepted'];
            } else {
                $response = ['success' => false, 'message' => 'Connection request not found'];
            }
        }
        
        // Reject request
        elseif ($action === 'reject' && isset($_POST['requester_id'])) {
            $requesterId = (int)$_POST['requester_id'];
            
            $stmt = $conn->prepare("
                UPDATE connections 
                SET status = 'rejected' 
                WHERE requester_id = ? AND receiver_id = ? AND status = 'pending'
            ");
            $stmt->execute([$requesterId, $userId]);
            
            if ($stmt->rowCount() > 0) {
                $response = ['success' => true, 'message' => 'Connection request rejected'];
            } else {
                $response = ['success' => false, 'message' => 'Connection request not found'];
            }
        }
        
        // Cancel request
        elseif ($action === 'cancel' && isset($_POST['receiver_id'])) {
            $receiverId = (int)$_POST['receiver_id'];
            
            $stmt = $conn->prepare("
                DELETE FROM connections 
                WHERE requester_id = ? AND receiver_id = ? AND status = 'pending'
            ");
            $stmt->execute([$userId, $receiverId]);
            
            if ($stmt->rowCount() > 0) {
                $response = ['success' => true, 'message' => 'Connection request canceled'];
            } else {
                $response = ['success' => false, 'message' => 'Connection request not found'];
            }
        }
        
        // Remove connection
        elseif ($action === 'remove' && isset($_POST['connection_id'])) {
            $connectionId = (int)$_POST['connection_id'];
            
            $stmt = $conn->prepare("
                DELETE FROM connections 
                WHERE (requester_id = ? AND receiver_id = ?) 
                   OR (requester_id = ? AND receiver_id = ?)
            ");
            $stmt->execute([$userId, $connectionId, $connectionId, $userId]);
            
            if ($stmt->rowCount() > 0) {
                $response = ['success' => true, 'message' => 'Connection removed'];
            } else {
                $response = ['success' => false, 'message' => 'Connection not found'];
            }
        }
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
}

// Redirect back to the previous page
$redirectUrl = $_SERVER['HTTP_REFERER'] ?? '../../pages/connections.php';
header('Location: ' . $redirectUrl);
exit();
?>