<?php
$pageTitle = 'My Network - GuardPal';
require_once 'header.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireLogin();

$userId = $_SESSION['user_id'];
$conn = getDBConnection();

// Get accepted connections
$stmt = $conn->prepare("
    SELECT c.*, 
           u.id as connection_id,
           u.username, 
           u.full_name, 
           u.job_title,
           u.profile_image
    FROM connections c
    JOIN users u ON (
        (c.requester_id = ? AND c.receiver_id = u.id) OR 
        (c.receiver_id = ? AND c.requester_id = u.id)
    )
    WHERE c.status = 'accepted'
    ORDER BY u.full_name
");
$stmt->execute([$userId, $userId]);
$connections = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get pending connection requests (sent)
$stmt = $conn->prepare("
    SELECT c.*, 
           u.id as connection_id,
           u.username, 
           u.full_name, 
           u.job_title,
           u.profile_image
    FROM connections c
    JOIN users u ON c.receiver_id = u.id
    WHERE c.requester_id = ? AND c.status = 'pending'
    ORDER BY c.created_at DESC
");
$stmt->execute([$userId]);
$sentRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get pending connection requests (received)
$stmt = $conn->prepare("
    SELECT c.*, 
           u.id as connection_id,
           u.username, 
           u.full_name, 
           u.job_title,
           u.profile_image
    FROM connections c
    JOIN users u ON c.requester_id = u.id
    WHERE c.receiver_id = ? AND c.status = 'pending'
    ORDER BY c.created_at DESC
");
$stmt->execute([$userId]);
$receivedRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h2 class="mb-4">My Network</h2>
    
    <!-- Connection Requests Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="connectionsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="connections-tab" data-bs-toggle="tab" 
                    data-bs-target="#connections" type="button" role="tab">
                My Connections <span class="badge bg-primary ms-1"><?php echo count($connections); ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="requests-tab" data-bs-toggle="tab" 
                    data-bs-target="#requests" type="button" role="tab">
                Received Requests <span class="badge bg-danger ms-1"><?php echo count($receivedRequests); ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="sent-tab" data-bs-toggle="tab" 
                    data-bs-target="#sent" type="button" role="tab">
                Sent Requests <span class="badge bg-secondary ms-1"><?php echo count($sentRequests); ?></span>
            </button>
        </li>
    </ul>
    
    <!-- Tab Content -->
    <div class="tab-content" id="connectionsTabContent">
        <!-- Connections Tab -->
        <div class="tab-pane fade show active" id="connections" role="tabpanel">
            <div class="row">
                <?php if (!empty($connections)): ?>
                    <?php foreach ($connections as $connection): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <img src="<?php echo $connection['profile_image'] ?: 'https://placehold.co/100x100?text=Profile'; ?>" 
                                         alt="Profile" class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                                    <h5 class="card-title"><?php echo htmlspecialchars($connection['full_name']); ?></h5>
                                    <p class="card-text text-muted"><?php echo htmlspecialchars($connection['job_title'] ?: 'Security Professional'); ?></p>
                                    <div class="d-grid gap-2">
                                        <a href="profile.php?id=<?php echo $connection['connection_id']; ?>" class="btn btn-outline-primary">View Profile</a>
                                        <a href="chat.php?with=<?php echo $connection['connection_id']; ?>" class="btn btn-outline-success">Message</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            You don't have any connections yet. Connect with other security professionals to grow your network.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Received Requests Tab -->
        <div class="tab-pane fade" id="requests" role="tabpanel">
            <div class="row">
                <?php if (!empty($receivedRequests)): ?>
                    <?php foreach ($receivedRequests as $request): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <img src="<?php echo $request['profile_image'] ?: 'https://placehold.co/100x100?text=Profile'; ?>" 
                                         alt="Profile" class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                                    <h5 class="card-title"><?php echo htmlspecialchars($request['full_name']); ?></h5>
                                    <p class="card-text text-muted"><?php echo htmlspecialchars($request['job_title'] ?: 'Security Professional'); ?></p>
                                    <div class="d-flex justify-content-center">
                                        <form action="../includes/ajax/connection.php" method="post" class="me-2">
                                            <input type="hidden" name="requester_id" value="<?php echo $request['connection_id']; ?>">
                                            <input type="hidden" name="action" value="accept">
                                            <button type="submit" class="btn btn-success">Accept</button>
                                        </form>
                                        <form action="../includes/ajax/connection.php" method="post">
                                            <input type="hidden" name="requester_id" value="<?php echo $request['connection_id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn btn-danger">Reject</button>
                                        </form>
                                    </div>
                                    <div class="mt-2">
                                        <a href="profile.php?id=<?php echo $request['connection_id']; ?>" class="btn btn-link">View Profile</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            You don't have any pending connection requests.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Sent Requests Tab -->
        <div class="tab-pane fade" id="sent" role="tabpanel">
            <div class="row">
                <?php if (!empty($sentRequests)): ?>
                    <?php foreach ($sentRequests as $request): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <img src="<?php echo $request['profile_image'] ?: 'https://placehold.co/100x100?text=Profile'; ?>" 
                                         alt="Profile" class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                                    <h5 class="card-title"><?php echo htmlspecialchars($request['full_name']); ?></h5>
                                    <p class="card-text text-muted"><?php echo htmlspecialchars($request['job_title'] ?: 'Security Professional'); ?></p>
                                    <div class="d-grid">
                                        <form action="../includes/ajax/connection.php" method="post">
                                            <input type="hidden" name="receiver_id" value="<?php echo $request['connection_id']; ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <button type="submit" class="btn btn-outline-danger">Cancel Request</button>
                                        </form>
                                    </div>
                                    <div class="mt-2">
                                        <a href="profile.php?id=<?php echo $request['connection_id']; ?>" class="btn btn-link">View Profile</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            You haven't sent any connection requests.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>