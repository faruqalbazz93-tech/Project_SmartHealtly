<?php
use App\Config\App;
use App\Core\Auth;
use App\Core\Session;

$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$userName = Session::get('user_name', 'Pasien');
$userPhone = Session::get('user_phone', '-');
$isFullyVerified = Auth::isFullyVerified();
?>
<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
    <div class="card-body bg-primary text-white p-4 text-center">
        <div class="avatar-circle bg-white text-primary rounded-circle mx-auto d-flex align-items-center justify-content-center shadow-sm mb-3" style="width: 70px; height: 70px; font-size: 1.75rem; font-weight: 700;">
            <?= strtoupper(substr($userName, 0, 1)) ?>
        </div>
        <h6 class="fw-bold mb-1 text-white"><?= htmlspecialchars($userName) ?></h6>
        <div class="d-flex justify-content-center gap-1 mb-2">
            <?php if ($isFullyVerified): ?>
                <span class="badge bg-success text-white small"><i class="bi bi-shield-check me-1"></i> Terverifikasi</span>
            <?php else: ?>
                <a href="<?= App::baseUrl('/verifikasi') ?>" class="badge bg-warning text-dark small text-decoration-none" title="Klik untuk verifikasi">
                    <i class="bi bi-shield-exclamation me-1"></i> Belum Verifikasi
                </a>
            <?php endif; ?>
        </div>
        <p class="small text-white-50 mb-0">Pasien Puskesmas Astanajapura</p>
    </div>

    <div class="list-group list-group-flush p-2">
        <a href="<?= App::baseUrl('/dashboard') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-3 <?= str_ends_with($currentUri, '/dashboard') ? 'active fw-bold' : '' ?>">
            <i class="bi bi-grid-fill fs-5"></i>
            <span>Dashboard</span>
        </a>
        <a href="<?= App::baseUrl('/verifikasi') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-3 <?= str_contains($currentUri, '/verifikasi') ? 'active fw-bold' : '' ?>">
            <i class="bi bi-shield-check fs-5 text-warning"></i>
            <span>Status Verifikasi</span>
            <?php if (!$isFullyVerified): ?>
                <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.65rem;">Belum</span>
            <?php endif; ?>
        </a>
        <a href="<?= App::baseUrl('/layanan') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-3 <?= str_contains($currentUri, '/layanan') ? 'active fw-bold' : '' ?>">
            <i class="bi bi-hospital fs-5 text-success"></i>
            <span>Layanan Poli & Dokter</span>
        </a>
        <a href="<?= App::baseUrl('/pendaftaran/riwayat') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-3 <?= str_contains($currentUri, '/pendaftaran/riwayat') ? 'active fw-bold' : '' ?>">
            <i class="bi bi-ticket-perforated-fill fs-5 text-info"></i>
            <span>Pendaftaran Saya</span>
        </a>
        <a href="<?= App::baseUrl('/artikel') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-3 <?= str_contains($currentUri, '/artikel') ? 'active fw-bold' : '' ?>">
            <i class="bi bi-journal-medical fs-5 text-primary"></i>
            <span>Materi Promkes</span>
        </a>
        <a href="<?= App::baseUrl('/kuis') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-3 <?= str_contains($currentUri, '/kuis') ? 'active fw-bold' : '' ?>">
            <i class="bi bi-award-fill fs-5 text-warning"></i>
            <span>Kuis Kesehatan</span>
        </a>
        <a href="<?= App::baseUrl('/notifikasi') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-3 <?= str_contains($currentUri, '/notifikasi') || str_contains($currentUri, '/notifications') ? 'active fw-bold' : '' ?>">
            <i class="bi bi-bell-fill fs-5 text-warning"></i>
            <span>Notifikasi Saya</span>
        </a>
        <a href="<?= App::baseUrl('/pengaturan/notifikasi') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-3 <?= str_contains($currentUri, '/pengaturan/notifikasi') ? 'active fw-bold' : '' ?>">
            <i class="bi bi-whatsapp fs-5 text-success"></i>
            <span>Pengaturan WhatsApp</span>
        </a>
        <a href="<?= App::baseUrl('/profil') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-3 <?= str_contains($currentUri, '/profil') ? 'active fw-bold' : '' ?>">
            <i class="bi bi-shield-lock-fill fs-5 text-primary"></i>
            <span>Profil & Kata Sandi</span>
        </a>
        <hr class="my-2 text-muted">
        <a href="<?= App::baseUrl('/') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-2 text-muted">
            <i class="bi bi-house fs-5"></i>
            <span>Halaman Depan</span>
        </a>
        <a href="<?= App::baseUrl('/logout') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-2 text-danger">
            <i class="bi bi-box-arrow-right fs-5"></i>
            <span>Keluar</span>
        </a>
    </div>
</div>
