<?php
$pageTitle = 'Edit Job Listing - GuardPal';
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

$error = '';
$success = '';

// Get job ID from URL
$jobId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if job exists and belongs to the agency
$stmt = $conn->prepare("
    SELECT * FROM job_listings 
    WHERE id = ? AND agency_id = ?
");
$stmt->execute([$jobId, $agencyId]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    header('Location: agency_jobs.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobTitle = sanitizeInput($_POST['job_title']);
    $jobDescription = sanitizeInput($_POST['job_description']);
    $jobLocation = sanitizeInput($_POST['job_location']);
    $jobType = sanitizeInput($_POST['job_type']);
    $salaryRange = sanitizeInput($_POST['salary_range']);
    $requirements = sanitizeInput($_POST['requirements']);
    $applicationUrl = sanitizeInput($_POST['application_url']);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Basic validation
    if (empty($jobTitle) || empty($jobDescription)) {
        $error = 'Job title and description are required';
    } else {
        try {
            $stmt = $conn->prepare("
                UPDATE job_listings SET 
                job_title = ?, 
                job_description = ?, 
                job_location = ?, 
                job_type = ?, 
                salary_range = ?, 
                requirements = ?, 
                application_url = ?, 
                is_active = ?
                WHERE id = ? AND agency_id = ?
            ");
            
            $stmt->execute([
                $jobTitle,
                $jobDescription,
                $jobLocation,
                $jobType,
                $salaryRange,
                $requirements,
                $applicationUrl,
                $isActive,
                $jobId,
                $agencyId
            ]);
            
            $success = 'Job listing updated successfully';
            
            // Refresh job data
            $stmt = $conn->prepare("SELECT * FROM job_listings WHERE id = ?");
            $stmt->execute([$jobId]);
            $job = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $error = 'Error updating job listing: ' . $e->getMessage();
        }
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit Job Listing</h5>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="job_title" class="form-label">Job Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="job_title" name="job_title" 
                                       value="<?php echo htmlspecialchars($job['job_title']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="job_location" class="form-label">Job Location</label>
                                <input type="text" class="form-control" id="job_location" name="job_location" 
                                       value="<?php echo htmlspecialchars($job['job_location']); ?>"
                                       placeholder="e.g. London, UK or Remote">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="job_type" class="form-label">Job Type</label>
                                <select class="form-select" id="job_type" name="job_type">
                                    <option value="">-- Select Job Type --</option>
                                    <option value="Full-time" <?php echo $job['job_type'] === 'Full-time' ? 'selected' : ''; ?>>Full-time</option>
                                    <option value="Part-time" <?php echo $job['job_type'] === 'Part-time' ? 'selected' : ''; ?>>Part-time</option>
                                    <option value="Contract" <?php echo $job['job_type'] === 'Contract' ? 'selected' : ''; ?>>Contract</option>
                                    <option value="Temporary" <?php echo $job['job_type'] === 'Temporary' ? 'selected' : ''; ?>>Temporary</option>
                                    <option value="Freelance" <?php echo $job['job_type'] === 'Freelance' ? 'selected' : ''; ?>>Freelance</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="salary_range" class="form-label">Salary Range</label>
                                <input type="text" class="form-control" id="salary_range" name="salary_range" 
                                       value="<?php echo htmlspecialchars($job['salary_range']); ?>"
                                       placeholder="e.g. £30,000 - £40,000 per annum">
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="job_description" class="form-label">Job Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="job_description" name="job_description" rows="6" required><?php echo htmlspecialchars($job['job_description']); ?></textarea>
                                <small class="text-muted">Provide a detailed description of the job role, responsibilities, and company information.</small>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="requirements" class="form-label">Job Requirements</label>
                                <textarea class="form-control" id="requirements" name="requirements" rows="4"><?php echo htmlspecialchars($job['requirements']); ?></textarea>
                                <small class="text-muted">List the required skills, qualifications, and experience for this position.</small>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="application_url" class="form-label">External Application URL (Optional)</label>
                                <input type="url" class="form-control" id="application_url" name="application_url" 
                                       value="<?php echo htmlspecialchars($job['application_url']); ?>"
                                       placeholder="https://example.com/apply">
                                <small class="text-muted">If provided, applicants will be directed to this URL to complete their application.</small>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo $job['is_active'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">
                                        Set this job as active and visible to security professionals
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="agency_jobs.php" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Job Listing</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>