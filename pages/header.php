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
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo SITE_URL; ?>">GuardPal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="search.php">Find Jobs</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="networkDropdown" role="button" data-bs-toggle="dropdown">
                                Network
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="networkDropdown">
                                <li><a class="dropdown-item" href="network-search.php">Discover Professionals</a></li>
                                <li><a class="dropdown-item" href="find-professionals.php">Search Professionals</a></li>
                                <li><a class="dropdown-item" href="connections.php">My Connections</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="chat.php">Messages</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown">
                                Profile
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
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
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="flex-grow-1">