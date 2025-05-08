<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'GuardPal'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" href="../assets/img/guardpal-icon.svg">
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container">
            <!-- Use inline SVG for the logo instead of an external file -->
            <a class="navbar-brand mx-auto" href="<?php echo SITE_URL; ?>">
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
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item mx-2">
                            <a class="nav-link" href="dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item mx-2">
                            <a class="nav-link" href="search.php">Find Jobs</a>
                        </li>
                        <li class="nav-item dropdown mx-2">
                            <a class="nav-link dropdown-toggle" href="#" id="networkDropdown" role="button" data-bs-toggle="dropdown">
                                Network
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="networkDropdown">
                                <li><a class="dropdown-item" href="network-search.php">Discover Professionals</a></li>
                                <li><a class="dropdown-item" href="find-professionals.php">Search Professionals</a></li>
                                <li><a class="dropdown-item" href="browse_agencies.php">Browse Agencies</a></li>
                                <li><a class="dropdown-item" href="connections.php">My Connections</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="chat.php">Messages</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown mx-2">
                            <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown">
                                Profile
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="profileDropdown">
                                <li><a class="dropdown-item" href="profile.php">View Profile</a></li>
                                <li><a class="dropdown-item" href="edit-profile.php">Edit Profile</a></li>
                                <li><a class="dropdown-item" href="edit-skills.php">Update Skills</a></li>
                                <li><a class="dropdown-item" href="edit-certifications.php">Update Certifications</a></li>
                                <li><a class="dropdown-item" href="edit-experience.php">Update Experience</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="bookmarks.php">Saved Jobs</a></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item mx-2">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item mx-2">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="flex-grow-1">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 class="mt-4 mb-3">Welcome to GuardPal</h1>
                    <p>Your trusted platform for finding security jobs and professionals.</p>
                </div>
            </div>
        </div>
    </div>