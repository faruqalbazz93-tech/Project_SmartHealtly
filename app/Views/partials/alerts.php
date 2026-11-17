<?php
use App\Core\Session;

$flashes = Session::getFlash();
if (!empty($flashes)):
?>
<div class="container my-3">
    <?php foreach ($flashes as $type => $messages): ?>
        <?php 
        $bsType = match($type) {
            'error'   => 'danger',
            'success' => 'success',
            'warning' => 'warning',
            'info'    => 'info',
            default   => $type
        };
        $icon = match($bsType) {
            'success' => 'bi-check-circle-fill',
            'danger'  => 'bi-exclamation-octagon-fill',
            'warning' => 'bi-exclamation-triangle-fill',
            'info'    => 'bi-info-circle-fill',
            default   => 'bi-bell-fill'
        };
        ?>
        <?php foreach ($messages as $msg): ?>
            <div class="alert alert-<?= $bsType ?> alert-dismissible fade show shadow-sm d-flex align-items-center mb-2" role="alert" style="border-radius: 0.75rem;">
                <i class="bi <?= $icon ?> me-2 fs-5"></i>
                <div class="flex-grow-1"><?= $msg ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>
