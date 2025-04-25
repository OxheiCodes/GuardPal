<?php
$pageTitle = 'Network Search - GuardPal';
require_once 'header.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireLogin();

$userId = $_SESSION['user_id'];
$conn = getDBConnection();

// Get user's skills to suggest similar professionals
$stmt = $conn->prepare("SELECT name FROM skills WHERE user_id = ?");
$stmt->execute([$userId]);
$userSkills = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get user's location to suggest nearby professionals
$stmt = $conn->prepare("SELECT location FROM users WHERE id = ?");
$stmt->execute([$userId]);
$userLocation = $stmt->fetchColumn();

// Get professionals with similar skills
$similarProfessionals = [];
if (!empty($userSkills)) {
    $placeholders = str_repeat('?,', count($userSkills) - 1) . '?';
    $query = "
        SELECT DISTINCT u.*, COUNT(s.id) as matching_skills
        FROM users u
        JOIN skills s ON u.id = s.user_id
        WHERE u.id != ? AND s.name IN ($placeholders)
        GROUP BY u.id
        ORDER BY matching_skills DESC
        LIMIT 6
    ";
    
    $params = array_merge([$userId], $userSkills);
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $similarProfessionals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get connection status for each professional
    foreach ($similarProfessionals as $key => $professional) {
        $stmt = $conn->prepare("
            SELECT status, requester_id FROM connections 
            WHERE (requester_id = ? AND receiver_id = ?) 
               OR (requester_id = ? AND receiver_id = ?)
        ");
        $stmt->execute([$userId, $professional['id'], $professional['id'], $userId]);
        $connection = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $similarProfessionals[$key]['connection_status'] = $connection ? $connection['status'] : null;
        $similarProfessionals[$key]['is_requester'] = $connection && $connection['requester_id'] == $userId;
    }
}

// Get professionals with same location
$nearbyProfessionals = [];
if (!empty($userLocation)) {
    $stmt = $conn->prepare("
        SELECT u.*
        FROM users u
        WHERE u.id != ? AND u.location LIKE ?
        ORDER BY u.full_name
        LIMIT 6
    ");
    $stmt->execute([$userId, "%$userLocation%"]);
    $nearbyProfessionals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get connection status for each professional
    foreach ($nearbyProfessionals as $key => $professional) {
        $stmt = $conn->prepare("
            SELECT status, requester_id FROM connections 
            WHERE (requester_id = ? AND receiver_id = ?) 
               OR (requester_id = ? AND receiver_id = ?)
        ");
        $stmt->execute([$userId, $professional['id'], $professional['id'], $userId]);
        $connection = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $nearbyProfessionals[$key]['connection_status'] = $connection ? $connection['status'] : null;
        $nearbyProfessionals[$key]['is_requester'] = $connection && $connection['requester_id'] == $userId;
    }
}

// Get new professionals (recently registered)
$stmt = $conn->prepare("
    SELECT u.*
    FROM users u
    WHERE u.id != ?
    ORDER BY u.created_at DESC
    LIMIT 6
");
$stmt->execute([$userId]);
$newProfessionals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get connection status for each professional
foreach ($newProfessionals as $key => $professional) {
    $stmt = $conn->prepare("
        SELECT status, requester_id FROM connections 
        WHERE (requester_id = ? AND receiver_id = ?) 
           OR (requester_id = ? AND receiver_id = ?)
    ");
    $stmt->execute([$userId, $professional['id'], $professional['id'], $userId]);
    $connection = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $newProfessionals[$key]['connection_status'] = $connection ? $connection['status'] : null;
    $newProfessionals[$key]['is_requester'] = $connection && $connection['requester_id'] == $userId;
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Grow Your Professional Network</h2>
        <a href="find-professionals.php" class="btn btn-primary">Advanced Search</a>
    </div>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body text-center p-4">
                    <h4 class="mb-3">Connect with security professionals to expand your network</h4>
                    <p class="mb-4">Building a strong professional network can help you discover job opportunities, share industry knowledge, and advance your career</p>
                    <a href="find-professionals.php" class="btn btn-lg btn-primary">Find Professionals</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Similar Skills Section -->
    <?php if (!empty($similarProfessionals)): ?>
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Professionals With Similar Skills</h3>
            <a href="find-professionals.php" class="btn btn-outline-primary btn-sm">View More</a>
        </div>
        
        <div class="row">
            <?php foreach ($similarProfessionals as $professional): ?>
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
                            
                            <?php
                            // Get skills for this professional
                            $stmt = $conn->prepare("SELECT name FROM skills WHERE user_id = ? ORDER BY proficiency DESC LIMIT 3");
                            $stmt->execute([$professional['id']]);
                            $skills = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            
                            if (!empty($skills)):
                            ?>
                                <div class="mb-3">
                                    <?php foreach ($skills as $skill): ?>
                                        <span class="badge bg-light text-dark me-1 mb-1"><?php echo htmlspecialchars($skill); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($professional['connection_status'] === 'accepted'): ?>
                                <div class="d-grid gap-2">
                                    <a href="profile.php?id=<?php echo $professional['id']; ?>" class="btn btn-outline-primary">View Profile</a>
                                    <a href="chat.php?with=<?php echo $professional['id']; ?>" class="btn btn-outline-success">
                                        <i class="fas fa-comment me-2"></i>Message
                                    </a>
                                </div>
                            <?php elseif ($professional['connection_status'] === 'pending' && $professional['is_requester']): ?>
                                <div class="d-grid gap-2">
                                    <a href="profile.php?id=<?php echo $professional['id']; ?>" class="btn btn-outline-primary">View Profile</a>
                                    <button class="btn btn-secondary" disabled>Connection Requested</button>
                                </div>
                            <?php elseif ($professional['connection_status'] === 'pending' && !$professional['is_requester']): ?>
                                <div class="d-grid gap-2">
                                    <a href="profile.php?id=<?php echo $professional['id']; ?>" class="btn btn-outline-primary">View Profile</a>
                                    <div class="btn-group w-100">
                                        <form action="../includes/ajax/connection.php" method="post" class="w-50">
                                            <input type="hidden" name="requester_id" value="<?php echo $professional['id']; ?>">
                                            <input type="hidden" name="action" value="accept">
                                            <button type="submit" class="btn btn-success w-100">Accept</button>
                                        </form>
                                        <form action="../includes/ajax/connection.php" method="post" class="w-50">
                                            <input type="hidden" name="requester_id" value="<?php echo $professional['id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn btn-danger w-100">Reject</button>
                                        </form>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="d-grid gap-2">
                                    <a href="profile.php?id=<?php echo $professional['id']; ?>" class="btn btn-outline-primary">View Profile</a>
                                    <form action="../includes/ajax/connection.php" method="post">
                                        <input type="hidden" name="receiver_id" value="<?php echo $professional['id']; ?>">
                                        <input type="hidden" name="action" value="connect">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-user-plus me-2"></i>Connect
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Nearby Professionals Section -->
    <?php if (!empty($nearbyProfessionals)): ?>
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Professionals Near <?php echo htmlspecialchars($userLocation); ?></h3>
            <a href="find-professionals.php?location=<?php echo urlencode($userLocation); ?>" class="btn btn-outline-primary btn-sm">View More</a>
        </div>
        
        <div class="row">
            <?php foreach ($nearbyProfessionals as $professional): ?>
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
                            
                            <p class="mb-2">
                                <i class="fas fa-map-marker-alt text-secondary me-2"></i>
                                <?php echo htmlspecialchars($professional['location']); ?>
                            </p>
                            
                            <?php if ($professional['sia_license_type']): ?>
                                <p class="mb-2">
                                    <i class="fas fa-id-card text-secondary me-2"></i>
                                    SIA: <?php echo htmlspecialchars($professional['sia_license_type']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <!-- Connection actions -->
                            <?php if ($professional['connection_status'] === 'accepted'): ?>
                                <div class="d-grid gap-2">
                                    <a href="profile.php?id=<?php echo $professional['id']; ?>" class="btn btn-outline-primary">View Profile</a>
                                    <a href="chat.php?with=<?php echo $professional['id']; ?>" class="btn btn-outline-success">
                                        <i class="fas fa-comment me-2"></i>Message
                                    </a>
                                </div>
                            <?php elseif ($professional['connection_status'] === 'pending' && $professional['is_requester']): ?>
                                <div class="d-grid gap-2">
                                    <a href="profile.php?id=<?php echo $professional['id']; ?>" class="btn btn-outline-primary">View Profile</a>
                                    <button class="btn btn-secondary" disabled>Connection Requested</button>
                                </div>
                            <?php elseif ($professional['connection_status'] === 'pending' && !$professional['is_requester']): ?>
                                <div class="d-grid gap-2">
                                    <a href="profile.php?id=<?php echo $professional['id']; ?>" class="btn btn-outline-primary">View Profile</a>
                                    <div class="btn-group w-100">
                                        <form action="../includes/ajax/connection.php" method="post" class="w-50">
                                            <input type="hidden" name="requester_id" value="<?php echo $professional['id']; ?>">
                                            <input type="hidden" name="action" value="accept">
                                            <button type="submit" class="btn btn-success w-100">Accept</button>
                                        </form>
                                        <form action="../includes/ajax/connection.php" method="post" class="w-50">
                                            <input type="hidden" name="requester_id" value="<?php echo $professional['id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn btn-danger w-100">Reject</button>
                                        </form>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="d-grid gap-2">
                                    <a href="profile.php?id=<?php echo $professional['id']; ?>" class="btn btn-outline-primary">View Profile</a>
                                    <form action="../includes/ajax/connection.php" method="post">
                                        <input type="hidden" name="receiver_id" value="<?php echo $professional['id']; ?>">
                                        <input type="hidden" name="action" value="connect">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-user-plus me-2"></i>Connect
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- New Professionals Section -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>New Security Professionals</h3>
            <a href="find-professionals.php" class="btn btn-outline-primary btn-sm">View More</a>
        </div>
        
        <div class="row">
            <?php foreach ($newProfessionals as $professional): ?>
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
                            
                            <!-- Connection actions -->
                            <?php if ($professional['connection_status'] === 'accepted'): ?>
                                <div class="d-grid gap-2">
                                    <a href="profile.php?id=<?php echo $professional['id']; ?>" class="btn btn-outline-primary">View Profile</a>
                                    <a href="chat.php?with=<?php echo $professional['id']; ?>" class="btn btn-outline-success">
                                        <i class="fas fa-comment me-2"></i>Message
                                    </a>
                                </div>
                            <?php elseif ($professional['connection_status'] === 'pending' && $professional['is_requester']): ?>
                                <div class="d-grid gap-2">
                                    <a href="profile.php?id=<?php echo $professional['id']; ?>" class="btn btn-outline-primary">View Profile</a>
                                    <button class="btn btn-secondary" disabled>Connection Requested</button>
                                </div>
                            <?php elseif ($professional['connection_status'] === 'pending' && !$professional['is_requester']): ?>
                                <div class="d-grid gap-2">
                                    <a href="profile.php?id=<?php echo $professional['id']; ?>" class="btn btn-outline-primary">View Profile</a>
                                    <div class="btn-group w-100">
                                        <form action="../includes/ajax/connection.php" method="post" class="w-50">
                                            <input type="hidden" name="requester_id" value="<?php echo $professional['id']; ?>">
                                            <input type="hidden" name="action" value="accept">
                                            <button type="submit" class="btn btn-success w-100">Accept</button>
                                        </form>
                                        <form action="../includes/ajax/connection.php" method="post" class="w-50">
                                            <input type="hidden" name="requester_id" value="<?php echo $professional['id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn btn-danger w-100">Reject</button>
                                        </form>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="d-grid gap-2">
                                    <a href="profile.php?id=<?php echo $professional['id']; ?>" class="btn btn-outline-primary">View Profile</a>
                                    <form action="../includes/ajax/connection.php" method="post">
                                        <input type="hidden" name="receiver_id" value="<?php echo $professional['id']; ?>">
                                        <input type="hidden" name="action" value="connect">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-user-plus me-2"></i>Connect
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add animations
    anime({
        targets: '.card',
        translateY: [20, 0],
        opacity: [0, 1],
        delay: anime.stagger(100),
        easing: 'easeOutCubic',
        duration: 800
    });
});
</script>

<?php require_once 'footer.php'; ?>