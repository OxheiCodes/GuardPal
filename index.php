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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">GuardPal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="pages/login.php">
                            <i class="fas fa-sign-in-alt me-1"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/register.php">
                            <i class="fas fa-user-plus me-1"></i> Register
                        </a>
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
        
        <div class="row mt-5">
            <div class="col-12 text-center mb-4">
                <h2>Why Choose GuardPal</h2>
                <p class="lead">The best platform for security professionals</p>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                        <h4>Industry Focus</h4>
                        <p>Dedicated to security professionals with specialized features</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-id-badge fa-3x text-primary mb-3"></i>
                        <h4>SIA License</h4>
                        <p>Showcase your SIA credentials to stand out to employers</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-network-wired fa-3x text-primary mb-3"></i>
                        <h4>Network</h4>
                        <p>Connect with peers and security industry professionals</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-lock fa-3x text-primary mb-3"></i>
                        <h4>Privacy</h4>
                        <p>Control who sees your sensitive security credentials</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5>About GuardPal</h5>
                    <p class="mb-3">Your trusted platform for finding security jobs and connecting with professionals in the industry.</p>
                    <div class="social-icons">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-md-2 mb-4 mb-md-0">
                    <h5>Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="pages/login.php">Login</a></li>
                        <li><a href="pages/register.php">Register</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-2 mb-4 mb-md-0">
                    <h5>Resources</h5>
                    <ul class="footer-links">
                        <li><a href="#">Career Advice</a></li>
                        <li><a href="#">SIA Licensing</a></li>
                        <li><a href="#">Training Resources</a></li>
                        <li><a href="#">Security News</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <p><i class="fas fa-envelope me-2"></i> support@guardpal.com</p>
                    <p><i class="fas fa-phone me-2"></i> +44 20 1234 5678</p>
                    <p><i class="fas fa-map-marker-alt me-2"></i> 123 Security Street, London, UK</p>
                </div>
            </div>
            <div class="text-center copyright">
                <p>&copy; <?php echo date('Y'); ?> GuardPal. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <script src="assets/js/animations.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animate hero section elements
        anime.timeline({
            easing: 'easeOutExpo',
        })
        .add({
            targets: '.display-4',
            opacity: [0, 1],
            translateY: [50, 0],
            duration: 1000,
            delay: 200
        })
        .add({
            targets: '.lead',
            opacity: [0, 1],
            translateY: [30, 0],
            duration: 800,
            offset: '-=800'
        })
        .add({
            targets: '.features .d-flex',
            opacity: [0, 1],
            translateX: [-30, 0],
            delay: anime.stagger(100),
            duration: 800,
            offset: '-=600'
        })
        .add({
            targets: '.btn-lg',
            opacity: [0, 1],
            translateY: [20, 0],
            delay: anime.stagger(100),
            duration: 600,
            offset: '-=700'
        });
        
        // Animate cards
        anime({
            targets: '.card',
            scale: [0.9, 1],
            opacity: [0, 1],
            delay: anime.stagger(100, {start: 300}),
            duration: 800,
            easing: 'easeOutSine'
        });
    });
    </script>
</body>
</html>