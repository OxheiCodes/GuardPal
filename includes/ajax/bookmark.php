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

$jobId = $_POST['job_id'] ?? '';
$action = $_POST['action'] ?? '';
$userId = $_SESSION['user_id'];

if (!$jobId || !$action) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

$conn = getDBConnection();

try {
    if ($action === 'add') {
        // Get job details from API
        $jobResponse = getJobDetails($jobId);
        if (!$jobResponse || !isset($jobResponse['data'][0])) {
            throw new Exception('Job not found');
        }
        
        $job = $jobResponse['data'][0];
        
        $stmt = $conn->prepare("
            INSERT INTO bookmarks (user_id, job_id, job_title, company_name, job_description) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $jobId,
            $job['job_title'],
            $job['employer_name'],
            $job['job_description']
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Job bookmarked']);
    } elseif ($action === 'remove') {
        $stmt = $conn->prepare("DELETE FROM bookmarks WHERE user_id = ? AND job_id = ?");
        $stmt->execute([$userId, $jobId]);
        
        echo json_encode(['success' => true, 'message' => 'Bookmark removed']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>