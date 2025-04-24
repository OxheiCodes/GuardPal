<?php
$pageTitle = 'Edit Profile - GuardPal';
require_once 'header.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

requireLogin();

$userId = $_SESSION['user_id'];
$conn = getDBConnection();

$success = '';
$error = '';

// Get current user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = sanitizeInput($_POST['full_name']);
    $jobTitle = sanitizeInput($_POST['job_title']);
    $yearsExperience = (int)$_POST['years_experience'];
    $location = sanitizeInput($_POST['location']);
    $phone = sanitizeInput($_POST['phone']);
    $website = sanitizeInput($_POST['website']);
    $availability = sanitizeInput($_POST['availability']);
    $bio = sanitizeInput($_POST['bio']);
    $siaLicenseNumber = sanitizeInput($_POST['sia_license_number']);
    $siaLicenseType = sanitizeInput($_POST['sia_license_type']);
    $siaExpiryDate = !empty($_POST['sia_expiry_date']) ? sanitizeInput($_POST['sia_expiry_date']) : null;
    
    // Basic validation
    if (empty($fullName)) {
        $error = 'Full name is required';
    } elseif (!empty($siaLicenseNumber) && (!preg_match('/^\d{16}$/', $siaLicenseNumber))) {
        $error = 'SIA License Number must be exactly 16 digits with numbers only';
    } else {
        // Process profile image upload if provided
        $profileImage = $userData['profile_image'];
        
        if (!empty($_FILES['profile_image']['name'])) {
            $uploadDir = '../uploads/profiles/';
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExtension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                $error = 'Invalid image format. Allowed formats: JPG, JPEG, PNG, GIF';
            } else {
                $fileName = $userId . '_' . time() . '.' . $fileExtension;
                $targetFile = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
                    $profileImage = $targetFile;
                } else {
                    $error = 'Failed to upload image. Please try again.';
                }
            }
        }
        
        if (empty($error)) {
            try {
                $stmt = $conn->prepare("
                    UPDATE users SET 
                    full_name = ?, 
                    job_title = ?, 
                    years_experience = ?, 
                    location = ?, 
                    phone = ?, 
                    website = ?, 
                    availability = ?, 
                    bio = ?, 
                    profile_image = ?,
                    sia_license_number = ?,
                    sia_license_type = ?,
                    sia_expiry_date = ?
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $fullName,
                    $jobTitle,
                    $yearsExperience,
                    $location,
                    $phone,
                    $website,
                    $availability,
                    $bio,
                    $profileImage,
                    $siaLicenseNumber,
                    $siaLicenseType,
                    $siaExpiryDate,
                    $userId
                ]);
                
                $success = 'Profile updated successfully';
                
                // Update session user name
                $_SESSION['user_name'] = $fullName;
                
                // Refresh user data
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $error = 'Error updating profile: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit Profile</h5>
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
                            <img src="<?php echo $userData['profile_image'] ?: 'https://placehold.co/200x200?text=Profile'; ?>" 
                                 alt="Profile Image" class="rounded-circle img-thumbnail" 
                                 style="width: 150px; height: 150px; object-fit: cover;" id="profile-preview">
                            <div class="mt-2">
                                <label for="profile_image" class="btn btn-outline-primary">
                                    <i class="fas fa-upload me-2"></i>Change Profile Picture
                                </label>
                                <input type="file" id="profile_image" name="profile_image" class="d-none" accept="image/*">
                            </div>
                        </div>
                        
                        <h5 class="mb-3 border-bottom pb-2">Personal Information</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($userData['full_name']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="job_title" class="form-label">Job Title</label>
                                <input type="text" class="form-control" id="job_title" name="job_title" 
                                       value="<?php echo htmlspecialchars($userData['job_title'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" 
                                       value="<?php echo htmlspecialchars($userData['email']); ?>" disabled>
                                <small class="text-muted">Email cannot be changed</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="years_experience" class="form-label">Years of Experience</label>
                                <input type="number" class="form-control" id="years_experience" name="years_experience" 
                                       value="<?php echo htmlspecialchars($userData['years_experience'] ?? 0); ?>" min="0">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" 
                                       value="<?php echo htmlspecialchars($userData['location'] ?? ''); ?>" 
                                       placeholder="City, State, Country">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="website" class="form-label">Website</label>
                                <input type="url" class="form-control" id="website" name="website" 
                                       value="<?php echo htmlspecialchars($userData['website'] ?? ''); ?>" 
                                       placeholder="https://example.com">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="availability" class="form-label">Availability Status</label>
                                <select class="form-select" id="availability" name="availability">
                                    <option value="">-- Select Status --</option>
                                    <option value="Available" <?php echo ($userData['availability'] ?? '') === 'Available' ? 'selected' : ''; ?>>
                                        Available for Work
                                    </option>
                                    <option value="Open to Opportunities" <?php echo ($userData['availability'] ?? '') === 'Open to Opportunities' ? 'selected' : ''; ?>>
                                        Open to Opportunities
                                    </option>
                                    <option value="Not Available" <?php echo ($userData['availability'] ?? '') === 'Not Available' ? 'selected' : ''; ?>>
                                        Not Available
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <h5 class="mt-4 mb-3 border-bottom pb-2">SIA License Information</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sia_license_number" class="form-label">SIA License Number</label>
                                <input type="text" class="form-control" id="sia_license_number" name="sia_license_number" 
                                       value="<?php echo htmlspecialchars($userData['sia_license_number'] ?? ''); ?>"
                                       pattern="\d{16}" maxlength="16"
                                       placeholder="16-digit license number">
                                <small class="text-muted">Enter your 16-digit SIA license number (numbers only)</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="sia_license_type" class="form-label">License Type</label>
                                <select class="form-select" id="sia_license_type" name="sia_license_type">
                                    <option value="">-- Select License Type --</option>
                                    <option value="Door Supervision" <?php echo ($userData['sia_license_type'] ?? '') === 'Door Supervision' ? 'selected' : ''; ?>>
                                        Door Supervision
                                    </option>
                                    <option value="Security Guarding" <?php echo ($userData['sia_license_type'] ?? '') === 'Security Guarding' ? 'selected' : ''; ?>>
                                        Security Guarding
                                    </option>
                                    <option value="CCTV" <?php echo ($userData['sia_license_type'] ?? '') === 'CCTV' ? 'selected' : ''; ?>>
                                        CCTV (Public Space Surveillance)
                                    </option>
                                    <option value="Close Protection" <?php echo ($userData['sia_license_type'] ?? '') === 'Close Protection' ? 'selected' : ''; ?>>
                                        Close Protection
                                    </option>
                                    <option value="Cash and Valuables in Transit" <?php echo ($userData['sia_license_type'] ?? '') === 'Cash and Valuables in Transit' ? 'selected' : ''; ?>>
                                        Cash and Valuables in Transit
                                    </option>
                                    <option value="Key Holding" <?php echo ($userData['sia_license_type'] ?? '') === 'Key Holding' ? 'selected' : ''; ?>>
                                        Key Holding
                                    </option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="sia_expiry_date" class="form-label">License Expiry Date</label>
                                <input type="date" class="form-control" id="sia_expiry_date" name="sia_expiry_date" 
                                       value="<?php echo htmlspecialchars($userData['sia_expiry_date'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <h5 class="mt-4 mb-3 border-bottom pb-2">Professional Summary</h5>
                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo htmlspecialchars($userData['bio'] ?? ''); ?></textarea>
                            <small class="text-muted">Tell employers about your experience, skills, and career goals</small>
                        </div>
                        
                        <div class="text-end">
                            <a href="profile.php" class="btn btn-outline-secondary me-2">Cancel</a>
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
    // Preview profile image before upload
    const profileImageInput = document.getElementById('profile_image');
    const profilePreview = document.getElementById('profile-preview');
    
    profileImageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                profilePreview.src = e.target.result;
            };
            
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // SIA License Number Validation - Ensure only numbers
    const siaLicenseInput = document.getElementById('sia_license_number');
    siaLicenseInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').substring(0, 16);
    });
});
</script>

<?php require_once 'footer.php'; ?>