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
        $agencyId = (int)$_POST['agency_id'] ?? 0;
        
        if (!$agencyId) {
            throw new Exception('Invalid agency ID');
        }
        
        // Accept connection request
        if ($action === 'accept') {
            $stmt = $conn->prepare("
                UPDATE agency_connections 
                SET status = 'accepted' 
                WHERE agency_id = ? AND user_id = ? AND status = 'pending'
            ");
            $stmt->execute([$agencyId, $userId]);
            
            if ($stmt->rowCount() > 0) {
                $response = ['success' => true, 'message' => 'Connection request accepted'];
            } else {
                $response = ['success' => false, 'message' => 'Connection request not found'];
            }
        }
        
        // Reject connection request
        elseif ($action === 'reject') {
            $stmt = $conn->prepare("
                DELETE FROM agency_connections 
                WHERE agency_id = ? AND user_id = ? AND status = 'pending'
            ");
            $stmt->execute([$agencyId, $userId]);
            
            if ($stmt->rowCount() > 0) {
                $response = ['success' => true, 'message' => 'Connection request rejected'];
            } else {
                $response = ['success' => false, 'message' => 'Connection request not found'];
            }
        }
        
        // Initiate connection from user side
        elseif ($action === 'connect') {
            // Check if connection already exists
            $stmt = $conn->prepare("
                SELECT id, status FROM agency_connections 
                WHERE agency_id = ? AND user_id = ?
            ");
            $stmt->execute([$agencyId, $userId]);
            $existingConnection = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existingConnection) {
                // Create new connection request
                $stmt = $conn->prepare("INSERT INTO agency_connections (agency_id, user_id, status) VALUES (?, ?, 'pending')");
                $stmt->execute([$agencyId, $userId]);
                
                $response = ['success' => true, 'message' => 'Connection request sent'];
            } else {
                $response = ['success' => false, 'message' => 'Connection request already exists'];
            }
        }
        
        // Remove connection from user side
        elseif ($action === 'remove') {
            $stmt = $conn->prepare("
                DELETE FROM agency_connections 
                WHERE agency_id = ? AND user_id = ?
            ");
            $stmt->execute([$agencyId, $userId]);
            
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
$redirectUrl = $_SERVER['HTTP_REFERER'] ?? '../../pages/dashboard.php';
header('Location: ' . $redirectUrl);
exit();
?>