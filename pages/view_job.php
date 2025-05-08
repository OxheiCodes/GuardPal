<?php
// pages/view_job.php
$pageTitle = 'Job Details - GuardPal';
require_once 'header.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireLogin();

$userId = $_SESSION['user_id'];
$conn = getDBConnection();

$jobId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get job details
$stmt = $conn->prepare("
    SELECT j.*, a.id as agency_id, a.name as agency_name, a.logo_image, a.phone, a.email, a.website
    FROM job_listings j
    JOIN agencies a ON j.agency_id = a.id
    WHERE j.id = ? AND j.is_active = 1
");
$stmt->execute([$jobId]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    header('Location: dashboard.php');
    exit();
}

// Check if user has already applied to this job
$stmt = $conn->prepare("
    SELECT 1 FROM bookmarks
    WHERE job_listing_id = ? AND user_id = ?
");
$stmt->execute([$jobId, $userId]);
$hasApplied = $stmt->fetchColumn() ? true : false;

// Check connection status with agency
$stmt = $conn->prepare("
    SELECT status FROM agency_connections
    WHERE agency_id = ? AND user_id = ?
");
$stmt->execute([$job['agency_id'], $userId]);
$connectionStatus = $stmt->fetch(PDO::FETCH_ASSOC);
$isConnected = $connectionStatus && $connectionStatus['status'] === 'accepted';

// Process job application
$applicationMessage = '';
if (isset($_POST['apply_job']) && !$hasApplied) {
    try {
        // Insert bookmark entry
        $stmt = $conn->prepare("
            INSERT INTO bookmarks (user_id, job_listing_id, job_title, company_name, job_description)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $jobId,
            $job['job_title'],
            $job['agency_name'],
            $job['job_description']
        ]);
        
        $hasApplied = true;
        $applicationMessage = 'Application submitted successfully! The agency has been notified.';
    } catch (Exception $e) {
        $applicationMessage = 'Error submitting application: ' . $e->getMessage();
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="view_agency.php?id=<?php echo $job['agency_id']; ?>">
                        <?php echo htmlspecialchars($job['agency_name']); ?>
                    </a></li>
                    <li class="breadcrumb-item active" aria-current="page">Job Details</li>
                </ol>
            </nav>
            
            <?php if ($applicationMessage): ?>
                <div class="alert alert-<?php echo $hasApplied ? 'success' : 'danger'; ?> mb-4">
                    <?php echo $applicationMessage; ?>
                </div>
            <?php endif; ?>
            
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h2 class="mb-1"><?php echo htmlspecialchars($job['job_title']); ?></h2>
                        <button class="btn btn-link bookmark-btn <?php echo $hasApplied ? 'bookmarked' : ''; ?>" 
                                data-job-id="<?php echo $jobId; ?>"
                                data-listing-type="agency">
                            <i class="<?php echo $hasApplied ? 'fas' : 'far'; ?> fa-bookmark fa-2x"></i>
                        </button>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <img src="<?php echo $job['logo_image'] ?: 'https://placehold.co/60x60?text=Agency'; ?>" 
                             alt="Agency Logo" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                        <div>
                            <h5 class="mb-0"><?php echo htmlspecialchars($job['agency_name']); ?></h5>
                            <p class="text-muted mb-0">
                                <i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($job['job_location'] ?: 'Remote'); ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <span class="badge bg-secondary me-2">
                            <i class="fas fa-briefcase me-1"></i> <?php echo htmlspecialchars($job['job_type'] ?: 'Not specified'); ?>
                        </span>
                        <?php if ($job['salary_range']): ?>
                            <span class="badge bg-success">
                                <i class="fas fa-pound-sign me-1"></i> <?php echo htmlspecialchars($job['salary_range']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <hr>
                    
                    <h5>Job Description</h5>
                    <div class="mb-4">
                        <?php echo nl2br(htmlspecialchars($job['job_description'])); ?>
                    </div>
                    
                    <?php if ($job['requirements']): ?>
                        <h5>Requirements</h5>
                        <div class="mb-4">
                            <?php echo nl2br(htmlspecialchars($job['requirements'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <small class="text-muted">
                            Posted on <?php echo date('M j, Y', strtotime($job['created_at'])); ?>
                        </small>
                        
                        <?php if ($hasApplied): ?>
                            <button class="btn btn-success" disabled>
                                <i class="fas fa-check-circle me-2"></i>Applied
                            </button>
                        <?php else: ?>
                            <?php if ($isConnected): ?>
                                <form method="POST" action="">
                                    <input type="hidden" name="apply_job" value="1">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i>Apply Now
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#connectModal">
                                    <i class="fas fa-paper-plane me-2"></i>Apply Now
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($job['application_url']): ?>
                        <div class="alert alert-info mt-3">
                            <p class="mb-2">You can also apply directly through the company website:</p>
                            <a href="<?php echo htmlspecialchars($job['application_url']); ?>" target="_blank" class="btn btn-outline-primary">
                                <i class="fas fa-external-link-alt me-2"></i>Apply on Company Website
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">About <?php echo htmlspecialchars($job['agency_name']); ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <img src="<?php echo $job['logo_image'] ?: 'https://placehold.co/100x100?text=Agency'; ?>" 
                                 alt="Agency Logo" class="img-fluid rounded mb-3" style="max-width: 100px;">
                            <a href="view_agency.php?id=<?php echo $job['agency_id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-building me-1"></i> View Agency
                            </a>
                        </div>
                        <div class="col-md-9">
                            <p>
                                <strong>Website:</strong> 
                                <?php if ($job['website']): ?>
                                    <a href="<?php echo htmlspecialchars($job['website']); ?>" target="_blank">
                                        <?php echo htmlspecialchars($job['website']); ?>
                                    </a>
                                <?php else: ?>
                                    Not provided
                                <?php endif; ?>
                            </p>
                            <p>
                                <strong>Contact Email:</strong> 
                                <a href="mailto:<?php echo htmlspecialchars($job['email']); ?>">
                                    <?php echo htmlspecialchars($job['email']); ?>
                                </a>
                            </p>
                            <?php if ($job['phone']): ?>
                                <p>
                                    <strong>Phone:</strong> 
                                    <a href="tel:<?php echo htmlspecialchars($job['phone']); ?>">
                                        <?php echo htmlspecialchars($job['phone']); ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!$isConnected): ?>
                                <div class="mt-3">
                                    <p class="text-muted">Connect with this agency to apply for their jobs directly through GuardPal.</p>
                                    <form action="../includes/ajax/user_agency_connection.php" method="post">
                                        <input type="hidden" name="agency_id" value="<?php echo $job['agency_id']; ?>">
                                        <input type="hidden" name="action" value="connect">
                                        <button type="submit" class="btn btn-outline-success">
                                            <i class="fas fa-user-plus me-1"></i> Connect with Agency
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Job Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6><i class="fas fa-map-marker-alt me-2"></i>Location</h6>
                        <p><?php echo htmlspecialchars($job['job_location'] ?: 'Remote'); ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <h6><i class="fas fa-briefcase me-2"></i>Employment Type</h6>
                        <p><?php echo htmlspecialchars($job['job_type'] ?: 'Not specified'); ?></p>
                    </div>
                    
                    <?php if ($job['salary_range']): ?>
                        <div class="mb-3">
                            <h6><i class="fas fa-pound-sign me-2"></i>Salary Range</h6>
                            <p><?php echo htmlspecialchars($job['salary_range']); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <h6><i class="fas fa-calendar-alt me-2"></i>Posted Date</h6>
                        <p><?php echo date('M j, Y', strtotime($job['created_at'])); ?></p>
                    </div>
                    
                    <?php if ($hasApplied): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>You have applied for this job
                        </div>
                    <?php elseif ($isConnected): ?>
                        <form method="POST" action="" class="d-grid">
                            <input type="hidden" name="apply_job" value="1">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Apply Now
                            </button>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#connectModal">
                            <i class="fas fa-paper-plane me-2"></i>Apply Now
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php
            // Get similar jobs from the same agency
            $stmt = $conn->prepare("
                SELECT * FROM job_listings
                WHERE agency_id = ? AND id != ? AND is_active = 1
                ORDER BY created_at DESC
                LIMIT 3
            ");
            $stmt->execute([$job['agency_id'], $jobId]);
            $similarJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($similarJobs)):
            ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">More Jobs From This Agency</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php foreach ($similarJobs as $similarJob): ?>
                                <a href="view_job.php?id=<?php echo $similarJob['id']; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($similarJob['job_title']); ?></h6>
                                            <small>
                                                <i class="fas fa-map-marker-alt me-1"></i> 
                                                <?php echo htmlspecialchars($similarJob['job_location'] ?: 'Remote'); ?>
                                            </small>
                                            <?php if ($similarJob['job_type']): ?>
                                                <small class="ms-2">
                                                    <i class="fas fa-briefcase me-1"></i> 
                                                    <?php echo htmlspecialchars($similarJob['job_type']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php 
                                            $postDate = new DateTime($similarJob['created_at']);
                                            $now = new DateTime();
                                            $interval = $postDate->diff($now);
                                            
                                            if ($interval->days == 0) {
                                                echo 'Today';
                                            } elseif ($interval->days == 1) {
                                                echo 'Yesterday';
                                            } else {
                                                echo $interval->days . ' days ago';
                                            }
                                            ?>
                                        </small>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Connect Modal -->
<?php if (!$isConnected && !$hasApplied): ?>
<div class="modal fade" id="connectModal" tabindex="-1" aria-labelledby="connectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="connectModalLabel">Connect to Apply</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-user-plus fa-3x text-primary"></i>
                </div>
                <p>To apply for jobs through GuardPal, you need to connect with the agency first.</p>
                <p>Connecting allows agencies to view your profile and contact you about job opportunities.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="../includes/ajax/user_agency_connection.php" method="post">
                    <input type="hidden" name="agency_id" value="<?php echo $job['agency_id']; ?>">
                    <input type="hidden" name="action" value="connect">
                    <button type="submit" class="btn btn-primary">Connect with Agency</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

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
                    
                    // Reload the page if this was an application (only when adding)
                    if (!isBookmarked) {
                        window.location.reload();
                    }
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    });
});
</script>

<?php require_once 'footer.php'; ?>