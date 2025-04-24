<?php
$pageTitle = 'Find Security Professionals - GuardPal';
require_once 'header.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireLogin();

$userId = $_SESSION['user_id'];
$conn = getDBConnection();

// Get search parameters
$searchQuery = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
$skillFilter = isset($_GET['skill']) ? sanitizeInput($_GET['skill']) : '';
$locationFilter = isset($_GET['location']) ? sanitizeInput($_GET['location']) : '';

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
    WHERE u.id != ?
";
$queryParams[] = $userId;

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

$query .= " GROUP BY u.id ORDER BY u.full_name ASC";

$stmt = $conn->prepare($query);
$stmt->execute($queryParams);
$professionals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get connection status for each professional
foreach ($professionals as $key => $professional) {
    $stmt = $conn->prepare("
        SELECT status, requester_id FROM connections 
        WHERE (requester_id = ? AND receiver_id = ?) 
           OR (requester_id = ? AND receiver_id = ?)
    ");
    $stmt->execute([$userId, $professional['id'], $professional['id'], $userId]);
    $connection = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $professionals[$key]['connection_status'] = $connection ? $connection['status'] : null;
    $professionals[$key]['is_requester'] = $connection && $connection['requester_id'] == $userId;
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
?>

<div class="container mt-4">
    <h2 class="mb-4">Find Security Professionals</h2>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="q" name="q" 
                                   placeholder="Search by name, job title..." 
                                   value="<?php echo htmlspecialchars($searchQuery); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="skill" class="form-label">Skill</label>
                            <input type="text" class="form-control" id="skill" name="skill" 
                                   placeholder="e.g. CCTV, Access Control" 
                                   value="<?php echo htmlspecialchars($skillFilter); ?>">
                        </div>
                        <div class