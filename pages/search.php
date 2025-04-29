<?php
$pageTitle = 'Search Security Jobs - GuardPal';
require_once 'header.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireLogin();

$jobs = [];
$searchQuery = '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$jobType = isset($_GET['job_type']) ? sanitizeInput($_GET['job_type']) : '';
$location = isset($_GET['location']) ? sanitizeInput($_GET['location']) : '';
$experience = isset($_GET['experience']) ? sanitizeInput($_GET['experience']) : '';

if (isset($_GET['q']) || !empty($jobType) || !empty($location)) {
    $searchQuery = sanitizeInput($_GET['q'] ?? '');
    
    // Build the search query with additional filters
    $queryParts = [];
    
    if (!empty($searchQuery)) {
        $queryParts[] = $searchQuery;
    }
    
    if (!empty($jobType)) {
        $queryParts[] = $jobType;
    }
    
    if (!empty($location)) {
        $queryParts[] = "in $location";
    }
    
    if (!empty($experience)) {
        $queryParts[] = "$experience experience";
    }
    
    // Always add "security" to ensure security jobs
    if (!in_array('security', array_map('strtolower', $queryParts))) {
        $queryParts[] = "security";
    }
    
    $fullQuery = implode(' ', $queryParts);
    
    $jobsResponse = searchJobs($fullQuery, $page);
    if ($jobsResponse && isset($jobsResponse['data'])) {
        $jobs = $jobsResponse['data'];
    }
}

// Get popular job types for security
$securityJobTypes = [
    'Security Officer',
    'Security Guard',
    'Door Supervisor',
    'CCTV Operator',
    'Close Protection',
    'Retail Security',
    'Corporate Security',
    'Event Security',
    'Mobile Patrol',
    'Security Supervisor'
];

// Get popular locations
$popularLocations = [
    'London',
    'Manchester',
    'Birmingham',
    'Glasgow',
    'Leeds',
    'Liverpool',
    'Edinburgh',
    'Bristol',
    'Cardiff',
    'Belfast'
];
?>

<div class="search-container mb-5">
    <div class="container">
        <h2 class="text-center mb-4">Find Your Next Security Job</h2>
        <form method="GET" action="" class="row justify-content-center" id="job-filters">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-lg" name="q" id="search-input"
                                           placeholder="Search security jobs..." 
                                           value="<?php echo htmlspecialchars($searchQuery); ?>">
                                    <button class="btn btn-primary btn-lg" type="submit">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="job_type" class="form-label">Job Type</label>
                                <select class="form-select" id="job_type" name="job_type">
                                    <option value="">All Security Jobs</option>
                                    <?php foreach ($securityJobTypes as $type): ?>
                                        <option value="<?php echo $type; ?>" <?php echo $jobType === $type ? 'selected' : ''; ?>>
                                            <?php echo $type; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" 
                                       placeholder="City or Region" value="<?php echo htmlspecialchars($location); ?>">
                                <div class="mt-1">
                                    <?php foreach (array_slice($popularLocations, 0, 5) as $loc): ?>
                                        <a href="?q=<?php echo urlencode($searchQuery); ?>&location=<?php echo urlencode($loc); ?>&job_type=<?php echo urlencode($jobType); ?>" 
                                           class="badge bg-light text-dark text-decoration-none me-1 mb-1">
                                            <?php echo $loc; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="experience" class="form-label">Experience Level</label>
                                <select class="form-select" id="experience" name="experience">
                                    <option value="">Any Experience</option>
                                    <option value="entry level" <?php echo $experience === 'entry level' ? 'selected' : ''; ?>>Entry Level</option>
                                    <option value="mid level" <?php echo $experience === 'mid level' ? 'selected' : ''; ?>>Mid Level</option>
                                    <option value="senior" <?php echo $experience === 'senior' ? 'selected' : ''; ?>>Senior Level</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="container">
    <div id="loading-indicator" class="text-center my-5 d-none">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Searching for security jobs...</p>
    </div>
    
    <div id="search-results">
        <?php if (!empty($jobs)): ?>
            <h4 class="mb-3">Found <?php echo count($jobs); ?> security job<?php echo count($jobs) !== 1 ? 's' : ''; ?></h4>
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
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['job_city'] ?? ($job['job_country'] ?? 'Remote')); ?>
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
                                <a class="page-link" href="?q=<?php echo urlencode($searchQuery); ?>&page=<?php echo $page - 1; ?>&job_type=<?php echo urlencode($jobType); ?>&location=<?php echo urlencode($location); ?>&experience=<?php echo urlencode($experience); ?>">Previous</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($page + 2, 5); $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?q=<?php echo urlencode($searchQuery); ?>&page=<?php echo $i; ?>&job_type=<?php echo urlencode($jobType); ?>&location=<?php echo urlencode($location); ?>&experience=<?php echo urlencode($experience); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if (count($jobs) >= 10): ?>
                            <li class="page-item">
                                <a class="page-link" href="?q=<?php echo urlencode($searchQuery); ?>&page=<?php echo $page + 1; ?>&job_type=<?php echo urlencode($jobType); ?>&location=<?php echo urlencode($location); ?>&experience=<?php echo urlencode($experience); ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php elseif ($searchQuery || !empty($jobType) || !empty($location)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No security jobs found. Try different keywords, job type, or location.
            </div>
            <div class="row mt-4">
                <div class="col-md-6 mx-auto">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Popular Security Job Searches</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h6><i class="fas fa-briefcase me-2"></i>Job Types</h6>
                                <div>
                                    <?php foreach (array_slice($securityJobTypes, 0, 6) as $type): ?>
                                        <a href="?job_type=<?php echo urlencode($type); ?>" class="badge bg-primary text-decoration-none me-1 mb-1">
                                            <?php echo $type; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div>
                                <h6><i class="fas fa-map-marker-alt me-2"></i>Locations</h6>
                                <div>
                                    <?php foreach (array_slice($popularLocations, 0, 6) as $loc): ?>
                                        <a href="?location=<?php echo urlencode($loc); ?>" class="badge bg-secondary text-decoration-none me-1 mb-1">
                                            <?php echo $loc; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center my-5">
                <div class="mb-4">
                    <img src="https://xpressguards.com/wp-content/uploads/2020/07/best-security-guards-in-los-angeles.jpg" alt="Security Jobs" class="img-fluid rounded">
                </div>
                <h3>Looking for security positions?</h3>
                <p class="lead">Search for "Security Officer", "Door Supervisor", "CCTV Operator" or other security roles.</p>
                <p>We've specialized our search to only show private security jobs.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="../assets/js/job-search.js"></script>
<?php require_once 'footer.php'; ?>