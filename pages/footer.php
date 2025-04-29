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
    <script src="<?php echo SITE_URL; ?>assets/js/animations.js"></script>
    <?php if (basename($_SERVER['PHP_SELF']) === 'profile.php'): ?>
    <script src="<?php echo SITE_URL; ?>assets/js/profile.js"></script>
    <?php elseif (basename($_SERVER['PHP_SELF']) === 'chat.php'): ?>
    <script src="<?php echo SITE_URL; ?>assets/js/chat.js"></script>
    <?php elseif (basename($_SERVER['PHP_SELF']) === 'search.php' || basename($_SERVER['PHP_SELF']) === 'bookmarks.php'): ?>
    <script src="<?php echo SITE_URL; ?>assets/js/job-search.js"></script>
    <?php elseif (basename($_SERVER['PHP_SELF']) === 'connections.php' || basename($_SERVER['PHP_SELF']) === 'find-professionals.php' || basename($_SERVER['PHP_SELF']) === 'network-search.php'): ?>
    <script src="<?php echo SITE_URL; ?>assets/js/connections.js"></script>
    <?php endif; ?>
</body>
</html>