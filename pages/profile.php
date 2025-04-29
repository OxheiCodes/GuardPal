<?php
$pageTitle = 'User Profile - GuardPal';
require_once 'header.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireLogin();

$userId = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user_id'];
$isOwnProfile = ($userId === (int)$_SESSION['user_id']);
$conn = getDBConnection();

// Get user profile data
$stmt = $conn->prepare("
    SELECT u.*, 
           COUNT(DISTINCT c.id) as certification_count,
           COUNT(DISTINCT s.id) as skill_count,
           COUNT(DISTINCT w.id) as experience_count
    FROM users u
    LEFT JOIN certifications c ON u.id = c.user_id
    LEFT JOIN skills s ON u.id = s.user_id
    LEFT JOIN work_experience w ON u.id = w.user_id
    WHERE u.id = ?
    GROUP BY u.id
");
$stmt->execute([$userId]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userData) {
    header('Location: dashboard.php');
    exit();
}

// Get connection status
$connectionStatus = null;
if (!$isOwnProfile) {
    $stmt = $conn->prepare("
        SELECT status FROM connections 
        WHERE (requester_id = ? AND receiver_id = ?) 
        OR (requester_id = ? AND receiver_id = ?)
    ");
    $stmt->execute([$_SESSION['user_id'], $userId, $userId, $_SESSION['user_id']]);
    $connection = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($connection) {
        $connectionStatus = $connection['status'];
    }
    
    // Check if current user is the requester
    $stmt = $conn->prepare("
        SELECT 1 FROM connections 
        WHERE requester_id = ? AND receiver_id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $userId]);
    $isRequester = $stmt->fetchColumn();
}

// Get certifications
$stmt = $conn->prepare("SELECT * FROM certifications WHERE user_id = ? ORDER BY issue_date DESC");
$stmt->execute([$userId]);
$certifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get skills
$stmt = $conn->prepare("SELECT * FROM skills WHERE user_id = ? ORDER BY proficiency DESC");
$stmt->execute([$userId]);
$skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get work experience
$stmt = $conn->prepare("
    SELECT * FROM work_experience 
    WHERE user_id = ? 
    ORDER BY is_current DESC, end_date DESC, start_date DESC
");
$stmt->execute([$userId]);
$workExperience = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <div class="row">
        <!-- Profile Header -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-body d-md-flex align-items-center">
                    <div class="text-center me-md-4 mb-3 mb-md-0">
                        <img src="<?php echo $userData['profile_image'] ?: 'https://placehold.co/200x200?text=Profile'; ?>" 
                             alt="Profile Image" class="rounded-circle img-fluid" style="width: 150px; height: 150px; object-fit: cover;">
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h2 class="mb-1"><?php echo htmlspecialchars($userData['full_name']); ?></h2>
                                <p class="text-muted mb-2"><?php echo htmlspecialchars($userData['job_title'] ?: 'Security Professional'); ?></p>
                                <p class="mb-1">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    <?php echo htmlspecialchars($userData['location'] ?: 'Location not specified'); ?>
                                </p>
                                <p class="mb-1">
                                    <i class="fas fa-briefcase me-2"></i>
                                    <?php echo htmlspecialchars($userData['years_experience'] ?: '0'); ?> years of experience
                                </p>
                                <p class="mb-1">
                                    <i class="fas fa-calendar-check me-2"></i>
                                    Status: <span class="badge bg-<?php 
                                        echo $userData['availability'] === 'Available' ? 'success' : 
                                            ($userData['availability'] === 'Open to Opportunities' ? 'warning' : 'secondary'); 
                                    ?>">
                                        <?php echo htmlspecialchars($userData['availability'] ?: 'Not Specified'); ?>
                                    </span>
                                </p>
                                
                                <?php if ($userData['sia_license_number'] && ($isOwnProfile || $connectionStatus === 'accepted')): ?>
                                    <div class="mt-3 p-3 bg-light rounded">
                                        <h5>SIA License Information</h5>
                                        <p class="mb-1">
                                            <strong>License Number:</strong> 
                                            <?php 
                                                $formattedSIA = chunk_split($userData['sia_license_number'], 4, ' ');
                                                echo htmlspecialchars(trim($formattedSIA)); 
                                            ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>License Type:</strong> 
                                            <?php echo htmlspecialchars($userData['sia_license_type'] ?: 'Not specified'); ?>
                                        </p>
                                        <?php if ($userData['sia_expiry_date']): ?>
                                            <p class="mb-0">
                                                <strong>Expires:</strong> 
                                                <?php echo date('d M Y', strtotime($userData['sia_expiry_date'])); ?>
                                                
                                                <?php
                                                $expiryDate = new DateTime($userData['sia_expiry_date']);
                                                $today = new DateTime();
                                                $interval = $today->diff($expiryDate);
                                                $daysRemaining = $interval->days;
                                                $isExpired = $today > $expiryDate;
                                                
                                                if ($isExpired): ?>
                                                    <span class="badge bg-danger ms-2">Expired</span>
                                                <?php elseif ($daysRemaining <= 30): ?>
                                                    <span class="badge bg-warning ms-2">Expires soon (<?php echo $daysRemaining; ?> days)</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success ms-2">Valid</span>
                                                <?php endif; ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($isOwnProfile): ?>
                                <a href="edit-profile.php" class="btn btn-primary">
                                    <i class="fas fa-edit me-2"></i>Edit Profile
                                </a>
                            <?php elseif ($connectionStatus === null): ?>
                                <form action="../includes/ajax/connection.php" method="post" class="connect-form">
                                    <input type="hidden" name="receiver_id" value="<?php echo $userId; ?>">
                                    <input type="hidden" name="action" value="connect">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-user-plus me-2"></i>Connect
                                    </button>
                                </form>
                            <?php elseif ($connectionStatus === 'pending' && $isRequester): ?>
                                <button class="btn btn-secondary" disabled>Connection Requested</button>
                            <?php elseif ($connectionStatus === 'pending' && !$isRequester): ?>
                                <div class="d-flex">
                                    <form action="../includes/ajax/connection.php" method="post" class="me-2">
                                        <input type="hidden" name="requester_id" value="<?php echo $userId; ?>">
                                        <input type="hidden" name="action" value="accept">
                                        <button type="submit" class="btn btn-success">Accept</button>
                                    </form>
                                    <form action="../includes/ajax/connection.php" method="post">
                                        <input type="hidden" name="requester_id" value="<?php echo $userId; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-danger">Reject</button>
                                    </form>
                                </div>
                            <?php elseif ($connectionStatus === 'accepted'): ?>
                                <div class="d-flex">
                                    <span class="btn btn-success me-2" disabled>
                                        <i class="fas fa-check me-2"></i>Connected
                                    </span>
                                    <a href="chat.php?with=<?php echo $userId; ?>" class="btn btn-primary">
                                        <i class="fas fa-comment me-2"></i>Message
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($userData['bio']): ?>
                            <div class="mt-3">
                                <h5>About</h5>
                                <p><?php echo nl2br(htmlspecialchars($userData['bio'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Skills Section -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Skills</h5>
                    <?php if ($isOwnProfile): ?>
                        <a href="edit-skills.php" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($skills)): ?>
                        <div class="row">
                            <?php foreach ($skills as $skill): ?>
                                <div class="col-12 mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span><?php echo htmlspecialchars($skill['name']); ?></span>
                                        <span class="badge bg-<?php 
                                            echo $skill['proficiency'] === 'Expert' ? 'danger' : 
                                                ($skill['proficiency'] === 'Advanced' ? 'warning' : 
                                                    ($skill['proficiency'] === 'Intermediate' ? 'info' : 'secondary')); 
                                        ?>"><?php echo htmlspecialchars($skill['proficiency']); ?></span>
                                    </div>
                                    <div class="progress mt-1" style="height: 5px;">
                                        <div class="progress-bar bg-<?php 
                                            echo $skill['proficiency'] === 'Expert' ? 'danger' : 
                                                ($skill['proficiency'] === 'Advanced' ? 'warning' : 
                                                    ($skill['proficiency'] === 'Intermediate' ? 'info' : 'secondary')); 
                                        ?>" role="progressbar" style="width: <?php 
                                            echo $skill['proficiency'] === 'Expert' ? '100%' : 
                                                ($skill['proficiency'] === 'Advanced' ? '75%' : 
                                                    ($skill['proficiency'] === 'Intermediate' ? '50%' : '25%')); 
                                        ?>" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No skills added yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Certifications Section -->
        <div class="col-md-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Certifications</h5>
                    <?php if ($isOwnProfile): ?>
                        <a href="edit-certifications.php" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($certifications)): ?>
                        <div class="row">
                            <?php foreach ($certifications as $cert): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title"><?php echo htmlspecialchars($cert['name']); ?></h6>
                                            <p class="card-text small mb-1">
                                                <strong>Issuer:</strong> <?php echo htmlspecialchars($cert['issuing_organization']); ?>
                                            </p>
                                            <p class="card-text small mb-1">
                                                <strong>Issued:</strong> <?php echo date('M Y', strtotime($cert['issue_date'])); ?>
                                            </p>
                                            <?php if ($cert['expiry_date']): ?>
                                                <p class="card-text small mb-1">
                                                    <strong>Expires:</strong> <?php echo date('M Y', strtotime($cert['expiry_date'])); ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if ($cert['credential_url']): ?>
                                                <a href="<?php echo htmlspecialchars($cert['credential_url']); ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">Verify</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No certifications added yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Work Experience Section -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Work Experience</h5>
                    <?php if ($isOwnProfile): ?>
                        <a href="edit-experience.php" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($workExperience)): ?>
                        <div class="timeline">
                            <?php foreach ($workExperience as $experience): ?>
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <div class="text-primary fw-bold">
                                            <?php echo date('M Y', strtotime($experience['start_date'])); ?> - 
                                            <?php echo $experience['is_current'] ? 'Present' : date('M Y', strtotime($experience['end_date'])); ?>
                                        </div>
                                        <?php 
                                        if ($experience['is_current']) {
                                            $startDate = new DateTime($experience['start_date']);
                                            $endDate = new DateTime();
                                            $interval = $startDate->diff($endDate);
                                            $duration = '';
                                            if ($interval->y > 0) {
                                                $duration .= $interval->y . ' yr' . ($interval->y > 1 ? 's' : '');
                                            }
                                            if ($interval->m > 0) {
                                                $duration .= ($duration ? ' ' : '') . $interval->m . ' mo' . ($interval->m > 1 ? 's' : '');
                                            }
                                        } else {
                                            $startDate = new DateTime($experience['start_date']);
                                            $endDate = new DateTime($experience['end_date']);
                                            $interval = $startDate->diff($endDate);
                                            $duration = '';
                                            if ($interval->y > 0) {
                                                $duration .= $interval->y . ' yr' . ($interval->y > 1 ? 's' : '');
                                            }
                                            if ($interval->m > 0) {
                                                $duration .= ($duration ? ' ' : '') . $interval->m . ' mo' . ($interval->m > 1 ? 's' : '');
                                            }
                                        }
                                        ?>
                                        <div class="text-muted">
                                            <?php echo $duration; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($experience['job_title']); ?></h5>
                                        <div class="fs-6 mb-2"><?php echo htmlspecialchars($experience['company_name']); ?></div>
                                        <div class="text-muted mb-2">
                                            <i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($experience['location'] ?: 'Location not specified'); ?>
                                        </div>
                                        <?php if ($experience['description']): ?>
                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($experience['description'])); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No work experience added yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Similar Professionals Section (if viewing own profile) -->
        <?php if ($isOwnProfile): ?>
            <?php
            // Get similar professionals based on skills
            $stmt = $conn->prepare("
                SELECT DISTINCT u.id, u.full_name, u.job_title, u.location, u.profile_image
                FROM users u
                JOIN skills us ON u.id = us.user_id
                JOIN skills my ON my.user_id = ?
                WHERE u.id != ? AND us.name = my.name
                GROUP BY u.id
                ORDER BY COUNT(us.id) DESC
                LIMIT 3
            ");
            $stmt->execute([$userId, $userId]);
            $similarProfessionals = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($similarProfessionals)):
            ?>
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Similar Professionals</h5>
                        <a href="find-professionals.php" class="btn btn-sm btn-outline-primary">Find More</a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($similarProfessionals as $professional): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <img src="<?php echo $professional['profile_image'] ?: 'https://placehold.co/100x100?text=Profile'; ?>" 
                                                 alt="Profile" class="rounded-circle mb-3" style="width: 80px; height: 80px; object-fit: cover;">
                                            <h5 class="card-title"><?php echo htmlspecialchars($professional['full_name']); ?></h5>
                                            <p class="card-text text-muted"><?php echo htmlspecialchars($professional['job_title'] ?: 'Security Professional'); ?></p>
                                            <?php if ($professional['location']): ?>
                                                <p class="mb-2 small">
                                                    <i class="fas fa-map-marker-alt me-1"></i> 
                                                    <?php echo htmlspecialchars($professional['location']); ?>
                                                </p>
                                            <?php endif; ?>
                                            <a href="profile.php?id=<?php echo $professional['id']; ?>" class="btn btn-sm btn-outline-primary">View Profile</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script src="../assets/js/profile.js"></script>
<?php require_once 'footer.php'; ?>