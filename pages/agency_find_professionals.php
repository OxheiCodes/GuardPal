<?php
$pageTitle = 'Find Security Professionals - GuardPal';
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

// Get search parameters
$searchQuery = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
$skillFilter = isset($_GET['skill']) ? sanitizeInput($_GET['skill']) : '';
$locationFilter = isset($_GET['location']) ? sanitizeInput($_GET['location']) : '';
$siaTypeFilter = isset($_GET['sia_type']) ? sanitizeInput($_GET['sia_type']) : '';

// Build query based on filters
$queryParams = [];
$query = "
    SELECT u.*, 
           COUNT(DISTINCT c.id) as certification_count,
           COUNT(DISTINCT s.id) as skill_count,
           COUNT(DISTINCT w.id) as experience_count,
           GROUP_CONCAT(DISTINCT s.name SEPARATOR ',') as skills
    FROM users u
    LEFT JOIN certifications c ON u.id = c.user_id
    LEFT JOIN skills s ON u.id = s.user_id
    LEFT JOIN work_experience w ON u.id = w.user_id
    WHERE 1=1
";

if ($searchQuery) {
    $query .= " AND (u.full_name LIKE ? OR u.username LIKE ? OR u.job_title LIKE ? OR u.bio LIKE ?)";
    $searchTerm = "%$searchQuery%";
    $queryParams[] = $searchTerm;
    $queryParams[] = $searchTerm;
    $queryParams[] = $searchTerm;
    $queryParams[] = $searchTerm;
}

if ($skillFilter) {
    $query .= " AND EXISTS (SELECT 1 FROM skills WHERE user_id = u.id AND name LIKE ?)";
    $queryParams[] = "%$skillFilter%";
}

if ($locationFilter) {
    $query .= " AND u.location LIKE ?";
    $queryParams[] = "%$locationFilter%";
}

if ($siaTypeFilter) {
    $query .= " AND u.sia_license_type = ?";
    $queryParams[] = $siaTypeFilter;
}

$query .= " GROUP BY u.id ORDER BY u.full_name ASC";

