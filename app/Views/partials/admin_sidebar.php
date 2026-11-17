<?php
use App\Config\App;
use App\Core\Session;

$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$userName = Session::get('user_name', 'Administrator');
?>
<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
    <div class="card-body bg-dark text-white p-4 text-center">
        <div class="avatar-circle bg-warning text-dark rounded-circle mx-auto d-flex align-items-center justify-content-center shadow-sm mb-3" style="width: 60px; height: 60px; font-size: 1.5rem; font-weight: 700;">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <h6 class="fw-bold mb-1 text-white"><?= htmlspecialchars($userName) ?></h6>
        <div class="badge bg-warning text-dark small mb-2">Petugas Puskesmas</div>
        <p class="small text-white-50 mb-0">Wilayah Astanajapura</p>
    </div>

    <div class="list-group list-group-flush p-2">
        <a href="<?= App::baseUrl('/admin') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-3 <?= str_ends_with($currentUri, '/admin') ? 'active fw-bold' : '' ?>">
            <i class="bi bi-speedometer2 fs-5"></i>
            <span>Dashboard</span>
        </a>
        <a href="<?= App::baseUrl('/admin/pendaftaran') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-3 <?= str_contains($currentUri, '/admin/pendaftaran') ? 'active fw-bold' : '' ?>">
            <i class="bi bi-people-fill fs-5 text-primary"></i>
            <span>Kelola Antrean Pasien</span>
        </a>
        <a href="<?= App::baseUrl('/admin/layanan') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-3 <?= str_contains($currentUri, '/admin/layanan') ? 'active fw-bold' : '' ?>">
            <i class="bi bi-hospital fs-5 text-success"></i>
            <span>Poli & Layanan</span>
        </a>
        <a href="<?= App::baseUrl('/admin/jadwal') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-3 <?= str_contains($currentUri, '/admin/jadwal') ? 'active fw-bold' : '' ?>">
            <i class="bi bi-calendar-week fs-5 text-info"></i>
            <span>Jadwal Dokter & Kuota</span>
        </a>
        <a href="<?= App::baseUrl('/admin/desa') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-3 <?= str_contains($currentUri, '/admin/desa') ? 'active fw-bold' : '' ?>">
            <i class="bi bi-geo-alt-fill fs-5 text-danger"></i>
            <span>Data Desa Astanajapura</span>
        </a>
        <a href="<?= App::baseUrl('/admin/artikel') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-3 <?= str_contains($currentUri, '/admin/artikel') ? 'active fw-bold' : '' ?>">
            <i class="bi bi-journal-text fs-5 text-secondary"></i>
            <span>Artikel Edukasi</span>
        </a>
        <a href="<?= App::baseUrl('/admin/kuis') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-3 <?= str_contains($currentUri, '/admin/kuis') ? 'active fw-bold' : '' ?>">
            <i class="bi bi-question-circle-fill fs-5 text-warning"></i>
            <span>Bank Soal Kuis</span>
        </a>
        <a href="<?= App::baseUrl('/admin/pengumuman') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-3 <?= str_contains($currentUri, '/admin/pengumuman') ? 'active fw-bold' : '' ?>">
            <i class="bi bi-megaphone-fill fs-5 text-danger"></i>
            <span>Kirim Pengumuman</span>
        </a>
        <a href="<?= App::baseUrl('/notifikasi') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-3 <?= str_contains($currentUri, '/notifikasi') || str_contains($currentUri, '/notifications') ? 'active fw-bold' : '' ?>">
            <i class="bi bi-bell-fill fs-5 text-warning"></i>
            <span>Pusat Notifikasi</span>
        </a>
        <div class="px-3 pt-3 pb-1 text-muted text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.05em;">
            Pengaturan Sistem
        </div>
        <a href="<?= App::baseUrl('/profil') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-3 <?= str_contains($currentUri, '/profil') ? 'active fw-bold' : '' ?>">
            <i class="bi bi-shield-lock-fill fs-5 text-warning"></i>
            <span>Pengaturan Akun & Sandi</span>
        </a>
        <a href="<?= App::baseUrl('/admin/pengaturan/whatsapp') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-3 <?= (str_contains($currentUri, '/admin/whatsapp') || str_contains($currentUri, '/admin/pengaturan/whatsapp')) ? 'active fw-bold' : '' ?>">
            <i class="bi bi-whatsapp fs-5 text-success"></i>
            <span>WhatsApp Gateway</span>
        </a>
        <a href="<?= App::baseUrl('/admin/ai-logs') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-3 <?= str_contains($currentUri, '/admin/ai-logs') ? 'active fw-bold' : '' ?>">
            <i class="bi bi-robot fs-5 text-purple" style="color: #6f42c1;"></i>
            <span>Log AI (Persiapan)</span>
        </a>
        <hr class="my-2 text-muted">
        <a href="<?= App::baseUrl('/') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-2 text-muted">
            <i class="bi bi-house fs-5"></i>
            <span>Lihat Website</span>
        </a>
        <a href="<?= App::baseUrl('/logout') ?>" class="list-group-item list-group-item-action border-0 rounded-3 d-flex align-items-center gap-3 py-2 text-danger">
            <i class="bi bi-box-arrow-right fs-5"></i>
            <span>Keluar</span>
        </a>
    </div>
</div>
