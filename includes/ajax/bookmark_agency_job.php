<?php
// includes/ajax/bookmark_agency_job.php
session_start();
require_once '../config.php';
require_once '../db_connect.php';
require_once '../functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$jobListingId = $_POST['job_listing_id'] ?? '';
$action = $_POST['action'] ?? '';
$userId = $_SESSION['user_id'];

if (!$jobListingId || !$action) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

$conn = getDBConnection();

try {
    if ($action === 'add') {
        // Get job details
        $stmt = $conn->prepare("
            SELECT j.*, a.name as agency_name 
            FROM job_listings j
            JOIN agencies a ON j.agency_id = a.id
            WHERE j.id = ?
        ");
        $stmt->execute([$jobListingId]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$job) {
            throw new Exception('Job not found');
        }
        
        $stmt = $conn->prepare("
            INSERT INTO bookmarks (user_id, job_listing_id, job_title, company_name, job_description) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $jobListingId,
            $job['job_title'],
            $job['agency_name'],
            $job['job_description']
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Job bookmarked']);
    } elseif ($action === 'remove') {
        $stmt = $conn->prepare("DELETE FROM bookmarks WHERE user_id = ? AND job_listing_id = ?");
        $stmt->execute([$userId, $jobListingId]);
        
        echo json_encode(['success' => true, 'message' => 'Bookmark removed']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>