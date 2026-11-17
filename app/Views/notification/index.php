<?php
use App\Config\App;
use App\Core\Session;

$notifications = $notifications ?? [];
$filter = $filter ?? 'all';
$counts = $counts ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$totalItems = $totalItems ?? 0;

$typeLabels = [
    'registration' => 'Pendaftaran',
    'schedule'     => 'Jadwal & Pengingat',
    'account'      => 'Akun & Keamanan',
    'education'    => 'Edukasi Kesehatan',
    'announcement' => 'Pengumuman Resmi',
];

$typeBadges = [
    'registration' => 'primary',
    'schedule'     => 'warning text-dark',
    'account'      => 'info text-dark',
    'education'    => 'success',
    'announcement' => 'danger',
];

$typeIcons = [
    'registration' => 'bi-ticket-perforated-fill',
    'schedule'     => 'bi-calendar-event-fill',
    'account'      => 'bi-shield-check',
    'education'    => 'bi-journal-medical',
    'announcement' => 'bi-megaphone-fill',
];
?>

<div class="container py-4">
    <!-- Header Section -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 small fw-semibold">
                        <i class="bi bi-bell-fill me-1"></i> Pusat Pemberitahuan
                    </span>
                    <span class="text-muted small">&bull; SmartHealthy Puskesmas Astanajapura</span>
                </div>
                <h3 class="fw-bold text-dark mb-1">
                    <i class="bi bi-inbox-fill text-primary me-2"></i>Notifikasi Anda
                </h3>
                <p class="text-muted small mb-0">
                    Informasi penting seputar pendaftaran antrean, jadwal dokter, status verifikasi akun, dan edukasi kesehatan keluarga.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <?php if (($counts['unread'] ?? 0) > 0): ?>
                    <form action="<?= App::baseUrl('/notifikasi/baca-semua') ?>" method="POST" class="d-inline">
                        <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                        <button type="submit" class="btn btn-outline-primary rounded-pill px-3 py-2 btn-sm fw-bold">
                            <i class="bi bi-check2-all me-1"></i> Tandai Semua Dibaca
                        </button>
                    </form>
                <?php endif; ?>

                <?php if (($counts['all'] ?? 0) > 0): ?>
                    <form action="<?= App::baseUrl('/notifikasi/hapus-semua-dibaca') ?>" method="POST" onsubmit="return confirm('Bersihkan semua notifikasi yang sudah dibaca?');" class="d-inline">
                        <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                        <button type="submit" class="btn btn-outline-secondary rounded-pill px-3 py-2 btn-sm fw-semibold">
                            <i class="bi bi-trash me-1"></i> Hapus yang Sudah Dibaca
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
            <ul class="nav nav-pills flex-wrap gap-2 mb-0" id="notifFilterPills">
                <!-- Semua -->
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-2 <?= $filter === 'all' ? 'active bg-primary text-white shadow-sm fw-bold' : 'text-muted bg-light hover-bg' ?>" 
                       href="<?= App::baseUrl('/notifikasi?filter=all') ?>">
                        <i class="bi bi-grid-fill me-1"></i> Semua 
                        <span class="badge <?= $filter === 'all' ? 'bg-white text-primary' : 'bg-secondary' ?> rounded-pill ms-1"><?= $counts['all'] ?? 0 ?></span>
                    </a>
                </li>

                <!-- Belum Dibaca -->
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-2 <?= $filter === 'unread' ? 'active bg-danger text-white shadow-sm fw-bold' : 'text-muted bg-light hover-bg' ?>" 
                       href="<?= App::baseUrl('/notifikasi?filter=unread') ?>">
                        <i class="bi bi-envelope-fill me-1"></i> Belum Dibaca 
                        <span class="badge <?= $filter === 'unread' ? 'bg-white text-danger' : 'bg-danger' ?> rounded-pill ms-1"><?= $counts['unread'] ?? 0 ?></span>
                    </a>
                </li>

                <!-- Pendaftaran -->
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-2 <?= $filter === 'registration' ? 'active bg-primary text-white shadow-sm fw-bold' : 'text-muted bg-light hover-bg' ?>" 
                       href="<?= App::baseUrl('/notifikasi?filter=registration') ?>">
                        <i class="bi bi-ticket-perforated-fill me-1"></i> Pendaftaran 
                        <span class="badge <?= $filter === 'registration' ? 'bg-white text-primary' : 'bg-secondary' ?> rounded-pill ms-1"><?= $counts['registration'] ?? 0 ?></span>
                    </a>
                </li>

                <!-- Jadwal -->
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-2 <?= $filter === 'schedule' ? 'active bg-warning text-dark shadow-sm fw-bold' : 'text-muted bg-light hover-bg' ?>" 
                       href="<?= App::baseUrl('/notifikasi?filter=schedule') ?>">
                        <i class="bi bi-calendar-event-fill me-1 text-warning"></i> Jadwal & Pengingat 
                        <span class="badge <?= $filter === 'schedule' ? 'bg-dark text-white' : 'bg-secondary' ?> rounded-pill ms-1"><?= $counts['schedule'] ?? 0 ?></span>
                    </a>
                </li>

                <!-- Akun -->
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-2 <?= $filter === 'account' ? 'active bg-info text-dark shadow-sm fw-bold' : 'text-muted bg-light hover-bg' ?>" 
                       href="<?= App::baseUrl('/notifikasi?filter=account') ?>">
                        <i class="bi bi-shield-check me-1 text-primary"></i> Akun & Keamanan 
                        <span class="badge <?= $filter === 'account' ? 'bg-dark text-white' : 'bg-secondary' ?> rounded-pill ms-1"><?= $counts['account'] ?? 0 ?></span>
                    </a>
                </li>

                <!-- Edukasi -->
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-2 <?= $filter === 'education' ? 'active bg-success text-white shadow-sm fw-bold' : 'text-muted bg-light hover-bg' ?>" 
                       href="<?= App::baseUrl('/notifikasi?filter=education') ?>">
                        <i class="bi bi-journal-medical me-1 text-success"></i> Edukasi 
                        <span class="badge <?= $filter === 'education' ? 'bg-white text-success' : 'bg-secondary' ?> rounded-pill ms-1"><?= $counts['education'] ?? 0 ?></span>
                    </a>
                </li>

                <!-- Pengumuman -->
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-2 <?= $filter === 'announcement' ? 'active bg-danger text-white shadow-sm fw-bold' : 'text-muted bg-light hover-bg' ?>" 
                       href="<?= App::baseUrl('/notifikasi?filter=announcement') ?>">
                        <i class="bi bi-megaphone-fill me-1 text-danger"></i> Pengumuman 
                        <span class="badge <?= $filter === 'announcement' ? 'bg-white text-danger' : 'bg-secondary' ?> rounded-pill ms-1"><?= $counts['announcement'] ?? 0 ?></span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Notification List -->
    <?php if (empty($notifications)): ?>
        <div class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center my-4">
            <div class="avatar-lg bg-light text-muted rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                <i class="bi bi-bell-slash"></i>
            </div>
            <h4 class="fw-bold text-dark mb-2">Tidak Ada Notifikasi</h4>
            <p class="text-muted small mx-auto mb-4" style="max-width: 480px;">
                <?php if ($filter === 'unread'): ?>
                    Hebat! Semua pemberitahuan sudah Anda baca. Tidak ada pesan yang tertunda.
                <?php else: ?>
                    Belum ada riwayat pemberitahuan untuk kategori ini. Notifikasi otomatis akan muncul saat ada perubahan status pendaftaran atau jadwal layanan.
                <?php endif; ?>
            </p>
            <div class="d-flex justify-content-center gap-2">
                <a href="<?= App::baseUrl('/dashboard') ?>" class="btn btn-outline-primary rounded-pill px-4 btn-sm fw-semibold">
                    <i class="bi bi-grid-fill me-1"></i> Ke Dashboard
                </a>
                <a href="<?= App::baseUrl('/pendaftaran/riwayat') ?>" class="btn btn-primary rounded-pill px-4 btn-sm fw-bold">
                    <i class="bi bi-ticket-perforated me-1"></i> Pendaftaran Saya
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-3 mb-4">
            <?php foreach ($notifications as $notif): 
                $isRead = (bool) $notif['is_read'];
                $type = $notif['type'];
                $badgeColor = $typeBadges[$type] ?? 'secondary';
                $typeLabel = $typeLabels[$type] ?? ucfirst($type);
                $typeIcon = $typeIcons[$type] ?? 'bi-bell-fill';
                $createdTime = strtotime($notif['created_at']);
            ?>
                <div class="card border-0 shadow-sm rounded-4 transition-all overflow-hidden <?= !$isRead ? 'bg-notif-unread border-start border-4 border-primary' : 'bg-white' ?>">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start gap-3">
                            <!-- Type Icon Bubble -->
                            <div class="notif-bubble bg-<?= $badgeColor ?> bg-opacity-10 text-<?= $type === 'schedule' ? 'warning-emphasis' : ($type === 'announcement' ? 'danger' : ($type === 'education' ? 'success' : 'primary')) ?> rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 48px; height: 48px; font-size: 1.35rem;">
                                <i class="bi <?= $typeIcon ?>"></i>
                            </div>

                            <!-- Content Column -->
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-<?= $badgeColor ?> rounded-pill small px-2 py-1 fw-semibold" style="font-size: 0.7rem;">
                                            <?= $typeLabel ?>
                                        </span>
                                        <?php if (!$isRead): ?>
                                            <span class="badge bg-danger rounded-pill px-2 py-0 small fw-bold" style="font-size: 0.65rem;">BARU</span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.75rem;">
                                        <i class="bi bi-clock me-1"></i> <?= date('d M Y, H:i', $createdTime) ?> WIB
                                    </small>
                                </div>

                                <h6 class="fw-bold text-dark mb-1 <?= !$isRead ? 'text-primary' : '' ?>">
                                    <?= htmlspecialchars((string)$notif['title']) ?>
                                </h6>
                                <p class="text-muted small mb-3 lh-sm">
                                    <?= nl2br(htmlspecialchars((string)$notif['message'])) ?>
                                </p>

                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 border-top border-light">
                                    <div>
                                        <?php if (!empty($notif['target_url'])): ?>
                                            <form action="<?= App::baseUrl('/notifikasi/baca/' . $notif['id']) ?>" method="POST" class="d-inline">
                                                <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                                                <input type="hidden" name="redirect_url" value="<?= htmlspecialchars((string)$notif['target_url']) ?>">
                                                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold">
                                                    Lihat Rincian <i class="bi bi-arrow-right ms-1"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>

                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!$isRead): ?>
                                            <form action="<?= App::baseUrl('/notifikasi/baca/' . $notif['id']) ?>" method="POST" class="d-inline">
                                                <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" style="font-size: 0.75rem;" title="Tandai Sudah Dibaca">
                                                    <i class="bi bi-check2 me-1"></i> Tandai Dibaca
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <form action="<?= App::baseUrl('/notifikasi/hapus/' . $notif['id']) ?>" method="POST" onsubmit="return confirm('Hapus notifikasi ini?');" class="d-inline">
                                            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                                            <button type="submit" class="btn btn-sm btn-link text-danger p-1" title="Hapus Notifikasi">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination Controls -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link rounded-pill px-3 me-2" href="<?= App::baseUrl('/notifikasi?filter=' . $filter . '&page=' . ($page - 1)) ?>">
                            &laquo; Sebelumnya
                        </a>
                    </li>
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                            <a class="page-link rounded-circle mx-1 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;" href="<?= App::baseUrl('/notifikasi?filter=' . $filter . '&page=' . $p) ?>">
                                <?= $p ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link rounded-pill px-3 ms-2" href="<?= App::baseUrl('/notifikasi?filter=' . $filter . '&page=' . ($page + 1)) ?>">
                            Berikutnya &raquo;
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.bg-notif-unread {
    background-color: #f6f9ff !important;
}
.hover-bg:hover {
    background-color: #e9ecef !important;
}
.transition-all {
    transition: all 0.2s ease-in-out;
}
.shadow-xs {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
}
</style>
