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
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container">
            <a class="navbar-brand mx-auto" href="#">
                <!-- Inline SVG for the logo -->
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 120 120" class="logo-shield">
                    <path d="M60,20 L60,20 L60,20 C60,20 40,15 20,20 C0,25 -5,60 20,85 C45,110 60,100 60,100 C60,100 75,110 100,85 C125,60 120,25 100,20 C80,15 60,20 60,20 Z" fill="#0d6efd" stroke="#0d6efd" stroke-width="2"/>
                    <path d="M60,35 L60,35 L60,35 C60,35 45,32 30,35 C15,38 12,60 30,75 C48,90 60,85 60,85 C60,85 72,90 90,75 C108,60 105,38 90,35 C75,32 60,35 60,35 Z" fill="white" stroke="white" stroke-width="1"/>
                    <path d="M60,50 L60,50 L60,50 C60,50 50,48 40,50 C30,52 28,65 40,73 C52,81 60,78 60,78 C60,78 68,81 80,73 C92,65 90,52 80,50 C70,48 60,50 60,50 Z" fill="#0d6efd" stroke="#0d6efd" stroke-width="1"/>
                    <path d="M55,65 L65,65 M60,60 L60,70" stroke="white" stroke-width="3" stroke-linecap="round"/>
                </svg>
                Guard<span>Pal</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item mx-2">
                        <a class="nav-link" href="pages/login.php">Login</a>
                    </li>
                    <li class="nav-item mx-2">
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
            <div class="col-md-6 text-center">
                <!-- Inline SVG for the full logo -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 120" width="400" height="120" class="img-fluid mb-4">
                    <!-- Shield Icon -->
                    <g transform="translate(20, 15) scale(0.9)">
                        <path d="M55,20 L55,20 L55,20 C55,20 40,15 25,20 C10,25 5,60 25,85 C45,110 55,100 55,100 C55,100 65,110 85,85 C105,60 100,25 85,20 C70,15 55,20 55,20 Z" fill="#0d6efd" stroke="#0d6efd" stroke-width="2"/>
                        <path d="M55,35 L55,35 L55,35 C55,35 45,32 35,35 C25,38 22,60 35,75 C48,90 55,85 55,85 C55,85 62,90 75,75 C88,60 85,38 75,35 C65,32 55,35 55,35 Z" fill="white" stroke="white" stroke-width="1"/>
                        <path d="M55,50 L55,50 L55,50 C55,50 50,48 45,50 C40,52 38,65 45,73 C52,81 55,78 55,78 C55,78 58,81 65,73 C72,65 70,52 65,50 C60,48 55,50 55,50 Z" fill="#0d6efd" stroke="#0d6efd" stroke-width="1"/>
                        <path d="M50,65 L60,65 M55,60 L55,70" stroke="white" stroke-width="3" stroke-linecap="round"/>
                    </g>
                    
                    <!-- Text -->
                    <text x="110" y="70" fill="#333333" font-size="45" font-family="'Roboto', sans-serif" font-weight="700" letter-spacing="0.5px">
                        Guard<tspan fill="#0d6efd">Pal</tspan>
                    </text>
                    <text x="110" y="90" fill="#666666" font-size="15" font-family="'Roboto', sans-serif">
                        Security Jobs Platform
                    </text>
                </svg>
                
                <img src="https://bluelineoperations.co.uk/wp-content/uploads/2024/03/industrial-warehouse-security-cambridge.jpg" alt="Security Professional" class="img-fluid rounded shadow">
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
                <div class="col-md-4">
                    <a href="#" class="d-inline-flex align-items-center mb-3 text-decoration-none">
                        <!-- Inline SVG for footer logo -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 120 120">
                            <path d="M60,20 L60,20 L60,20 C60,20 40,15 20,20 C0,25 -5,60 20,85 C45,110 60,100 60,100 C60,100 75,110 100,85 C125,60 120,25 100,20 C80,15 60,20 60,20 Z" fill="#0d6efd" stroke="#0d6efd" stroke-width="2"/>
                            <path d="M60,35 L60,35 L60,35 C60,35 45,32 30,35 C15,38 12,60 30,75 C48,90 60,85 60,85 C60,85 72,90 90,75 C108,60 105,38 90,35 C75,32 60,35 60,35 Z" fill="white" stroke="white" stroke-width="1"/>
                            <path d="M60,50 L60,50 L60,50 C60,50 50,48 40,50 C30,52 28,65 40,73 C52,81 60,78 60,78 C60,78 68,81 80,73 C92,65 90,52 80,50 C70,48 60,50 60,50 Z" fill="#0d6efd" stroke="#0d6efd" stroke-width="1"/>
                            <path d="M55,65 L65,65 M60,60 L60,70" stroke="white" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                        <span class="h5 mb-0 ms-2">Guard<span style="color: #0d6efd;">Pal</span></span>
                    </a>
                    <p>Your trusted platform for finding security jobs.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="pages/login.php" class="text-decoration-none">Login</a></li>
                        <li><a href="pages/register.php" class="text-decoration-none">Register</a></li>
                    </ul>
                </div>
                <div class="col-md-4 text-md-end">
                    <h5>Contact Us</h5>
                    <p><i class="fas fa-envelope me-2"></i>support@guardpal.com</p>
                    <p><i class="fas fa-phone me-2"></i>+44 20 1234 5678</p>
                    <div class="social-icons">
                        <a href="#" class="text-dark me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-dark me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-dark me-2"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-dark"><i class="fab fa-instagram"></i></a>
                    </div>
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