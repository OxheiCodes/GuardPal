<?php
$pageTitle = 'Edit Agency Profile - GuardPal';
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

$success = '';
$error = '';

// Get current agency data
$stmt = $conn->prepare("SELECT * FROM agencies WHERE id = ?");
$stmt->execute([$agencyId]);
$agencyData = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $phone = sanitizeInput($_POST['phone']);
    $website = sanitizeInput($_POST['website']);
    $address = sanitizeInput($_POST['address']);
    $description = sanitizeInput($_POST['description']);
    
    // Basic validation
    if (empty($name)) {
        $error = 'Agency name is required';
    } else {
        try {
            // Process logo upload if provided
            $logoImage = $agencyData['logo_image'];
            
            if (!empty($_FILES['logo_image']['name'])) {
                $uploadDir = '../uploads/agencies/';
                
                // Create directory if it doesn't exist
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileExtension = strtolower(pathinfo($_FILES['logo_image']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (!in_array($fileExtension, $allowedExtensions)) {
                    $error = 'Invalid image format. Allowed formats: JPG, JPEG, PNG, GIF';
                } else {
                    $fileName = 'agency_' . $agencyId . '_' . time() . '.' . $fileExtension;
                    $targetFile = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['logo_image']['tmp_name'], $targetFile)) {
                        $logoImage = $targetFile;
                    } else {
                        $error = 'Failed to upload image. Please try again.';
                    }
                }
            }
            
            if (empty($error)) {
                $stmt = $conn->prepare("
                    UPDATE agencies SET 
                    name = ?, 
                    phone = ?, 
                    website = ?, 
                    address = ?, 
                    description = ?, 
                    logo_image = ?
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $name,
                    $phone,
                    $website,
                    $address,
                    $description,
                    $logoImage,
                    $agencyId
                ]);
                
                $success = 'Agency profile updated successfully';
                
                // Update session agency name
                $_SESSION['agency_name'] = $name;
                
                // Refresh agency data
                $stmt = $conn->prepare("SELECT * FROM agencies WHERE id = ?");
                $stmt->execute([$agencyId]);
                $agencyData = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            $error = 'Error updating profile: ' . $e->getMessage();
        }
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit Agency Profile</h5>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3 text-center">
                            <img src="<?php echo $agencyData['logo_image'] ?: 'https://placehold.co/200x200?text=Agency+Logo'; ?>" 
                                 alt="Agency Logo" class="img-thumbnail" 
                                 style="max-width: 200px;" id="logo-preview">
                            <div class="mt-2">
                                <label for="logo_image" class="btn btn-outline-primary">
                                    <i class="fas fa-upload me-2"></i>Change Agency Logo
                                </label>
                                <input type="file" id="logo_image" name="logo_image" class="d-none" accept="image/*">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Agency Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($agencyData['name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" 
                                   value="<?php echo htmlspecialchars($agencyData['email']); ?>" disabled>
                            <small class="text-muted">Email cannot be changed</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($agencyData['phone']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="website" class="form-label">Website</label>
                            <input type="url" class="form-control" id="website" name="website" 
                                   value="<?php echo htmlspecialchars($agencyData['website']); ?>" 
                                   placeholder="https://example.com">
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($agencyData['address']); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Agency Description</label>
                            <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($agencyData['description']); ?></textarea>
                        </div>
                        
                        <div class="text-end">
                            <a href="agency_dashboard.php" class="btn btn-outline-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview logo image before upload
    const logoImageInput = document.getElementById('logo_image');
    const logoPreview = document.getElementById('logo-preview');
    
    logoImageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                logoPreview.src = e.target.result;
            };
            
            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>

<?php require_once 'footer.php'; ?>