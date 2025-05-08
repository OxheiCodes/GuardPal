<?php
$pageTitle = 'Agency Dashboard - GuardPal';
require_once 'header.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Function to check if agency is logged in
function requireAgencyLogin() {
    if (!isset($_SESSION['agency_id'])) {
        header('Location: agency_login.php');
        exit();
    }
}

requireAgencyLogin();

$agencyId = $_SESSION['agency_id'];
$conn = getDBConnection();

// Get agency details
$stmt = $conn->prepare("SELECT * FROM agencies WHERE id = ?");
$stmt->execute([$agencyId]);
$agencyData = $stmt->fetch(PDO::FETCH_ASSOC);

// Get active job listings count
$stmt = $conn->prepare("SELECT COUNT(*) FROM job_listings WHERE agency_id = ? AND is_active = TRUE");
$stmt->execute([$agencyId]);
$activeJobsCount = $stmt->fetchColumn();

// Get applicants count from bookmarks
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT b.user_id) 
    FROM bookmarks b
    JOIN job_listings j ON b.job_listing_id = j.id
    WHERE j.agency_id = ?
");
$stmt->execute([$agencyId]);
$applicantsCount = $stmt->fetchColumn();

// Get connections count
$stmt = $conn->prepare("
    SELECT COUNT(*) FROM agency_connections 
    WHERE agency_id = ? AND status = 'accepted'
");
$stmt->execute([$agencyId]);
$connectionsCount = $stmt->fetchColumn();

// Get recent job listings
$stmt = $conn->prepare("
    SELECT * FROM job_listings 
    WHERE agency_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute([$agencyId]);
$recentJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get pending connection requests
$stmt = $conn->prepare("
    SELECT ac.*, u.full_name, u.profile_image, u.job_title, u.location 
    FROM agency_connections ac
    JOIN users u ON ac.user_id = u.id
    WHERE ac.agency_id = ? AND ac.status = 'pending'
    ORDER BY ac.created_at DESC
    LIMIT 5
");
$stmt->execute([$agencyId]);
$pendingConnections = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h1 class="mb-4">Welcome, <?php echo htmlspecialchars($agencyData['name']); ?>!</h1>
    
    <div class="row mb-4">
        <div class="col-md-3 mb-4">
            <div class="card dashboard-card">
                <div class="card-body text-center">
                    <i class="fas fa-briefcase fa-3x mb-3 text-primary"></i>
                    <h5 class="card-title">Active Jobs</h5>
                    <p class="card-text">You have <?php echo $activeJobsCount; ?> active job listings</p>
                    <a href="agency_jobs.php" class="btn btn-primary">Manage Jobs</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card dashboard-card">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x mb-3 text-info"></i>
                    <h5 class="card-title">Connections</h5>
                    <p class="card-text">You have <?php echo $connectionsCount; ?> professional connections</p>
                    <a href="agency_connections.php" class="btn btn-info">View Connections</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card dashboard-card">
                <div class="card-body text-center">
                    <i class="fas fa-file-alt fa-3x mb-3 text-warning"></i>
                    <h5 class="card-title">Applications</h5>
                    <p class="card-text"><?php echo $applicantsCount; ?> professionals applied to your jobs</p>
                    <a href="agency_applications.php" class="btn btn-warning">View Applications</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card dashboard-card">
                <div class="card-body text-center">
                    <i class="fas fa-plus-circle fa-3x mb-3 text-success"></i>
                    <h5 class="card-title">Post New Job</h5>
                    <p class="card-text">Create a new job listing</p>
                    <a href="agency_create_job.php" class="btn btn-success">Create Job</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Job Listings -->
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Job Listings</h5>
                    <a href="agency_jobs.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentJobs)): ?>
                        <p class="text-muted">You haven't posted any jobs yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Location</th>
                                        <th>Posted Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentJobs as $job): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($job['job_title']); ?></td>
                                            <td><?php echo htmlspecialchars($job['job_location']); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($job['created_at'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $job['is_active'] ? 'success' : 'danger'; ?>">
                                                    <?php echo $job['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="agency_edit_job.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="agency_job_applications.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-users"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Agency Profile Information -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Agency Profile</h5>
                    <a href="agency_edit_profile.php" class="btn btn-sm btn-outline-primary">Edit Profile</a>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 text-center">
                            <img src="<?php echo $agencyData['logo_image'] ?: 'https://placehold.co/200x200?text=Agency'; ?>" 
                                 alt="Agency Logo" class="img-fluid rounded mb-3" style="max-width: 150px;">
                        </div>
                        <div class="col-md-8">
                            <h4><?php echo htmlspecialchars($agencyData['name']); ?></h4>
                            <p class="text-muted mb-1">
                                <i class="fas fa-globe me-2"></i>
                                <a href="<?php echo htmlspecialchars($agencyData['website']); ?>" target="_blank">
                                    <?php echo htmlspecialchars($agencyData['website'] ?: 'Website not specified'); ?>
                                </a>
                            </p>
                            <p class="text-muted mb-1">
                                <i class="fas fa-phone me-2"></i>
                                <?php echo htmlspecialchars($agencyData['phone'] ?: 'Phone not specified'); ?>
                            </p>
                            <p class="text-muted mb-1">
                                <i class="fas fa-envelope me-2"></i>
                                <?php echo htmlspecialchars($agencyData['email']); ?>
                            </p>
                            <?php if ($agencyData['address']): ?>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    <?php echo htmlspecialchars($agencyData['address']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($agencyData['description']): ?>
                        <div class="mt-3">
                            <h5>About the Agency</h5>
                            <p><?php echo nl2br(htmlspecialchars($agencyData['description'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Connection Requests -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Pending Connection Requests</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($pendingConnections)): ?>
                        <p class="text-muted">No pending connection requests.</p>
                    <?php else: ?>
                        <?php foreach ($pendingConnections as $connection): ?>
                            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                <img src="<?php echo $connection['profile_image'] ?: 'https://placehold.co/50x50?text=User'; ?>" 
                                     alt="Profile" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0"><?php echo htmlspecialchars($connection['full_name']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($connection['job_title'] ?: 'Security Professional'); ?></small>
                                </div>
                                <div class="d-flex">
                                    <form action="../includes/ajax/agency_connection.php" method="post" class="me-1">
                                        <input type="hidden" name="user_id" value="<?php echo $connection['user_id']; ?>">
                                        <input type="hidden" name="action" value="accept">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form action="../includes/ajax/agency_connection.php" method="post">
                                        <input type="hidden" name="user_id" value="<?php echo $connection['user_id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <a href="agency_connections.php" class="btn btn-warning btn-sm w-100">View All Requests</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Quick Links</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="agency_create_job.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-plus-circle me-2"></i> Create New Job
                    </a>
                    <a href="agency_find_professionals.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-search me-2"></i> Find Security Professionals
                    </a>
                    <a href="agency_applications.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-file-alt me-2"></i> View All Applications
                    </a>
                    <a href="agency_jobs.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-briefcase me-2"></i> Manage Job Listings
                    </a>
                    <a href="agency_connections.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-users me-2"></i> Manage Connections
                    </a>
                    <a href="agency_edit_profile.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-building me-2"></i> Update Agency Profile
                    </a>
                    <a href="agency_logout.php" class="list-group-item list-group-item-action text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>