<?php
session_start();
require_once '../config.php';
require_once '../db_connect.php';
require_once '../functions.php';

// Function to check if agency is logged in
function requireAgencyLogin() {
    if (!isset($_SESSION['agency_id'])) {
        header('Location: ../../pages/agency_login.php');
        exit();
    }
}

requireAgencyLogin();

$agencyId = $_SESSION['agency_id'];
$conn = getDBConnection();
$response = ['success' => false, 'message' => 'Invalid action'];

try {
    // Process connection request actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        // Connect (send request)
        if ($action === 'connect' && isset($_POST['user_id'])) {
            $userId = (int)$_POST['user_id'];
            
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
        
        // Accept request
        elseif ($action === 'accept' && isset($_POST['user_id'])) {
            $userId = (int)$_POST['user_id'];
            
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
        
        // Reject request
        elseif ($action === 'reject' && isset($_POST['user_id'])) {
            $userId = (int)$_POST['user_id'];
            
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
        
        // Remove connection
        elseif ($action === 'remove' && isset($_POST['user_id'])) {
            $userId = (int)$_POST['user_id'];
            
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
$redirectUrl = $_SERVER['HTTP_REFERER'] ?? '../../pages/agency_dashboard.php';
header('Location: ' . $redirectUrl);
exit();
?>