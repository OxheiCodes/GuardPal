<?php
$pageTitle = 'Dashboard - GuardPal';
require_once 'header.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireLogin();

$conn = getDBConnection();

// Get bookmark count
$stmt = $conn->prepare("SELECT COUNT(*) FROM bookmarks WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$bookmarkCount = $stmt->fetchColumn();

// Get message count
$stmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
$stmt->execute([$_SESSION['user_id']]);
$messageCount = $stmt->fetchColumn();
?>

<div class="container mt-4">
    <h1 class="mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card dashboard-card">
                <div class="card-body text-center">
                    <i class="fas fa-search fa-3x mb-3 text-primary"></i>
                    <h5 class="card-title">Search Jobs</h5>
                    <p class="card-text">Find security jobs that match your skills</p>
                    <a href="search.php" class="btn btn-primary">Start Search</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card dashboard-card">
                <div class="card-body text-center">
                    <i class="fas fa-bookmark fa-3x mb-3 text-warning"></i>
                    <h5 class="card-title">Bookmarks</h5>
                    <p class="card-text">You have <?php echo $bookmarkCount; ?> saved jobs</p>
                    <a href="bookmarks.php" class="btn btn-warning">View Bookmarks</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card dashboard-card">
                <div class="card-body text-center">
                    <i class="fas fa-comment fa-3x mb-3 text-success"></i>
                    <h5 class="card-title">Messages</h5>
                    <p class="card-text"><?php echo $messageCount; ?> new messages</p>
                    <a href="chat.php" class="btn btn-success">Open Chat</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <h3>Recent Job Searches</h3>
            <div class="card">
                <div class="card-body">
                    <p class="text-muted">Your recent searches will appear here</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>