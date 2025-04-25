<?php
session_start();
require_once 'includes/config.php';

// Redirect to dashboard if logged in
if (isset($_SESSION['user_id'])) {
    header('Location: pages/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GuardPal - Security Jobs Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">GuardPal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="pages/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/register.php">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 flex-grow-1">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4 mb-4">Find Security Jobs That Match Your Skills</h1>
                <p class="lead mb-4">GuardPal connects security professionals with top employers. Start your job search today!</p>
                <div class="features mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="feature-icon bg-primary text-white rounded-circle p-2 me-3">
                            <i class="fas fa-search"></i>
                        </div>
                        <span>Find security jobs tailored to your experience</span>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="feature-icon bg-primary text-white rounded-circle p-2 me-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <span>Connect with other security professionals</span>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="feature-icon bg-primary text-white rounded-circle p-2 me-3">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <span>Showcase your SIA license credentials</span>
                    </div>
                </div>
                <a href="pages/register.php" class="btn btn-primary btn-lg">Get Started</a>
                <a href="pages/login.php" class="btn btn-outline-primary btn-lg ms-2">Login</a>
            </div>
            <div class="col-md-6">
                <img src="https://placehold.co/600x400" alt="Security Professional" class="img-fluid rounded shadow">
            </div>
        </div>
        
        <div class="row mt-5 pt-5">
            <div class="col-12 text-center mb-4">
                <h2>How GuardPal Works</h2>
                <p class="lead">The easiest way to find security jobs and connect with professionals</p>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-user-plus fa-2x text-primary"></i>
                        </div>
                        <h4>Create Profile</h4>
                        <p>Sign up and create your professional profile with your skills, certifications, and SIA license details.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-search fa-2x text-primary"></i>
                        </div>
                        <h4>Find Opportunities</h4>
                        <p>Search for jobs that match your skills and experience in the security industry.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-handshake fa-2x text-primary"></i>
                        </div>
                        <h4>Connect & Apply</h4>
                        <p>Connect with other security professionals and apply directly to jobs that interest you.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-white text-dark py-4 mt-5 border-top">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>About GuardPal</h5>
                    <p>Your trusted platform for finding security jobs.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5>Contact Us</h5>
                    <p>support@guardpal.com</p>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> GuardPal. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <script src="assets/js/animations.js"></script>
</body>
</html>