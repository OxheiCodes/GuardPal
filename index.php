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
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
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

    <div class="container mt-5">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4 mb-4">Find Security Jobs That Match Your Skills</h1>
                <p class="lead mb-4">GuardPal connects security professionals with top employers. Start your job search today!</p>
                <a href="pages/register.php" class="btn btn-primary btn-lg">Get Started</a>
                <a href="pages/login.php" class="btn btn-outline-primary btn-lg ms-2">Login</a>
            </div>
            <div class="col-md-6">
                <img src="https://placehold.co/600x400" alt="Security Professional" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <script src="assets/js/animations.js"></script>
</body>
</html>