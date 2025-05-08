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
        END as has_experience,
        CASE 
            WHEN sia_license_number IS NOT NULL AND sia_license_number != '' THEN 1 ELSE 0 
        END as has_sia_license
    FROM users
    WHERE id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$profileData = $stmt->fetch(PDO::FETCH_ASSOC);

// Calculate profile completeness percentage
$profileCompleteness = 0;
if ($profileData) {
    $totalFields = 6; // Total number of profile fields we're checking
    $completedFields = 
        $profileData['has_profile_image'] + 
        $profileData['has_job_title'] + 
        $profileData['has_bio'] + 
        $profileData['has_location'] + 
        $profileData['has_experience'] +
        $profileData['has_sia_license'];
    
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

// Get agency connection count
$stmt = $conn->prepare("
    SELECT COUNT(*) FROM agency_connections 
    WHERE user_id = ? AND status = 'accepted'
");
$stmt->execute([$_SESSION['user_id']]);
$agencyConnectionCount = $stmt->fetchColumn();

// Get message count
$stmt = $conn->prepare("
    SELECT COUNT(*) FROM messages 
    WHERE receiver_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
");
$stmt->execute([$_SESSION['user_id']]);
$messageCount = $stmt->fetchColumn();

// Get pending connection requests
$stmt = $conn->prepare("
    SELECT COUNT(*) FROM connections 
    WHERE receiver_id = ? AND status = 'pending'
");
$stmt->execute([$_SESSION['user_id']]);
$pendingRequestsCount = $stmt->fetchColumn();

// Get pending agency connection requests
$stmt = $conn->prepare("
    SELECT COUNT(*) FROM agency_connections 
    WHERE user_id = ? AND status = 'pending'
");
$stmt->execute([$_SESSION['user_id']]);
$pendingAgencyRequestsCount = $stmt->fetchColumn();

// Get recent jobs from bookmarks
$stmt = $conn->prepare("
    SELECT * FROM bookmarks 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 3
");
$stmt->execute([$_SESSION['user_id']]);
$recentJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent agency jobs
$stmt = $conn->prepare("
    SELECT j.*, a.name as agency_name, a.logo_image
    FROM job_listings j
    JOIN agencies a ON j.agency_id = a.id
    JOIN agency_connections ac ON j.agency_id = ac.agency_id
    WHERE ac.user_id = ? AND ac.status = 'accepted' AND j.is_active = 1
    ORDER BY j.created_at DESC
    LIMIT 3
");
$stmt->execute([$_SESSION['user_id']]);
$recentAgencyJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    
    <?php if ($pendingRequestsCount > 0 || $pendingAgencyRequestsCount > 0): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>New Connection Requests!</strong> You have 
            <?php if ($pendingRequestsCount > 0): ?>
                <?php echo $pendingRequestsCount; ?> pending professional connection <?php echo $pendingRequestsCount > 1 ? 'requests' : 'request'; ?>
            <?php endif; ?>
            <?php if ($pendingRequestsCount > 0 && $pendingAgencyRequestsCount > 0): ?> and <?php endif; ?>
            <?php if ($pendingAgencyRequestsCount > 0): ?>
                <?php echo $pendingAgencyRequestsCount; ?> pending agency connection <?php echo $pendingAgencyRequestsCount > 1 ? 'requests' : 'request'; ?>
            <?php endif; ?>.
            <a href="connections.php" class="btn btn-sm btn-primary ms-2">View Requests</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card dashboard-card">
                <div class="card-body text-center">
                    <i class="fas fa-search fa-3x mb-3 text-primary"></i>
                    <h5 class="card-title">Find Jobs</h5>
                    <p class="card-text">Search security jobs that match your skills</p>
                    <a href="search.php" class="btn btn-primary">Search Jobs</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card dashboard-card">
                <div class="card-body text-center">
                    <i class="fas fa-building fa-3x mb-3 text-success"></i>
                    <h5 class="card-title">Security Agencies</h5>
                    <p class="card-text">Connected with <?php echo $agencyConnectionCount; ?> agencies</p>
                    <a href="browse_agencies.php" class="btn btn-success">Browse Agencies</a>
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
                    <i class="fas fa-comment fa-3x mb-3 text-info"></i>
                    <h5 class="card-title">Messages</h5>
                    <p class="card-text"><?php echo $messageCount; ?> new messages</p>
                    <a href="chat.php" class="btn btn-info">Open Chat</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Your Network</h3>
                <a href="connections.php" class="btn btn-outline-primary btn-sm">View All</a>
            </div>
            
            <?php
            // Get recent connections
            $stmt = $conn->prepare("
                SELECT c.*, 
                       u.id as connection_id,
                       u.username, 
                       u.full_name, 
                       u.job_title,
                       u.profile_image,
                       u.location
                FROM connections c
                JOIN users u ON (
                    (c.requester_id = ? AND c.receiver_id = u.id) OR 
                    (c.receiver_id = ? AND c.requester_id = u.id)
                )
                WHERE c.status = 'accepted'
                ORDER BY c.updated_at DESC
                LIMIT 3
            ");
            $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
            $recentConnections = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            
            <?php if (!empty($recentConnections)): ?>
                <div class="row">
                    <?php foreach ($recentConnections as $connection): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <img src="<?php echo $connection['profile_image'] ?: 'https://placehold.co/100x100?text=Profile'; ?>" 
                                         alt="Profile" class="rounded-circle mb-3" style="width: 80px; height: 80px; object-fit: cover;">
                                    <h5 class="card-title"><?php echo htmlspecialchars($connection['full_name']); ?></h5>
                                    <p class="card-text text-muted"><?php echo htmlspecialchars($connection['job_title'] ?: 'Security Professional'); ?></p>
                                    <?php if ($connection['location']): ?>
                                        <p class="card-text small">
                                            <i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($connection['location']); ?>
                                        </p>
                                    <?php endif; ?>
                                    <div class="d-grid gap-2">
                                        <a href="profile.php?id=<?php echo $connection['connection_id']; ?>" class="btn btn-sm btn-outline-primary">View Profile</a>
                                        <a href="chat.php?with=<?php echo $connection['connection_id']; ?>" class="btn btn-sm btn-outline-success">Message</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-light">
                    <p>You haven't connected with any security professionals yet. Start building your network!</p>
                    <a href="find-professionals.php" class="btn btn-primary mt-2">Find Professionals</a>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($recentAgencyJobs)): ?>
                <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                    <h3>Agency Job Listings</h3>
                    <a href="browse_agencies.php" class="btn btn-outline-success btn-sm">View All Agencies</a>
                </div>
                <div class="row">
                    <?php foreach ($recentAgencyJobs as $job): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card job-card card-hover">
                                <div class="card-body job-card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="<?php echo $job['logo_image'] ?: 'https://placehold.co/50x50?text=Agency'; ?>" 
                                             alt="Agency" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                        <span class="company-name"><?php echo htmlspecialchars($job['agency_name']); ?></span>
                                    </div>
                                    <h5 class="job-title"><?php echo htmlspecialchars($job['job_title']); ?></h5>
                                    <div class="mb-2">
                                        <span class="badge badge-location me-2">
                                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['job_location'] ?: 'Remote'); ?>
                                        </span>
                                        <?php if ($job['job_type']): ?>
                                            <span class="badge badge-type">
                                                <i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($job['job_type']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="card-text small">
                                        <?php echo htmlspecialchars(substr($job['job_description'], 0, 100)) . '...'; ?>
                                    </p>
                                    <a href="view_job.php?id=<?php echo urlencode($job['id']); ?>" 
                                       class="btn btn-outline-primary btn-sm">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <h3 class="mt-4">Recent Jobs</h3>
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
                        <span>Agency Connections</span>
                        <span class="badge bg-success"><?php echo $agencyConnectionCount; ?></span>
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
            
            <?php if ($pendingRequestsCount > 0 || $pendingAgencyRequestsCount > 0): ?>
            <div class="card mt-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Connection Requests</h5>
                </div>
                <div class="card-body">
                    <?php if ($pendingRequestsCount > 0): ?>
                        <p>You have <?php echo $pendingRequestsCount; ?> pending professional connection <?php echo $pendingRequestsCount == 1 ? 'request' : 'requests'; ?>.</p>
                    <?php endif; ?>
                    <?php if ($pendingAgencyRequestsCount > 0): ?>
                        <p>You have <?php echo $pendingAgencyRequestsCount; ?> pending agency connection <?php echo $pendingAgencyRequestsCount == 1 ? 'request' : 'requests'; ?>.</p>
                    <?php endif; ?>
                    <a href="connections.php" class="btn btn-warning">View Requests</a>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Quick Links</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="search.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-search me-2"></i> Search Jobs
                    </a>
                    <a href="find-professionals.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-users me-2"></i> Find Professionals
                    </a>
                    <a href="browse_agencies.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-building me-2"></i> Browse Agencies
                    </a>
                    <a href="edit-skills.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-cogs me-2"></i> Update Skills
                    </a>
                    <a href="edit-certifications.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-certificate me-2"></i> Update Certifications
                    </a>
                    <a href="edit-experience.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-briefcase me-2"></i> Update Experience
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>