$stmt = $conn->prepare($query);
$stmt->execute($queryParams);
$professionals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get connection status for each professional
foreach ($professionals as $key => $professional) {
    $stmt = $conn->prepare("
        SELECT status FROM agency_connections 
        WHERE agency_id = ? AND user_id = ?
    ");
    $stmt->execute([$agencyId, $professional['id']]);
    $connection = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $professionals[$key]['connection_status'] = $connection ? $connection['status'] : null;
}

// Get popular skills for filter suggestions
$stmt = $conn->prepare("
    SELECT name, COUNT(*) as count 
    FROM skills 
    GROUP BY name 
    ORDER BY count DESC 
    LIMIT 10
");
$stmt->execute();
$popularSkills = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get popular locations for filter suggestions
$stmt = $conn->prepare("
    SELECT location, COUNT(*) as count 
    FROM users 
    WHERE location IS NOT NULL AND location != ''
    GROUP BY location 
    ORDER BY count DESC 
    LIMIT 10
");
$stmt->execute();
$popularLocations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get SIA license types
$siaTypes = [
    'Door Supervision',
    'Security Guarding',
    'CCTV',
    'Close Protection',
    'Cash and Valuables in Transit',
    'Key Holding'
];
?>

<div class="container mt-4">
    <h2 class="mb-4">Find Security Professionals</h2>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label for="q" class="form-label">Search</label>
                            <input type="text" class="form-control" id="q" name="q" 
                                   placeholder="Search by name, job title..." 
                                   value="<?php echo htmlspecialchars($searchQuery); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="skill" class="form-label">Skill</label>
                            <input type="text" class="form-control" id="skill" name="skill" 
                                   placeholder="e.g. CCTV, Access Control" 
                                   value="<?php echo htmlspecialchars($skillFilter); ?>">
                            <?php if (!empty($popularSkills)): ?>
                                <div class="mt-1">
                                    <small class="text-muted">Popular: 
                                        <?php foreach (array_slice($popularSkills, 0, 3) as $skill): ?>
                                            <a href="?skill=<?php echo urlencode($skill['name']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($skill['name']); ?>
                                            </a>,
                                        <?php endforeach; ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   placeholder="City, Region..." 
                                   value="<?php echo htmlspecialchars($locationFilter); ?>">
                            <?php if (!empty($popularLocations)): ?>
                                <div class="mt-1">
                                    <small class="text-muted">Popular: 
                                        <?php foreach (array_slice($popularLocations, 0, 3) as $location): ?>
                                            <a href="?location=<?php echo urlencode($location['location']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($location['location']); ?>
                                            </a>,
                                        <?php endforeach; ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-2">
                            <label for="sia_type" class="form-label">SIA License Type</label>
                            <select class="form-select" id="sia_type" name="sia_type">
                                <option value="">Any</option>
                                <?php foreach ($siaTypes as $type): ?>
                                    <option value="<?php echo $type; ?>" <?php echo $siaTypeFilter === $type ? 'selected' : ''; ?>>
                                        <?php echo $type; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                            <a href="agency_find_professionals.php" class="btn btn-outline-secondary ms-2">Clear Filters</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <?php if (!empty($professionals)): ?>
            <?php foreach ($professionals as $professional): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <img src="<?php echo $professional['profile_image'] ?: 'https://placehold.co/100x100?text=Profile'; ?>" 
                                     class="rounded-circle me-3" style="width: 80px; height: 80px; object-fit: cover;">
                                <div>
                                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($professional['full_name']); ?></h5>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($professional['job_title'] ?: 'Security Professional'); ?></p>
                                </div>
                            </div>
                            
                            <?php if ($professional['location']): ?>
                                <p class="mb-2">
                                    <i class="fas fa-map-marker-alt text-secondary me-2"></i>
                                    <?php echo htmlspecialchars($professional['location']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($professional['sia_license_type']): ?>
                                <p class="mb-2">
                                    <i class="fas fa-id-card text-secondary me-2"></i>
                                    SIA: <?php echo htmlspecialchars($professional['sia_license_type']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($professional['skills']): ?>
                                <div class="mb-3">
                                    <?php 
                                    $skillsArray = explode(',', $professional['skills']);
                                    $skillsArray = array_unique($skillsArray);
                                    $skillsArray = array_slice($skillsArray, 0, 3); // Show max 3 skills
                                    
                                    foreach ($skillsArray as $skill): 
                                    ?>
                                        <span class="badge bg-light text-dark me-1 mb-1"><?php echo htmlspecialchars($skill); ?></span>
                                    <?php endforeach; ?>
                                    
                                    <?php if ($professional['skill_count'] > 3): ?>
                                        <span class="badge bg-secondary">+<?php echo $professional['skill_count'] - 3; ?> more</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($professional['connection_status'] === 'accepted'): ?>
                                <div class="d-grid gap-2">
                                    <a href="profile.php?id=<?php echo $professional['id']; ?>" class="btn btn-outline-primary">View Profile</a>
                                    <form method="POST" action="agency_connections.php" onsubmit="return confirm('Are you sure you want to remove this connection?');">
                                        <input type="hidden" name="user_id" value="<?php echo $professional['id']; ?>">
                                        <input type="hidden" name="action" value="remove">
                                        <button type="submit" class="btn btn-outline-danger w-100">
                                            <i class="fas fa-user-minus me-1"></i> Remove Connection
                                        </button>
                                    </form>
                                </div>
                            <?php elseif ($professional['connection_status'] === 'pending'): ?>
                                <div class="d-grid gap-2">
                                    <a href="profile.php?id=<?php echo $professional['id']; ?>" class="btn btn-outline-primary">View Profile</a>
                                    <button class="btn btn-secondary" disabled>Connection Request Sent</button>
                                </div>
                            <?php else: ?>
                                <div class="d-grid gap-2">
                                    <a href="profile.php?id=<?php echo $professional['id']; ?>" class="btn btn-outline-primary">View Profile</a>
                                    <form action="../includes/ajax/agency_connection.php" method="post">
                                        <input type="hidden" name="user_id" value="<?php echo $professional['id']; ?>">
                                        <input type="hidden" name="action" value="connect">
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="fas fa-user-plus me-1"></i> Connect with Professional
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer text-muted">
                            <small>
                                <i class="fas fa-certificate me-1"></i><?php echo $professional['certification_count']; ?> certifications
                                <i class="fas fa-briefcase ms-2 me-1"></i><?php echo $professional['experience_count']; ?> jobs
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <p class="mb-0">No security professionals found matching your search criteria. Try adjusting your filters or search terms.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>