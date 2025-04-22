<?php
$pageTitle = 'Bookmarks - GuardPal';
require_once 'header.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireLogin();

$conn = getDBConnection();

// Get user's bookmarks
$stmt = $conn->prepare("SELECT * FROM bookmarks WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$bookmarks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h2 class="mb-4">Your Saved Jobs</h2>
    
    <?php if (!empty($bookmarks)): ?>
        <div class="row">
            <?php foreach ($bookmarks as $bookmark): ?>
                <div class="col-md-6 mb-4">
                    <div class="card job-card card-hover">
                        <div class="card-body job-card-body position-relative">
                            <button class="btn btn-link bookmark-btn bookmarked" data-job-id="<?php echo htmlspecialchars($bookmark['job_id']); ?>">
                                <i class="fas fa-bookmark fa-lg"></i>
                            </button>
                            
                            <h5 class="job-title"><?php echo htmlspecialchars($bookmark['job_title']); ?></h5>
                            <p class="company-name mb-2"><?php echo htmlspecialchars($bookmark['company_name']); ?></p>
                            
                            <p class="card-text small">
                                <?php echo htmlspecialchars(substr($bookmark['job_description'], 0, 150)) . '...'; ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <a href="job-details.php?id=<?php echo urlencode($bookmark['job_id']); ?>" 
                                   class="btn btn-primary">View Details</a>
                                <small class="text-muted">Saved on <?php echo date('M j, Y', strtotime($bookmark['created_at'])); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            You haven't saved any jobs yet. Start by <a href="search.php">searching for jobs</a>.
        </div>
    <?php endif; ?>
</div>

<script src="../assets/js/bookmarks.js"></script>
<?php require_once 'footer.php'; ?>