<?php
$pageTitle = 'Job Details - GuardPal';
require_once 'header.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireLogin();

$job = null;
$jobId = isset($_GET['id']) ? $_GET['id'] : '';

if ($jobId) {
    $jobResponse = getJobDetails($jobId);
    if ($jobResponse && isset($jobResponse['data'][0])) {
        $job = $jobResponse['data'][0];
    }
}

if (!$job) {
    header('Location: search.php');
    exit();
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h2><?php echo htmlspecialchars($job['job_title']); ?></h2>
                            <h4 class="text-muted"><?php echo htmlspecialchars($job['employer_name']); ?></h4>
                        </div>
                        <button class="btn btn-outline-primary bookmark-btn" data-job-id="<?php echo htmlspecialchars($job['job_id']); ?>">
                            <i class="far fa-bookmark"></i> Save Job
                        </button>
                    </div>
                    
                    <div class="mt-3">
                        <span class="badge bg-secondary me-2">
                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['job_city'] ?? 'Remote'); ?>
                        </span>
                        <span class="badge bg-secondary me-2">
                            <i class="fas fa-clock"></i> <?php echo htmlspecialchars($job['job_employment_type'] ?? 'Full-time'); ?>
                        </span>
                        <?php if (isset($job['job_salary'])): ?>
                            <span class="badge bg-success">
                                <i class="fas fa-dollar-sign"></i> <?php echo htmlspecialchars($job['job_salary']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <hr>
                    
                    <h5>Job Description</h5>
                    <div class="job-description">
                        <?php echo nl2br(htmlspecialchars($job['job_description'])); ?>
                    </div>
                    
                    <?php if (isset($job['job_required_skills']) && !empty($job['job_required_skills'])): ?>
                        <hr>
                        <h5>Required Skills</h5>
                        <div>
                            <?php foreach ($job['job_required_skills'] as $skill): ?>
                                <span class="badge bg-info me-2 mb-2"><?php echo htmlspecialchars($skill); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($job['job_benefits']) && !empty($job['job_benefits'])): ?>
                        <hr>
                        <h5>Benefits</h5>
                        <ul>
                            <?php foreach ($job['job_benefits'] as $benefit): ?>
                                <li><?php echo htmlspecialchars($benefit); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    
                    <hr>
                    <a href="<?php echo htmlspecialchars($job['job_apply_link']); ?>" target="_blank" class="btn btn-primary btn-lg">Apply Now</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5>Company Information</h5>
                    <?php if (isset($job['employer_logo'])): ?>
                        <img src="<?php echo htmlspecialchars($job['employer_logo']); ?>" 
                             alt="<?php echo htmlspecialchars($job['employer_name']); ?>" 
                             class="img-fluid mb-3">
                    <?php endif; ?>
                    <p><strong>Company:</strong> <?php echo htmlspecialchars($job['employer_name']); ?></p>
                    <?php if (isset($job['employer_website'])): ?>
                        <p><strong>Website:</strong> <a href="<?php echo htmlspecialchars($job['employer_website']); ?>" target="_blank">Visit Website</a></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h5>Job Details</h5>
                    <p><strong>Posted:</strong> <?php echo htmlspecialchars($job['job_posted_at_datetime_utc'] ?? 'N/A'); ?></p>
                    <p><strong>Job ID:</strong> <?php echo htmlspecialchars($job['job_id']); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/bookmarks.js"></script>
<?php require_once 'footer.php'; ?>