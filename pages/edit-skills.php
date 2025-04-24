<?php
$pageTitle = 'Manage Skills - GuardPal';
require_once 'header.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireLogin();

$userId = $_SESSION['user_id'];
$conn = getDBConnection();

$success = '';
$error = '';

// Add skill
if (isset($_POST['add_skill'])) {
    $skillName = sanitizeInput($_POST['skill_name']);
    $proficiency = sanitizeInput($_POST['proficiency']);
    
    if (empty($skillName)) {
        $error = 'Skill name is required';
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO skills (user_id, name, proficiency) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $skillName, $proficiency]);
            $success = 'Skill added successfully';
        } catch (Exception $e) {
            $error = 'Error adding skill: ' . $e->getMessage();
        }
    }
}

// Delete skill
if (isset($_POST['delete_skill'])) {
    $skillId = (int)$_POST['skill_id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM skills WHERE id = ? AND user_id = ?");
        $stmt->execute([$skillId, $userId]);
        $success = 'Skill deleted successfully';
    } catch (Exception $e) {
        $error = 'Error deleting skill: ' . $e->getMessage();
    }
}

// Get all skills
$stmt = $conn->prepare("SELECT * FROM skills WHERE user_id = ? ORDER BY proficiency DESC");
$stmt->execute([$userId]);
$skills = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Skills</h5>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($skills)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Skill</th>
                                        <th>Proficiency</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($skills as $skill): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($skill['name']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $skill['proficiency'] === 'Expert' ? 'danger' : 
                                                        ($skill['proficiency'] === 'Advanced' ? 'warning' : 
                                                            ($skill['proficiency'] === 'Intermediate' ? 'info' : 'secondary')); 
                                                ?>"><?php echo htmlspecialchars($skill['proficiency']); ?></span>
                                            </td>
                                            <td>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="skill_id" value="<?php echo $skill['id']; ?>">
                                                    <button type="submit" name="delete_skill" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Are you sure you want to delete this skill?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">You haven't added any skills yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Add New Skill</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="skill_name" class="form-label">Skill Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="skill_name" name="skill_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="proficiency" class="form-label">Proficiency Level</label>
                            <select class="form-select" id="proficiency" name="proficiency" required>
                                <option value="Beginner">Beginner</option>
                                <option value="Intermediate">Intermediate</option>
                                <option value="Advanced">Advanced</option>
                                <option value="Expert">Expert</option>
                            </select>
                        </div>
                        
                        <button type="submit" name="add_skill" class="btn btn-primary w-100">Add Skill</button>
                    </form>
                </div>
            </div>
            
            <div class="mt-3 d-grid">
                <a href="profile.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Profile
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>