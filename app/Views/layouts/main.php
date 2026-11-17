<?php
require_once dirname(__DIR__) . '/partials/header.php';
require_once dirname(__DIR__) . '/partials/navbar.php';
require_once dirname(__DIR__) . '/partials/alerts.php';
?>

<main class="flex-grow-1">
    <?= $content ?? '' ?>
</main>

<?php
require_once dirname(__DIR__) . '/partials/footer.php';
?>
