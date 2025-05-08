<?php
$pageTitle = 'Agency Profile - GuardPal';
require_once 'header.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireLogin();

$userId = $_SESSION['user_id'];
$conn = getDBConnection();

// Get agency ID from URL
$agencyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if agency exists
$stmt = $conn->prepare("SELECT * FROM agencies WHERE id = ?");
$stmt->execute([$agencyId]);
$agencyData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$agencyData) {
    header('Location: browse_agencies.php');
    exit();
}

// Get connection status
$stmt = $conn->prepare("
    SELECT status FROM agency_connections 
    WHERE agency_id = ? AND user_id = ?
");
$stmt->execute([$agencyId, $userId]);
$connection = $stmt->fetch(PDO::FETCH_ASSOC);
$connectionStatus = $connection ? $connection['status'] : null;

// Get active job listings
$stmt = $conn->prepare("
    SELECT j.*, 
           (SELECT COUNT(*) FROM bookmarks b WHERE b.job_listing_id = j.id) as application_count
    FROM job_listings j
    WHERE j.agency_id = ? AND j.is_active = 1
    ORDER BY j.created_at DESC
");
$stmt->execute([$agencyId]);
$jobListings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check which jobs the user has already applied to
foreach ($jobListings as $key => $job) {
    $stmt = $conn->prepare("
        SELECT 1 FROM bookmarks 
        WHERE job_listing_id = ? AND user_id = ?
    ");
    $stmt->execute([$job['id'], $userId]);
    $jobListings[$key]['user_applied'] = $stmt->fetchColumn() ? true : false;
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div class="d-flex">
                            <img src="<?php echo $agencyData['logo_image'] ?: 'https://placehold.co/150x150?text=Agency+Logo'; ?>" 
                                 alt="Agency Logo" class="rounded me-4" 
                                 style="width: 120px; height: 120px; object-fit: cover;">
                            <div>
                                <h2 class="mb-1"><?php echo htmlspecialchars($agencyData['name']); ?></h2>
                                
                                <?php if ($agencyData['website']): ?>
                                    <p class="mb-1">
                                        <i class="fas fa-globe me-2"></i>
                                        <a href="<?php echo htmlspecialchars($agencyData['website']); ?>" target="_blank">
                                            <?php echo htmlspecialchars($agencyData['website']); ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if ($agencyData['phone']): ?>
                                    <p class="mb-1">
                                        <i class="fas fa-phone me-2"></i>
                                        <?php echo htmlspecialchars($agencyData['phone']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if ($agencyData['address']): ?>
                                    <p class="mb-1">
                                        <i class="fas fa-map-marker-alt me-2"></i>
                                        <?php echo htmlspecialchars($agencyData['address']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($connectionStatus === 'accepted'): ?>
                            <span class="badge bg-success p-2">
                                <i class="fas fa-check me-1"></i> Connected
                            </span>
                        <?php elseif ($connectionStatus === 'pending'): ?>
                            <span class="badge bg-warning p-2">
                                <i class="fas fa-clock me-1"></i> Connection Pending
                            </span>
                        <?php else: ?>
                            <form action="../includes/ajax/user_agency_connection.php" method="post">
                                <input type="hidden" name="agency_id" value="<?php echo $agencyId; ?>">
                                <input type="hidden" name="action" value="connect">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-plus me-1"></i> Connect
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($agencyData['description']): ?>
                        <div class="mt-3">
                            <h5>About <?php echo htmlspecialchars($agencyData['name']); ?></h5>
                            <div class="mb-4">
                                <?php echo nl2br(htmlspecialchars($agencyData['description'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Job Listings (<?php echo count($jobListings); ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($jobListings)): ?>
                        <div class="alert alert-info">
                            <p class="mb-0">This agency doesn't have any active job listings at the moment.</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($jobListings as $job): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card job-card card-hover h-100">
                                        <div class="card-body job-card-body position-relative">
                                            <button class="btn btn-link bookmark-btn <?php echo $job['user_applied'] ? 'bookmarked' : ''; ?>" 
                                                    data-job-id="<?php echo htmlspecialchars($job['id']); ?>"
                                                    data-listing-type="agency">
                                                <i class="<?php echo $job['user_applied'] ? 'fas' : 'far'; ?> fa-bookmark fa-lg"></i>
                                            </button>
                                            
                                            <h5 class="job-title"><?php echo htmlspecialchars($job['job_title']); ?></h5>
                                            <p class="company-name mb-2"><?php echo htmlspecialchars($agencyData['name']); ?></p>
                                            
                                            <div class="mb-3">
                                                <span class="badge badge-location me-2">
                                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['job_location'] ?: 'Remote'); ?>
                                                </span>
                                                <?php if ($job['job_type']): ?>
                                                    <span class="badge badge-type">
                                                        <i class="fas fa-clock"></i> <?php echo htmlspecialchars($job['job_type']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <p class="card-text small">
                                                <?php echo htmlspecialchars(substr($job['job_description'], 0, 150)) . '...'; ?>
                                            </p>
                                            
                                            <a href="view_job.php?id=<?php echo urlencode($job['id']); ?>" 
                                               class="btn btn-primary mt-2">View Details</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Contact Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($agencyData['phone'] ?: 'Not provided'); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($agencyData['email']); ?></p>
                    <?php if ($agencyData['website']): ?>
                        <p>
                            <strong>Website:</strong> 
                            <a href="<?php echo htmlspecialchars($agencyData['website']); ?>" target="_blank">
                                <?php echo htmlspecialchars($agencyData['website']); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                    <?php if ($agencyData['address']): ?>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($agencyData['address']); ?></p>
                    <?php endif; ?>
                    <?php if ($agencyData['registration_number']): ?>
                        <p><strong>Registration Number:</strong> <?php echo htmlspecialchars($agencyData['registration_number']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Connection Status</h5>
                </div>
                <div class="card-body">
                    <?php if ($connectionStatus === 'accepted'): ?>
                        <div class="text-center mb-3">
                            <div class="mb-3">
                                <i class="fas fa-handshake fa-3x text-success"></i>
                            </div>
                            <h5>You're connected with this agency</h5>
                            <p class="text-muted">You can apply to their job listings and receive messages from them</p>
                        </div>
                        <form action="../includes/ajax/user_agency_connection.php" method="post">
                            <input type="hidden" name="agency_id" value="<?php echo $agencyId; ?>">
                            <input type="hidden" name="action" value="remove">
                            <button type="submit" class="btn btn-outline-danger w-100" 
                                    onclick="return confirm('Are you sure you want to remove this connection?');">
                                <i class="fas fa-user-minus me-1"></i> Remove Connection
                            </button>
                        </form>
                    <?php elseif ($connectionStatus === 'pending'): ?>
                        <div class="text-center">
                            <div class="mb-3">
                                <i class="fas fa-clock fa-3x text-warning"></i>
                            </div>
                            <h5>Connection Request Pending</h5>
                            <p class="text-muted">Wait for the agency to accept your connection request</p>
                        </div>
                    <?php else: ?>
                        <div class="text-center mb-3">
                            <div class="mb-3">
                                <i class="fas fa-user-plus fa-3x text-primary"></i>
                            </div>
                            <h5>Connect with this agency</h5>
                            <p class="text-muted">Build your network and get noticed by employers</p>
                        </div>
                        <form action="../includes/ajax/user_agency_connection.php" method="post">
                            <input type="hidden" name="agency_id" value="<?php echo $agencyId; ?>">
                            <input type="hidden" name="action" value="connect">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-user-plus me-1"></i> Connect with Agency
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Script to handle bookmarking agency jobs
document.addEventListener('DOMContentLoaded', function() {
    const bookmarkButtons = document.querySelectorAll('.bookmark-btn[data-listing-type="agency"]');
    
    bookmarkButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const jobId = this.dataset.jobId;
            const icon = this.querySelector('i');
            const isBookmarked = icon.classList.contains('fas');
            
            try {
                const response = await fetch('../includes/ajax/bookmark_agency_job.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `job_listing_id=${jobId}&action=${isBookmarked ? 'remove' : 'add'}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    icon.classList.toggle('fas');
                    icon.classList.toggle('far');
                    this.classList.toggle('bookmarked');
                    
                    // Show notification
                    const notification = document.createElement('div');
                    notification.className = 'position-fixed bottom-0 end-0 p-3';
                    notification.style.zIndex = '11';
                    notification.innerHTML = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ${isBookmarked ? 'Job removed from bookmarks' : 'Job added to bookmarks'}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    document.body.appendChild(notification);
                    
                    setTimeout(() => {
                        notification.remove();
                    }, 3000);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    });
});
</script>

<?php require_once 'footer.php'; ?>