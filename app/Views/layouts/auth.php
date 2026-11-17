<?php
use App\Config\App;
require_once dirname(__DIR__) . '/partials/header.php';
?>

<div class="auth-wrapper min-vh-100 d-flex flex-column justify-content-between py-4 bg-auth-gradient">
    <div class="container text-center mb-3">
        <a href="<?= App::baseUrl('/') ?>" class="d-inline-flex align-items-center gap-2 text-decoration-none">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow" style="width: 44px; height: 44px;">
                <i class="bi bi-heart-pulse-fill fs-4"></i>
            </div>
            <span class="fs-3 fw-bold text-dark">Smart<span class="text-primary">Healthy</span></span>
        </a>
    </div>

    <div class="container">
        <?php require_once dirname(__DIR__) . '/partials/alerts.php'; ?>
        <?= $content ?? '' ?>
    </div>

    <div class="container text-center mt-4">
        <p class="small text-muted mb-0">
            &copy; <?= date('Y') ?> <?= htmlspecialchars(App::name()) ?> &bull; Pelayanan Puskesmas Astanajapura Gratis
        </p>
    </div>
</div>

<!-- SmartHealthy AI Chatbot Partial -->
<?php require_once dirname(__DIR__) . '/partials/chatbot.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= App::baseUrl('/public/js/app.js') ?>"></script>
</body>
</html>

