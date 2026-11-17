<?php
require_once dirname(__DIR__) . '/partials/header.php';
require_once dirname(__DIR__) . '/partials/navbar.php';
?>

<div class="bg-light py-4 flex-grow-1">
    <div class="container">
        <?php require_once dirname(__DIR__) . '/partials/alerts.php'; ?>
        
        <div class="row g-4">
            <!-- Sidebar -->
            <div class="col-lg-3 col-md-4">
                <?php require_once dirname(__DIR__) . '/partials/user_sidebar.php'; ?>
            </div>
            
            <!-- Main Content Area -->
            <div class="col-lg-9 col-md-8">
                <?= $content ?? '' ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once dirname(__DIR__) . '/partials/footer.php';
?>
