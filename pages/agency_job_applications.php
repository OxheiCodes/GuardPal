<?php
$pageTitle = 'Job Applications - GuardPal';
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

// Get job ID from URL parameter
$jobId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verify job belongs to this agency
$stmt = $conn->prepare("SELECT * FROM job_listings WHERE id = ? AND agency_id = ?");
$stmt->execute([$jobId, $agencyId]);
$jobData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$jobData) {
    header('Location: agency_jobs.php');
    exit();
}

// Get applicants for this job
$stmt = $conn->prepare("
    SELECT b.*, u.id as user_id, u.full_name, u.email, u.job_title, u.profile_image,
           u.location, u.years_experience, u.sia_license_type
    FROM bookmarks b
    JOIN users u ON b.user_id = u.id
    WHERE b.job_listing_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$jobId]);
$applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Applications for "<?php echo htmlspecialchars($jobData['job_title']); ?>"</h5>
                    <a href="agency_jobs.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Jobs
                    </a>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="text-muted">Job Details</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($jobData['job_location'] ?: 'Not specified'); ?></p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>Job Type:</strong> <?php echo htmlspecialchars($jobData['job_type'] ?: 'Not specified'); ?></p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>Salary Range:</strong> <?php echo htmlspecialchars($jobData['salary_range'] ?: 'Not specified'); ?></p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>Posted:</strong> <?php echo date('M j, Y', strtotime($jobData['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6>Applicants (<?php echo count($applicants); ?>)</h6>
                    
                    <?php if (empty($applicants)): ?>
                        <div class="alert alert-info">No applications received for this job yet.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Applicant</th>
                                        <th>Experience</th>
                                        <th>SIA License</th>
                                        <th>Location</th>
                                        <th>Applied On</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applicants as $applicant): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo $applicant['profile_image'] ?: 'https://placehold.co/50x50?text=Profile'; ?>" 
                                                         alt="Profile" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                    <div>
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($applicant['full_name']); ?></h6>
                                                        <small class="text-muted"><?php echo htmlspecialchars($applicant['job_title'] ?: 'Security Professional'); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo $applicant['years_experience'] ?: '0'; ?> years</td>
                                            <td><?php echo htmlspecialchars($applicant['sia_license_type'] ?: 'Not specified'); ?></td>
                                            <td><?php echo htmlspecialchars($applicant['location'] ?: 'Not specified'); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($applicant['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="profile.php?id=<?php echo $applicant['user_id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" target="_blank">
                                                        <i class="fas fa-user me-1"></i> View Profile
                                                    </a>
                                                    <a href="agency_chat.php?with=<?php echo $applicant['user_id']; ?>" 
                                                       class="btn btn-sm btn-outline-success">
                                                        <i class="fas fa-comment me-1"></i> Contact
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>