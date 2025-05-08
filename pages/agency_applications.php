<?php
$pageTitle = 'All Applications - GuardPal';
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

// Get all applications for agency jobs
$stmt = $conn->prepare("
    SELECT b.*, 
           j.id as job_id, 
           j.job_title, 
           j.job_location, 
           j.is_active,
           u.id as user_id, 
           u.full_name, 
           u.job_title as user_job_title, 
           u.profile_image, 
           u.location as user_location
    FROM bookmarks b
    JOIN job_listings j ON b.job_listing_id = j.id
    JOIN users u ON b.user_id = u.id
    WHERE j.agency_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$agencyId]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group applications by job
$jobApplications = [];
foreach ($applications as $application) {
    $jobId = $application['job_id'];
    if (!isset($jobApplications[$jobId])) {
        $jobApplications[$jobId] = [
            'job_id' => $jobId,
            'job_title' => $application['job_title'],
            'job_location' => $application['job_location'],
            'is_active' => $application['is_active'],
            'applications' => []
        ];
    }
    $jobApplications[$jobId]['applications'][] = $application;
}
?>

<div class="container mt-4">
    <h2 class="mb-4">All Job Applications</h2>
    
    <?php if (empty($applications)): ?>
        <div class="alert alert-info">
            <p class="mb-0">You haven't received any applications for your job listings yet.</p>
        </div>
        <div class="card">
            <div class="card-body text-center p-5">
                <div class="mb-3">
                    <i class="fas fa-file-alt fa-4x text-muted"></i>
                </div>
                <h4>No Applications Yet</h4>
                <p class="text-muted mb-4">Create or promote your job listings to receive applications from security professionals</p>
                <a href="agency_create_job.php" class="btn btn-primary me-2">Create New Job</a>
                <a href="agency_jobs.php" class="btn btn-outline-secondary">Manage Jobs</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Applications Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Location</th>
                                        <th>Applications</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($jobApplications as $job): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($job['job_title']); ?></td>
                                            <td><?php echo htmlspecialchars($job['job_location']); ?></td>
                                            <td><span class="badge bg-primary"><?php echo count($job['applications']); ?></span></td>
                                            <td>
                                                <span class="badge bg-<?php echo $job['is_active'] ? 'success' : 'danger'; ?>">
                                                    <?php echo $job['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="agency_job_applications.php?id=<?php echo $job['job_id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye me-1"></i> View Applications
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Applications</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php 
                            // Show only the 6 most recent applications
                            $recentApplications = array_slice($applications, 0, 6); 
                            
                            foreach ($recentApplications as $application): 
                            ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0"><?php echo htmlspecialchars($application['job_title']); ?></h6>
                                                <span class="badge bg-<?php echo $application['is_active'] ? 'success' : 'danger'; ?>">
                                                    <?php echo $application['is_active'] ? 'Active Job' : 'Inactive Job'; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex mb-3">
                                                <img src="<?php echo $application['profile_image'] ?: 'https://placehold.co/80x80?text=User'; ?>" 
                                                     alt="Profile" class="rounded-circle me-3" style="width: 80px; height: 80px; object-fit: cover;">
                                                <div>
                                                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($application['full_name']); ?></h5>
                                                    <p class="text-muted mb-1"><?php echo htmlspecialchars($application['user_job_title'] ?: 'Security Professional'); ?></p>
                                                    <?php if ($application['user_location']): ?>
                                                        <p class="mb-1 small">
                                                            <i class="fas fa-map-marker-alt me-1"></i> 
                                                            <?php echo htmlspecialchars($application['user_location']); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <p class="mb-1 small">
                                                    <i class="fas fa-calendar me-1"></i> 
                                                    Applied: <?php echo date('M j, Y', strtotime($application['created_at'])); ?>
                                                </p>
                                            </div>
                                            
                                            <div class="d-grid gap-2">
                                                <a href="profile.php?id=<?php echo $application['user_id']; ?>" class="btn btn-outline-primary">
                                                    <i class="fas fa-user me-1"></i> View Profile
                                                </a>
                                                <?php 
                                                // Check connection status
                                                $stmt = $conn->prepare("
                                                    SELECT status FROM agency_connections 
                                                    WHERE agency_id = ? AND user_id = ?
                                                ");
                                                $stmt->execute([$agencyId, $application['user_id']]);
                                                $connection = $stmt->fetch(PDO::FETCH_ASSOC);
                                                
                                                if (!$connection): 
                                                ?>
                                                    <form action="../includes/ajax/agency_connection.php" method="post">
                                                        <input type="hidden" name="user_id" value="<?php echo $application['user_id']; ?>">
                                                        <input type="hidden" name="action" value="connect">
                                                        <button type="submit" class="btn btn-outline-success w-100">
                                                            <i class="fas fa-user-plus me-1"></i> Connect
                                                        </button>
                                                    </form>
                                                <?php elseif ($connection['status'] === 'accepted'): ?>
                                                    <button class="btn btn-outline-success" disabled>
                                                        <i class="fas fa-check me-1"></i> Connected
                                                    </button>
                                                <?php elseif ($connection['status'] === 'pending'): ?>
                                                    <button class="btn btn-outline-secondary" disabled>
                                                        <i class="fas fa-clock me-1"></i> Connection Pending
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (count($applications) > 6): ?>
                            <div class="text-center mt-3">
                                <p>Showing 6 of <?php echo count($applications); ?> applications</p>
                                <p>View all applications for a specific job using the table above</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>