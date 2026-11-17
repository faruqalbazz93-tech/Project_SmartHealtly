<?php
require_once dirname(__DIR__) . '/partials/header.php';
require_once dirname(__DIR__) . '/partials/navbar.php';
?>

<div class="bg-light py-4 flex-grow-1">
    <div class="container-fluid px-lg-4">
        <?php require_once dirname(__DIR__) . '/partials/alerts.php'; ?>
        
        <div class="row g-4">
            <!-- Admin Sidebar -->
            <div class="col-lg-3 col-xl-2 col-md-4">
                <?php require_once dirname(__DIR__) . '/partials/admin_sidebar.php'; ?>
            </div>
            
            <!-- Admin Content -->
            <div class="col-lg-9 col-xl-10 col-md-8">
                <?= $content ?? '' ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once dirname(__DIR__) . '/partials/footer.php';
?>
