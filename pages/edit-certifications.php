<?php
$pageTitle = 'Manage Certifications - GuardPal';
require_once 'header.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireLogin();

$userId = $_SESSION['user_id'];
$conn = getDBConnection();

$success = '';
$error = '';

// Add certification
if (isset($_POST['add_certification'])) {
    $certName = sanitizeInput($_POST['cert_name']);
    $issuingOrg = sanitizeInput($_POST['issuing_organization']);
    $issueDate = sanitizeInput($_POST['issue_date']);
    $expiryDate = !empty($_POST['expiry_date']) ? sanitizeInput($_POST['expiry_date']) : null;
    $credentialId = sanitizeInput($_POST['credential_id']);
    $credentialUrl = sanitizeInput($_POST['credential_url']);
    
    if (empty($certName) || empty($issuingOrg) || empty($issueDate)) {
        $error = 'Name, issuing organization and issue date are required';
    } else {
        try {
            $stmt = $conn->prepare("
                INSERT INTO certifications 
                (user_id, name, issuing_organization, issue_date, expiry_date, credential_id, credential_url) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $certName, $issuingOrg, $issueDate, $expiryDate, $credentialId, $credentialUrl]);
            $success = 'Certification added successfully';
        } catch (Exception $e) {
            $error = 'Error adding certification: ' . $e->getMessage();
        }
    }
}

// Delete certification
if (isset($_POST['delete_certification'])) {
    $certId = (int)$_POST['cert_id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM certifications WHERE id = ? AND user_id = ?");
        $stmt->execute([$certId, $userId]);
        $success = 'Certification deleted successfully';
    } catch (Exception $e) {
        $error = 'Error deleting certification: ' . $e->getMessage();
    }
}

// Get all certifications
$stmt = $conn->prepare("SELECT * FROM certifications WHERE user_id = ? ORDER BY issue_date DESC");
$stmt->execute([$userId]);
$certifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Certifications</h5>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($certifications)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Certification</th>
                                        <th>Issuing Organization</th>
                                        <th>Issue Date</th>
                                        <th>Expiry Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($certifications as $cert): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($cert['name']); ?></td>
                                            <td><?php echo htmlspecialchars($cert['issuing_organization']); ?></td>
                                            <td><?php echo date('M Y', strtotime($cert['issue_date'])); ?></td>
                                            <td>
                                                <?php echo $cert['expiry_date'] ? date('M Y', strtotime($cert['expiry_date'])) : 'N/A'; ?>
                                            </td>
                                            <td>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="cert_id" value="<?php echo $cert['id']; ?>">
                                                    <button type="submit" name="delete_certification" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Are you sure you want to delete this certification?')">
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
                        <p class="text-muted">You haven't added any certifications yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Add New Certification</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="cert_name" class="form-label">Certification Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="cert_name" name="cert_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="issuing_organization" class="form-label">Issuing Organization <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="issuing_organization" name="issuing_organization" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="issue_date" class="form-label">Issue Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="issue_date" name="issue_date" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="expiry_date" class="form-label">Expiry Date</label>
                            <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                            <small class="text-muted">Leave blank if it doesn't expire</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="credential_id" class="form-label">Credential ID</label>
                            <input type="text" class="form-control" id="credential_id" name="credential_id">
                        </div>
                        
                        <div class="mb-3">
                            <label for="credential_url" class="form-label">Credential URL</label>
                            <input type="url" class="form-control" id="credential_url" name="credential_url" 
                                   placeholder="https://example.com/verify">
                        </div>
                        
                        <button type="submit" name="add_certification" class="btn btn-primary w-100">Add Certification</button>
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