<?php
$pageTitle = 'Dashboard - GuardPal';
require_once 'header.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireLogin();

$conn = getDBConnection();

// Get user profile completeness
$stmt = $conn->prepare("
    SELECT 
        CASE 
            WHEN profile_image IS NOT NULL THEN 1 ELSE 0 
        END as has_profile_image,
        CASE 
            WHEN job_title IS NOT NULL AND job_title != '' THEN 1 ELSE 0 
        END as has_job_title,
        CASE 
            WHEN bio IS NOT NULL AND bio != '' THEN 1 ELSE 0 
        END as has_bio,
        CASE 
            WHEN location IS NOT NULL AND location != '' THEN 1 ELSE 0 
        END as has_location,
        CASE 
            WHEN years_experience > 0 THEN 1 ELSE 0 
        END as has_experience
    FROM users
    WHERE id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$profileData = $stmt->fetch(PDO::FETCH_ASSOC);

// Calculate profile completeness percentage
$profileCompleteness = 0;
if ($profileData) {
    $totalFields = 5; // Total number of profile fields we're checking
    $completedFields = 
        $profileData['has_profile_image'] + 
        $profileData['has_job_title'] + 
        $profileData['has_bio'] + 
        $profileData['has_location'] + 
        $profileData['has_experience'];
    
    $profileCompleteness = ($completedFields / $totalFields) * 100;
}

// Get certification count
$stmt = $conn->prepare("SELECT COUNT(*) FROM certifications WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$certificationCount = $stmt->fetchColumn();

// Get skills count
$stmt = $conn->prepare("SELECT COUNT(*) FROM skills WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$skillsCount = $stmt->fetchColumn();

// Get bookmark count
$stmt = $conn->prepare("SELECT COUNT(*) FROM bookmarks WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$bookmarkCount = $stmt->fetchColumn();

// Get connection count
$stmt = $conn->prepare("
    SELECT COUNT(*) FROM connections 
    WHERE (requester_id = ? OR receiver_id = ?) 
    AND status = 'accepted'
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$connectionCount = $stmt->fetchColumn();

// Get message count
$stmt = $conn->prepare("
    SELECT COUNT(*) FROM messages 
    WHERE receiver_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
");
$stmt->execute([$_SESSION['user_id']]);
$messageCount = $stmt->fetchColumn();

// Get recent jobs from bookmarks
$stmt = $conn->prepare("
    SELECT * FROM bookmarks 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 3
");
$stmt->execute([$_SESSION['user_id']]);
$recentJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h1 class="mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
    
    <?php if ($profileCompleteness < 100): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <strong>Complete your profile!</strong> A complete profile increases your chances of being noticed by employers.
            <div class="progress mt-2" style="height: 10px;">
                <div class="progress-bar" role="progressbar" style="width: <?php echo $profileCompleteness; ?>%" 
                     aria-valuenow="<?php echo $profileCompleteness; ?>" aria-valuemin="0" aria-valuemax="100">
                    <?php echo round($profileCompleteness); ?>%
                </div>
            </div>
            <a href="edit-profile.php" class="btn btn-sm btn-primary mt-2">Complete Profile</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card dashboard-card">
                <div class="card-body text-center">
                    <i class="fas fa-search fa-3x mb-3 text-primary"></i>
                    <h5 class="card-title">Search Jobs</h5>
                    <p class="card-text">Find security jobs that match your skills</p>
                    <a href="search.php" class="btn btn-primary">Start Search</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card dashboard-card">
                <div class="card-body text-center">
                    <i class="fas fa-bookmark fa-3x mb-3 text-warning"></i>
                    <h5 class="card-title">Bookmarks</h5>
                    <p class="card-text">You have <?php echo $bookmarkCount; ?> saved jobs</p>
                    <a href="bookmarks.php" class="btn btn-warning">View Bookmarks</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card dashboard-card">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x mb-3 text-info"></i>
                    <h5 class="card-title">Network</h5>
                    <p class="card-text"><?php echo $connectionCount; ?> professional connections</p>
                    <a href="connections.php" class="btn btn-info">View Network</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
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
        <div class="col-md-8">
            <h3>Recent Jobs</h3>
            <?php if (!empty($recentJobs)): ?>
                <div class="row">
                    <?php foreach ($recentJobs as $job): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card job-card card-hover">
                                <div class="card-body job-card-body">
                                    <h5 class="job-title"><?php echo htmlspecialchars($job['job_title']); ?></h5>
                                    <p class="company-name mb-2"><?php echo htmlspecialchars($job['company_name']); ?></p>
                                    <p class="card-text small">
                                        <?php echo htmlspecialchars(substr($job['job_description'], 0, 100)) . '...'; ?>
                                    </p>
                                    <a href="job-details.php?id=<?php echo urlencode($job['job_id']); ?>" 
                                       class="btn btn-outline-primary btn-sm">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-light">
                    <p>You haven't saved any jobs yet. <a href="search.php">Start searching</a> to find jobs that match your skills.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-4">
            <h3>Profile Stats</h3>
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Profile Completeness</span>
                        <span class="badge bg-<?php 
                            echo $profileCompleteness >= 80 ? 'success' : 
                                ($profileCompleteness >= 40 ? 'warning' : 'danger'); 
                        ?>"><?php echo round($profileCompleteness); ?>%</span>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-<?php 
                            echo $profileCompleteness >= 80 ? 'success' : 
                                ($profileCompleteness >= 40 ? 'warning' : 'danger'); 
                        ?>" role="progressbar" style="width: <?php echo $profileCompleteness; ?>%"></div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Skills</span>
                        <span class="badge bg-primary"><?php echo $skillsCount; ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Certifications</span>
                        <span class="badge bg-info"><?php echo $certificationCount; ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Network Connections</span>
                        <span class="badge bg-secondary"><?php echo $connectionCount; ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Saved Jobs</span>
                        <span class="badge bg-warning"><?php echo $bookmarkCount; ?></span>
                    </div>
                    
                    <div class="mt-3">
                        <a href="profile.php" class="btn btn-outline-primary btn-sm">View Profile</a>
                        <a href="edit-profile.php" class="btn btn-outline-secondary btn-sm">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>