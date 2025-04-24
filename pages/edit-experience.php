<?php
$pageTitle = 'Manage Work Experience - GuardPal';
require_once 'header.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireLogin();

$userId = $_SESSION['user_id'];
$conn = getDBConnection();

$success = '';
$error = '';

// Add work experience
if (isset($_POST['add_experience'])) {
    $companyName = sanitizeInput($_POST['company_name']);
    $jobTitle = sanitizeInput($_POST['job_title']);
    $startDate = sanitizeInput($_POST['start_date']);
    $isCurrent = isset($_POST['is_current']);
    $endDate = $isCurrent ? null : sanitizeInput($_POST['end_date']);
    $location = sanitizeInput($_POST['location']);
    $description = sanitizeInput($_POST['description']);
    
    if (empty($companyName) || empty($jobTitle) || empty($startDate)) {
        $error = 'Company name, job title and start date are required';
    } elseif (!$isCurrent && empty($endDate)) {
        $error = 'End date is required if this is not your current job';
    } else {
        try {
            $stmt = $conn->prepare("
                INSERT INTO work_experience 
                (user_id, company_name, job_title, start_date, end_date, is_current, location, description) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $companyName, $jobTitle, $startDate, $endDate, $isCurrent, $location, $description]);
            $success = 'Work experience added successfully';
        } catch (Exception $e) {
            $error = 'Error adding work experience: ' . $e->getMessage();
        }
    }
}

// Delete work experience
if (isset($_POST['delete_experience'])) {
    $experienceId = (int)$_POST['experience_id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM work_experience WHERE id = ? AND user_id = ?");
        $stmt->execute([$experienceId, $userId]);
        $success = 'Work experience deleted successfully';
    } catch (Exception $e) {
        $error = 'Error deleting work experience: ' . $e->getMessage();
    }
}

// Get all work experiences
$stmt = $conn->prepare("
    SELECT * FROM work_experience 
    WHERE user_id = ? 
    ORDER BY is_current DESC, end_date DESC, start_date DESC
");
$stmt->execute([$userId]);
$experiences = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Work Experience</h5>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($experiences)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Company</th>
                                        <th>Job Title</th>
                                        <th>Duration</th>
                                        <th>Location</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($experiences as $exp): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($exp['company_name']); ?></td>
                                            <td><?php echo htmlspecialchars($exp['job_title']); ?></td>
                                            <td>
                                                <?php 
                                                echo date('M Y', strtotime($exp['start_date'])) . ' - ';
                                                echo $exp['is_current'] ? 'Present' : date('M Y', strtotime($exp['end_date']));
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($exp['location'] ?: 'N/A'); ?></td>
                                            <td>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="experience_id" value="<?php echo $exp['id']; ?>">
                                                    <button type="submit" name="delete_experience" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Are you sure you want to delete this work experience?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">You haven't added any work experience yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Add Work Experience</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="company_name" name="company_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="job_title" class="form-label">Job Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="job_title" name="job_title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   placeholder="City, State, Country">
                        </div>
                        
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_current" name="is_current">
                            <label class="form-check-label" for="is_current">I currently work here</label>
                        </div>
                        
                        <div class="mb-3" id="end-date-group">
                            <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Job Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <button type="submit" name="add_experience" class="btn btn-primary w-100">Add Experience</button>
                    </form>
                </div>
            </div>
            
            <div class="mt-3 d-grid">
                <a href="profile.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Profile
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isCurrentCheckbox = document.getElementById('is_current');
    const endDateGroup = document.getElementById('end-date-group');
    const endDateInput = document.getElementById('end_date');
    
    function toggleEndDate() {
        if (isCurrentCheckbox.checked) {
            endDateGroup.style.display = 'none';
            endDateInput.removeAttribute('required');
        } else {
            endDateGroup.style.display = 'block';
            endDateInput.setAttribute('required', 'required');
        }
    }
    
    isCurrentCheckbox.addEventListener('change', toggleEndDate);
    toggleEndDate(); // Initialize on page load
});
</script>

<?php require_once 'footer.php'; ?>