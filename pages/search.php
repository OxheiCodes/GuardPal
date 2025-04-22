<?php
$pageTitle = 'Search Jobs - GuardPal';
require_once 'header.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireLogin();

$jobs = [];
$searchQuery = '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if (isset($_GET['q'])) {
    $searchQuery = sanitizeInput($_GET['q']);
    $jobsResponse = searchJobs($searchQuery, $page);
    if ($jobsResponse && isset($jobsResponse['data'])) {
        $jobs = $jobsResponse['data'];
    }
}
?>

<div class="search-container mb-5">
    <div class="container">
        <h2 class="text-center mb-4">Find Your Next Security Job</h2>
        <form method="GET" action="" class="row justify-content-center">
            <div class="col-md-8">
                <div class="input-group">
                    <input type="text" class="form-control form-control-lg" name="q" 
                           placeholder="Search for security jobs..." 
                           value="<?php echo htmlspecialchars($searchQuery); ?>" required>
                    <button class="btn btn-light btn-lg" type="submit">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="container">
    <?php if (!empty($jobs)): ?>
        <div class="row">
            <?php foreach ($jobs as $job): ?>
                <div class="col-md-6 mb-4">
                    <div class="card job-card card-hover">
                        <div class="card-body job-card-body position-relative">
                            <button class="btn btn-link bookmark-btn" data-job-id="<?php echo htmlspecialchars($job['job_id']); ?>">
                                <i class="far fa-bookmark fa-lg"></i>
                            </button>
                            
                            <h5 class="job-title"><?php echo htmlspecialchars($job['job_title']); ?></h5>
                            <p class="company-name mb-2"><?php echo htmlspecialchars($job['employer_name']); ?></p>
                            
                            <div class="mb-3">
                                <span class="badge badge-location me-2">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['job_city'] ?? 'Remote'); ?>
                                </span>
                                <span class="badge badge-type">
                                    <i class="fas fa-clock"></i> <?php echo htmlspecialchars($job['job_employment_type'] ?? 'Full-time'); ?>
                                </span>
                            </div>
                            
                            <p class="card-text small">
                                <?php echo htmlspecialchars(substr($job['job_description'], 0, 150)) . '...'; ?>
                            </p>
                            
                            <a href="job-details.php?id=<?php echo urlencode($job['job_id']); ?>" 
                               class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($page > 1 || count($jobs) >= 10): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?q=<?php echo urlencode($searchQuery); ?>&page=<?php echo $page - 1; ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($page + 2, 5); $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?q=<?php echo urlencode($searchQuery); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if (count($jobs) >= 10): ?>
                        <li class="page-item">
                            <a class="page-link" href="?q=<?php echo urlencode($searchQuery); ?>&page=<?php echo $page + 1; ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php elseif ($searchQuery): ?>
        <div class="alert alert-info">No jobs found for "<?php echo htmlspecialchars($searchQuery); ?>". Try different keywords.</div>
    <?php endif; ?>
</div>

<script src="../assets/js/bookmarks.js"></script>
<?php require_once 'footer.php'; ?>