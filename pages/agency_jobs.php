<?php
$pageTitle = 'Manage Jobs - GuardPal';
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

$success = '';
$error = '';

// Handle job activation/deactivation
if (isset($_POST['toggle_status'])) {
    $jobId = (int)$_POST['job_id'];
    $newStatus = $_POST['status'] === 'activate' ? 1 : 0;
    
    try {
        $stmt = $conn->prepare("
            UPDATE job_listings 
            SET is_active = ? 
            WHERE id = ? AND agency_id = ?
        ");
        $stmt->execute([$newStatus, $jobId, $agencyId]);
        $success = 'Job status updated successfully';
    } catch (Exception $e) {
        $error = 'Error updating job status: ' . $e->getMessage();
    }
}

// Handle job deletion
if (isset($_POST['delete_job'])) {
    $jobId = (int)$_POST['job_id'];
    
    try {
        $stmt = $conn->prepare("
            DELETE FROM job_listings 
            WHERE id = ? AND agency_id = ?
        ");
        $stmt->execute([$jobId, $agencyId]);
        $success = 'Job deleted successfully';
    } catch (Exception $e) {
        $error = 'Error deleting job: ' . $e->getMessage();
    }
}

// Get all job listings
$stmt = $conn->prepare("
    SELECT j.*, 
           (SELECT COUNT(*) FROM bookmarks b WHERE b.job_listing_id = j.id) as application_count
    FROM job_listings j
    WHERE j.agency_id = ? 
    ORDER BY j.created_at DESC
");
$stmt->execute([$agencyId]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Job Listings</h2>
        <a href="agency_create_job.php" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Create New Job
        </a>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($jobs)): ?>
                <div class="text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-briefcase fa-3x text-muted"></i>
                    </div>
                    <h4>No job listings yet</h4>
                    <p class="text-muted">Get started by creating your first job listing</p>
                    <a href="agency_create_job.php" class="btn btn-primary">Create Job Listing</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Job Title</th>
                                <th>Location</th>
                                <th>Posted Date</th>
                                <th>Applications</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jobs as $job): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($job['job_title']); ?></td>
                                    <td><?php echo htmlspecialchars($job['job_location']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($job['created_at'])); ?></td>
                                    <td>
                                        <a href="agency_job_applications.php?id=<?php echo $job['id']; ?>" class="text-primary">
                                            <?php echo $job['application_count']; ?> applications
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $job['is_active'] ? 'success' : 'danger'; ?>">
                                            <?php echo $job['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="agency_edit_job.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="agency_job_applications.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-outline-info" title="View Applications">
                                                <i class="fas fa-users"></i>
                                            </a>
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                                <input type="hidden" name="status" value="<?php echo $job['is_active'] ? 'deactivate' : 'activate'; ?>">
                                                <button type="submit" name="toggle_status" class="btn btn-sm btn-outline-<?php echo $job['is_active'] ? 'warning' : 'success'; ?>" title="<?php echo $job['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                                    <i class="fas fa-<?php echo $job['is_active'] ? 'pause' : 'play'; ?>"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this job listing? This action cannot be undone.');">
                                                <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                                <button type="submit" name="delete_job" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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

<?php require_once 'footer.php'; ?>