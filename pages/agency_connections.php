<?php
$pageTitle = 'Manage Connections - GuardPal';
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

// Handle connection actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)$_POST['user_id'];
    $action = $_POST['action'];
    
    if ($action === 'accept') {
        $stmt = $conn->prepare("
            UPDATE agency_connections 
            SET status = 'accepted' 
            WHERE agency_id = ? AND user_id = ? AND status = 'pending'
        ");
        $stmt->execute([$agencyId, $userId]);
    } elseif ($action === 'reject' || $action === 'remove') {
        $stmt = $conn->prepare("
            DELETE FROM agency_connections 
            WHERE agency_id = ? AND user_id = ?
        ");
        $stmt->execute([$agencyId, $userId]);
    }
}

// Get connections tab data
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'connected';

// Get connected users
$acceptedConnections = [];
if ($tab === 'connected') {
    $stmt = $conn->prepare("
        SELECT ac.*, 
               u.id as user_id, 
               u.full_name, 
               u.job_title, 
               u.profile_image, 
               u.location
        FROM agency_connections ac
        JOIN users u ON ac.user_id = u.id
        WHERE ac.agency_id = ? AND ac.status = 'accepted'
        ORDER BY u.full_name ASC
    ");
    $stmt->execute([$agencyId]);
    $acceptedConnections = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get pending connection requests
$pendingRequests = [];
if ($tab === 'pending') {
    $stmt = $conn->prepare("
        SELECT ac.*, 
               u.id as user_id, 
               u.full_name, 
               u.job_title, 
               u.profile_image, 
               u.location
        FROM agency_connections ac
        JOIN users u ON ac.user_id = u.id
        WHERE ac.agency_id = ? AND ac.status = 'pending'
        ORDER BY ac.created_at DESC
    ");
    $stmt->execute([$agencyId]);
    $pendingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container mt-4">
    <h2 class="mb-4">Manage Connections</h2>
    
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?php echo $tab === 'connected' ? 'active' : ''; ?>" href="?tab=connected">
                Connected Users
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $tab === 'pending' ? 'active' : ''; ?>" href="?tab=pending">
                Pending Requests
            </a>
        </li>
    </ul>
    
    <?php if ($tab === 'connected'): ?>
        <div class="row">
            <?php if (empty($acceptedConnections)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <p class="mb-0">You don't have any connections yet. Connect with security professionals to build your network.</p>
                    </div>
                    <div class="card">
                        <div class="card-body text-center p-5">
                            <div class="mb-3">
                                <i class="fas fa-users fa-4x text-muted"></i>
                            </div>
                            <h4>Start Building Your Network</h4>
                            <p class="text-muted mb-4">Connect with security professionals to find the right candidates for your jobs</p>
                            <a href="agency_find_professionals.php" class="btn btn-primary">Find Professionals</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($acceptedConnections as $connection): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <img src="<?php echo $connection['profile_image'] ?: 'https://placehold.co/150x150?text=Profile'; ?>" 
                                     alt="Profile" class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                                <h5 class="card-title"><?php echo htmlspecialchars($connection['full_name']); ?></h5>
                                <p class="card-text text-muted"><?php echo htmlspecialchars($connection['job_title'] ?: 'Security Professional'); ?></p>
                                
                                <?php if ($connection['location']): ?>
                                    <p class="mb-3 small">
                                        <i class="fas fa-map-marker-alt me-1"></i> 
                                        <?php echo htmlspecialchars($connection['location']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="d-grid gap-2">
                                    <a href="profile.php?id=<?php echo $connection['user_id']; ?>" class="btn btn-outline-primary">View Profile</a>
                                    <form method="POST" action="" onsubmit="return confirm('Are you sure you want to remove this connection?');">
                                        <input type="hidden" name="user_id" value="<?php echo $connection['user_id']; ?>">
                                        <input type="hidden" name="action" value="remove">
                                        <button type="submit" class="btn btn-outline-danger w-100">
                                            <i class="fas fa-user-minus me-1"></i> Remove Connection
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php elseif ($tab === 'pending'): ?>
        <div class="row">
            <?php if (empty($pendingRequests)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <p class="mb-0">You don't have any pending connection requests.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($pendingRequests as $request): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="position-absolute top-0 end-0 p-2">
                                    <span class="badge bg-warning">Pending</span>
                                </div>
                                
                                <img src="<?php echo $request['profile_image'] ?: 'https://placehold.co/150x150?text=Profile'; ?>" 
                                     alt="Profile" class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                                <h5 class="card-title"><?php echo htmlspecialchars($request['full_name']); ?></h5>
                                <p class="card-text text-muted"><?php echo htmlspecialchars($request['job_title'] ?: 'Security Professional'); ?></p>
                                
                                <?php if ($request['location']): ?>
                                    <p class="mb-3 small">
                                        <i class="fas fa-map-marker-alt me-1"></i> 
                                        <?php echo htmlspecialchars($request['location']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="d-grid gap-2">
                                    <a href="profile.php?id=<?php echo $request['user_id']; ?>" class="btn btn-outline-primary">View Profile</a>
                                    <div class="btn-group">
                                        <form method="POST" action="" class="w-50">
                                            <input type="hidden" name="user_id" value="<?php echo $request['user_id']; ?>">
                                            <input type="hidden" name="action" value="accept">
                                            <button type="submit" class="btn btn-success w-100">Accept</button>
                                        </form>
                                        <form method="POST" action="" class="w-50">
                                            <input type="hidden" name="user_id" value="<?php echo $request['user_id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn btn-danger w-100">Reject</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>