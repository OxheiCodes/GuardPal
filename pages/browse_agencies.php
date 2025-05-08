<?php
$pageTitle = 'Browse Security Agencies - GuardPal';
require_once 'header.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireLogin();

$userId = $_SESSION['user_id'];
$conn = getDBConnection();

// Get search parameters
$searchQuery = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
$locationFilter = isset($_GET['location']) ? sanitizeInput($_GET['location']) : '';

// Build query based on filters
$queryParams = [];
$query = "
    SELECT a.*, 
           (SELECT COUNT(*) FROM job_listings WHERE agency_id = a.id AND is_active = 1) as active_jobs_count
    FROM agencies a
    WHERE 1=1
";

if ($searchQuery) {
    $query .= " AND (a.name LIKE ? OR a.description LIKE ?)";
    $searchTerm = "%$searchQuery%";
    $queryParams[] = $searchTerm;
    $queryParams[] = $searchTerm;
}

if ($locationFilter) {
    $query .= " AND a.address LIKE ?";
    $queryParams[] = "%$locationFilter%";
}

$query .= " ORDER BY a.name ASC";

$stmt = $conn->prepare($query);
$stmt->execute($queryParams);
$agencies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get connection status for each agency
foreach ($agencies as $key => $agency) {
    $stmt = $conn->prepare("
        SELECT status FROM agency_connections 
        WHERE agency_id = ? AND user_id = ?
    ");
    $stmt->execute([$agency['id'], $userId]);
    $connection = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $agencies[$key]['connection_status'] = $connection ? $connection['status'] : null;
}

// Get popular locations for filter suggestions
$stmt = $conn->prepare("
    SELECT SUBSTRING_INDEX(address, ',', 1) as location, COUNT(*) as count 
    FROM agencies 
    WHERE address IS NOT NULL AND address != ''
    GROUP BY SUBSTRING_INDEX(address, ',', 1)
    ORDER BY count DESC 
    LIMIT 10
");
$stmt->execute();
$popularLocations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h2 class="mb-4">Security Agencies</h2>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-6">
                            <label for="q" class="form-label">Search</label>
                            <input type="text" class="form-control" id="q" name="q" 
                                   placeholder="Search by agency name or description" 
                                   value="<?php echo htmlspecialchars($searchQuery); ?>">
                        </div>
                        <div class="col-md-6">
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
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                            <a href="browse_agencies.php" class="btn btn-outline-secondary ms-2">Clear Filters</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <?php if (!empty($agencies)): ?>
            <?php foreach ($agencies as $agency): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <img src="<?php echo $agency['logo_image'] ?: 'https://placehold.co/100x100?text=Agency'; ?>" 
                                     class="rounded me-3" style="width: 80px; height: 80px; object-fit: cover;">
                                <div>
                                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($agency['name']); ?></h5>
                                    <?php if ($agency['website']): ?>
                                        <p class="mb-1 small">
                                            <i class="fas fa-globe me-1"></i>
                                            <a href="<?php echo htmlspecialchars($agency['website']); ?>" target="_blank" class="text-decoration-none">
                                                <?php echo htmlspecialchars($agency['website']); ?>
                                            </a>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if ($agency['address']): ?>
                                <p class="mb-2 small">
                                    <i class="fas fa-map-marker-alt text-secondary me-2"></i>
                                    <?php echo htmlspecialchars($agency['address']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($agency['active_jobs_count'] > 0): ?>
                                <p class="mb-2">
                                    <span class="badge bg-success">
                                        <i class="fas fa-briefcase me-1"></i>
                                        <?php echo $agency['active_jobs_count']; ?> Active Jobs
                                    </span>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($agency['description']): ?>
                                <p class="text-muted mb-3">
                                    <?php echo nl2br(htmlspecialchars(substr($agency['description'], 0, 150) . (strlen($agency['description']) > 150 ? '...' : ''))); ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="d-grid gap-2">
                                <a href="view_agency.php?id=<?php echo $agency['id']; ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-building me-1"></i> View Agency Profile
                                </a>
                                <?php if ($agency['connection_status'] === 'accepted'): ?>
                                    <form action="../includes/ajax/user_agency_connection.php" method="post">
                                        <input type="hidden" name="agency_id" value="<?php echo $agency['id']; ?>">
                                        <input type="hidden" name="action" value="remove">
                                        <button type="submit" class="btn btn-outline-danger w-100" 
                                                onclick="return confirm('Are you sure you want to remove this connection?');">
                                            <i class="fas fa-user-minus me-1"></i> Remove Connection
                                        </button>
                                    </form>
                                <?php elseif ($agency['connection_status'] === 'pending'): ?>
                                    <button class="btn btn-outline-secondary" disabled>
                                        <i class="fas fa-clock me-1"></i> Connection Pending
                                    </button>
                                <?php else: ?>
                                    <form action="../includes/ajax/user_agency_connection.php" method="post">
                                        <input type="hidden" name="agency_id" value="<?php echo $agency['id']; ?>">
                                        <input type="hidden" name="action" value="connect">
                                        <button type="submit" class="btn btn-outline-success w-100">
                                            <i class="fas fa-user-plus me-1"></i> Connect with Agency
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <p class="mb-0">No security agencies found matching your search criteria. Try adjusting your filters or search terms.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>