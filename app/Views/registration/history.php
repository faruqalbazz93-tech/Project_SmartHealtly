<?php
use App\Config\App;
use App\Core\Session;

$registrations = $registrations ?? [];
$statusFilter = $statusFilter ?? 'all';
$counts = $counts ?? [
    'all'       => 0,
    'Pending'   => 0,
    'Confirmed' => 0,
    'Completed' => 0,
    'Cancelled' => 0,
];

$statusColors = [
    'Pending'   => 'warning',
    'Confirmed' => 'primary',
    'Completed' => 'success',
    'Cancelled' => 'danger'
];

$statusLabels = [
    'Pending'   => 'Menunggu',
    'Confirmed' => 'Dikonfirmasi',
    'Completed' => 'Selesai',
    'Cancelled' => 'Dibatalkan'
];

$statusIcons = [
    'Pending'   => 'bi-hourglass-split',
    'Confirmed' => 'bi-check2-circle',
    'Completed' => 'bi-clipboard2-check-fill',
    'Cancelled' => 'bi-x-circle-fill'
];
?>

<div class="container py-4">
    <!-- Header Section -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 small fw-semibold">
                        <i class="bi bi-person-circle me-1"></i> Portal Pasien
                    </span>
                    <span class="text-muted small">&bull; Puskesmas Astanajapura</span>
                </div>
                <h3 class="fw-bold text-dark mb-1">
                    <i class="bi bi-clock-history text-primary me-2"></i>Riwayat & Status Pendaftaran
                </h3>
                <p class="text-muted small mb-0">
                    Pantau nomor antrean secara real-time, kelola e-Ticket digital, dan cetak bukti riwayat pelayanan kesehatan Anda.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= App::baseUrl('/pendaftaran/baru') ?>" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm fw-bold">
                    <i class="bi bi-plus-circle-fill me-1"></i> Daftar Antrean Baru
                </a>
                <a href="<?= App::baseUrl('/layanan') ?>" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold">
                    <i class="bi bi-hospital me-1"></i> Jadwal Poli
                </a>
                <a href="<?= App::baseUrl('/cek-antrean') ?>" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold">
                    <i class="bi bi-display me-1"></i> Cek Antrean Hari Ini
                </a>
            </div>
        </div>
    </div>

    <!-- Status Filter Tabs & Search Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <!-- Status Tabs -->
                <div class="col-lg-8">
                    <ul class="nav nav-pills flex-wrap gap-2 mb-0" id="statusFilterPills">
                        <!-- Semua Status -->
                        <li class="nav-item">
                            <a class="nav-link rounded-pill px-3 py-2 <?= $statusFilter === 'all' ? 'active bg-primary text-white shadow-sm fw-bold' : 'text-muted bg-light hover-bg' ?>" 
                               href="<?= App::baseUrl('/pendaftaran/riwayat?status=all') ?>">
                                <i class="bi bi-grid-fill me-1"></i> Semua 
                                <span class="badge <?= $statusFilter === 'all' ? 'bg-white text-primary' : 'bg-secondary' ?> rounded-pill ms-1"><?= $counts['all'] ?? 0 ?></span>
                            </a>
                        </li>

                        <!-- Menunggu -->
                        <li class="nav-item">
                            <a class="nav-link rounded-pill px-3 py-2 <?= $statusFilter === 'Pending' ? 'active bg-warning text-dark shadow-sm fw-bold border border-warning' : 'text-muted bg-light hover-bg' ?>" 
                               href="<?= App::baseUrl('/pendaftaran/riwayat?status=Pending') ?>">
                                <i class="bi bi-hourglass-split me-1 text-warning"></i> Menunggu 
                                <span class="badge <?= $statusFilter === 'Pending' ? 'bg-dark text-white' : 'bg-warning-subtle text-warning-emphasis' ?> rounded-pill ms-1"><?= $counts['Pending'] ?? 0 ?></span>
                            </a>
                        </li>

                        <!-- Dikonfirmasi -->
                        <li class="nav-item">
                            <a class="nav-link rounded-pill px-3 py-2 <?= $statusFilter === 'Confirmed' ? 'active bg-info text-dark shadow-sm fw-bold' : 'text-muted bg-light hover-bg' ?>" 
                               href="<?= App::baseUrl('/pendaftaran/riwayat?status=Confirmed') ?>">
                                <i class="bi bi-check2-circle me-1 text-primary"></i> Dikonfirmasi 
                                <span class="badge <?= $statusFilter === 'Confirmed' ? 'bg-dark text-white' : 'bg-primary-subtle text-primary' ?> rounded-pill ms-1"><?= $counts['Confirmed'] ?? 0 ?></span>
                            </a>
                        </li>

                        <!-- Selesai -->
                        <li class="nav-item">
                            <a class="nav-link rounded-pill px-3 py-2 <?= $statusFilter === 'Completed' ? 'active bg-success text-white shadow-sm fw-bold' : 'text-muted bg-light hover-bg' ?>" 
                               href="<?= App::baseUrl('/pendaftaran/riwayat?status=Completed') ?>">
                                <i class="bi bi-clipboard2-check-fill me-1 text-success"></i> Selesai 
                                <span class="badge <?= $statusFilter === 'Completed' ? 'bg-white text-success' : 'bg-success-subtle text-success' ?> rounded-pill ms-1"><?= $counts['Completed'] ?? 0 ?></span>
                            </a>
                        </li>

                        <!-- Dibatalkan -->
                        <li class="nav-item">
                            <a class="nav-link rounded-pill px-3 py-2 <?= $statusFilter === 'Cancelled' ? 'active bg-danger text-white shadow-sm fw-bold' : 'text-muted bg-light hover-bg' ?>" 
                               href="<?= App::baseUrl('/pendaftaran/riwayat?status=Cancelled') ?>">
                                <i class="bi bi-x-circle-fill me-1 text-danger"></i> Dibatalkan 
                                <span class="badge <?= $statusFilter === 'Cancelled' ? 'bg-white text-danger' : 'bg-danger-subtle text-danger' ?> rounded-pill ms-1"><?= $counts['Cancelled'] ?? 0 ?></span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Instant Filter Search -->
                <div class="col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="filterKeyword" class="form-control bg-light border-start-0" placeholder="Cari kode booking, poli, atau keluhan...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Context Guide Banner -->
    <?php if ($statusFilter === 'Pending'): ?>
        <div class="alert alert-warning border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-center gap-3">
            <div class="bg-warning text-dark p-2 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="small">
                <strong class="d-block text-dark fw-bold mb-1">Status: Menunggu Konfirmasi Loket & Poli</strong>
                Pendaftaran antrean Anda telah masuk ke sistem. Silakan hadir di Puskesmas Astanajapura minimal 15 menit sebelum waktu layanan dengan menunjukkan e-Ticket dan membawa kartu identitas (KTP/KK).
            </div>
        </div>
    <?php elseif ($statusFilter === 'Confirmed'): ?>
        <div class="alert alert-info border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-center gap-3">
            <div class="bg-primary text-white p-2 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                <i class="bi bi-patch-check-fill"></i>
            </div>
            <div class="small">
                <strong class="d-block text-dark fw-bold mb-1">Status: Dikonfirmasi & Siap Dipanggil</strong>
                Pendaftaran Anda telah disetujui oleh petugas Puskesmas. Anda dapat langsung menuju ke ruang tunggu poliklinik tujuan dan memantau pemanggilan nomor antrean di layar display atau menu Cek Antrean.
            </div>
        </div>
    <?php elseif ($statusFilter === 'Completed'): ?>
        <div class="alert alert-success border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-center gap-3">
            <div class="bg-success text-white p-2 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="small">
                <strong class="d-block text-dark fw-bold mb-1">Status: Pemeriksaan Selesai</strong>
                Riwayat pemeriksaan Anda telah tuntas dilaksanakan. Simpan e-Ticket sebagai bukti arsip kunjungan berobat. Anda dapat mendaftar kembali sewaktu-waktu atau membaca materi edukasi promkes terkait.
            </div>
        </div>
    <?php elseif ($statusFilter === 'Cancelled'): ?>
        <div class="alert alert-danger border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-center gap-3">
            <div class="bg-danger text-white p-2 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                <i class="bi bi-x-circle-fill"></i>
            </div>
            <div class="small">
                <strong class="d-block text-dark fw-bold mb-1">Status: Pendaftaran Dibatalkan</strong>
                Antrean pada daftar ini telah dibatalkan (oleh pasien atau sistem). Kuota antrean telah dikembalikan. Jika Anda masih membutuhkan pemeriksaan, silakan klik tombol <strong>Daftar Ulang Sekarang</strong> di kartu terkait.
            </div>
        </div>
    <?php endif; ?>

    <!-- Registrations Grid -->
    <?php if (empty($registrations)): ?>
        <!-- Empty State Component -->
        <div class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center my-4">
            <div class="mb-3">
                <?php if ($statusFilter === 'Pending'): ?>
                    <div class="avatar-lg bg-warning-subtle text-warning-emphasis rounded-circle mx-auto d-flex align-items-center justify-content-center shadow-xs" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="bi bi-hourglass"></i>
                    </div>
                <?php elseif ($statusFilter === 'Confirmed'): ?>
                    <div class="avatar-lg bg-primary-subtle text-primary rounded-circle mx-auto d-flex align-items-center justify-content-center shadow-xs" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                <?php elseif ($statusFilter === 'Completed'): ?>
                    <div class="avatar-lg bg-success-subtle text-success rounded-circle mx-auto d-flex align-items-center justify-content-center shadow-xs" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                <?php elseif ($statusFilter === 'Cancelled'): ?>
                    <div class="avatar-lg bg-danger-subtle text-danger rounded-circle mx-auto d-flex align-items-center justify-content-center shadow-xs" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="bi bi-shield-x"></i>
                    </div>
                <?php else: ?>
                    <div class="avatar-lg bg-primary-subtle text-primary rounded-circle mx-auto d-flex align-items-center justify-content-center shadow-xs" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="bi bi-ticket-perforated"></i>
                    </div>
                <?php endif; ?>
            </div>

            <h4 class="fw-bold text-dark mb-2">
                <?php if ($statusFilter === 'Pending'): ?>
                    Tidak Ada Antrean Menunggu
                <?php elseif ($statusFilter === 'Confirmed'): ?>
                    Tidak Ada Pendaftaran yang Dikonfirmasi
                <?php elseif ($statusFilter === 'Completed'): ?>
                    Belum Ada Riwayat Pemeriksaan Selesai
                <?php elseif ($statusFilter === 'Cancelled'): ?>
                    Tidak Ada Pendaftaran yang Dibatalkan
                <?php else: ?>
                    Belum Ada Riwayat Pendaftaran
                <?php endif; ?>
            </h4>

            <p class="text-muted small mx-auto mb-4" style="max-width: 520px;">
                <?php if ($statusFilter === 'Pending'): ?>
                    Saat ini Anda tidak memiliki antrean yang sedang menunggu. Jika Anda berencana berkunjung ke Puskesmas Astanajapura, buat antrean online sekarang tanpa perlu antre di loket.
                <?php elseif ($statusFilter === 'Confirmed'): ?>
                    Semua pendaftaran Anda mungkin masih dalam status menunggu atau telah selesai diperiksa dokter.
                <?php elseif ($statusFilter === 'Completed'): ?>
                    Riwayat pemeriksaan yang telah tuntas dilaksanakan bersama dokter akan tersimpan rapi di sini untuk keperluan arsip kesehatan Anda.
                <?php elseif ($statusFilter === 'Cancelled'): ?>
                    Bagus! Anda tidak memiliki antrean yang dibatalkan. Semua antrean Anda aktif atau telah selesai dilaksanakan.
                <?php else: ?>
                    Dapatkan nomor antrean online Puskesmas Astanajapura secara gratis dari rumah. Pilih layanan poli, tentukan tanggal kunjungan, dan dapatkan e-Ticket resmi seketika.
                <?php endif; ?>
            </p>

            <div class="d-flex justify-content-center flex-wrap gap-2">
                <a href="<?= App::baseUrl('/pendaftaran/baru') ?>" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> Daftar Antrean Sekarang
                </a>
                <a href="<?= App::baseUrl('/layanan') ?>" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
                    <i class="bi bi-hospital me-1"></i> Lihat Layanan Poli
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4" id="registrationsContainer">
            <?php foreach ($registrations as $reg): 
                $status = $reg['status'];
                $statusColor = $statusColors[$status] ?? 'secondary';
                $statusLabel = $statusLabels[$status] ?? $status;
                $statusIcon = $statusIcons[$status] ?? 'bi-circle-fill';
                $isCancellable = in_array($status, ['Pending', 'Confirmed'], true);
                $icon = $reg['service_icon'] ?? 'bi-hospital';
                $queueNo = str_pad((string)$reg['queue_number'], 3, '0', STR_PAD_LEFT);
                $regDateFormatted = date('d F Y', strtotime((string)$reg['registration_date']));
                $regDateShort = date('d/m/Y', strtotime((string)$reg['registration_date']));
            ?>
                <div class="col-md-6 col-lg-4 reg-item-card" 
                     data-code="<?= strtolower((string)$reg['registration_code']) ?>"
                     data-service="<?= strtolower((string)$reg['service_name']) ?>"
                     data-complaint="<?= strtolower((string)$reg['complaint']) ?>">
                    <div class="card border-0 shadow-sm rounded-4 h-100 hover-shadow transition-all position-relative overflow-hidden bg-white">
                        <!-- Top Colored Status Strip -->
                        <div class="position-absolute top-0 start-0 w-100 bg-<?= $statusColor ?>" style="height: 5px;"></div>
                        
                        <div class="card-body p-4 d-flex flex-column">
                            <!-- Header Row: Status Badge & Date -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-<?= $statusColor ?>-subtle text-<?= $statusColor ?> border border-<?= $statusColor ?>-subtle px-3 py-1 rounded-pill fw-bold small">
                                    <i class="bi <?= $statusIcon ?> me-1"></i> <?= $statusLabel ?>
                                </span>
                                <div class="text-muted small fw-semibold bg-light px-2 py-1 rounded-3">
                                    <i class="bi bi-calendar-event me-1 text-primary"></i> <?= $regDateShort ?>
                                </div>
                            </div>
                            
                            <!-- Poli / Service Info -->
                            <div class="d-flex align-items-start gap-2 mb-2">
                                <div class="bg-primary-subtle text-primary p-2 rounded-3 fs-5 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi <?= htmlspecialchars((string)$icon) ?>"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars((string)$reg['service_name']) ?></h5>
                                    <small class="text-muted"><i class="bi bi-person-badge text-primary me-1"></i> <?= htmlspecialchars((string)($reg['doctor_name'] ?? 'Dokter Jaga Poli')) ?></small>
                                </div>
                            </div>

                            <!-- Queue Number & Ticket Code Box -->
                            <div class="bg-light rounded-4 p-3 my-3 d-flex justify-content-between align-items-center border">
                                <div>
                                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Nomor Antrean</div>
                                    <div class="fs-2 fw-extrabold text-primary lh-1 mt-1">#<?= $queueNo ?></div>
                                    <small class="text-muted" style="font-size: 0.7rem;">Puskesmas Astanajapura</small>
                                </div>
                                <div class="text-end">
                                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Kode Registrasi</div>
                                    <div class="fw-bold font-monospace small bg-white px-2 py-1 rounded border shadow-2xs mt-1 text-dark d-inline-flex align-items-center gap-1">
                                        <span><?= htmlspecialchars((string)$reg['registration_code']) ?></span>
                                        <button type="button" class="btn btn-link p-0 text-muted hover-primary" onclick="copyText('<?= htmlspecialchars((string)$reg['registration_code']) ?>', this)" title="Salin Kode">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Complaint Text -->
                            <div class="mb-3">
                                <div class="text-muted small fw-semibold mb-1" style="font-size: 0.75rem;">Keluhan yang Didaftarkan:</div>
                                <div class="p-2 rounded-3 bg-light-subtle border small text-dark fst-italic text-truncate-2" title="<?= htmlspecialchars((string)$reg['complaint']) ?>">
                                    "<?= htmlspecialchars((string)$reg['complaint']) ?>"
                                </div>
                            </div>

                            <!-- Notes / Petugas Feedback if exists -->
                            <?php if (!empty($reg['notes'])): ?>
                                <div class="alert alert-light border small mb-3 p-2 rounded-3 text-secondary">
                                    <i class="bi bi-info-circle me-1 text-primary"></i> <strong>Catatan:</strong> <?= htmlspecialchars((string)$reg['notes']) ?>
                                </div>
                            <?php endif; ?>

                            <!-- Action Buttons based on Status -->
                            <div class="mt-auto pt-2 border-top">
                                <?php if ($status === 'Pending'): ?>
                                    <!-- Menunggu Actions -->
                                    <div class="d-flex gap-2">
                                        <a href="<?= App::baseUrl('/pendaftaran/tiket/' . urlencode((string)$reg['registration_code'])) ?>" class="btn btn-primary flex-grow-1 rounded-pill fw-semibold btn-sm py-2">
                                            <i class="bi bi-ticket-perforated me-1"></i> Buka E-Ticket
                                        </a>
                                        <a href="<?= App::baseUrl('/cek-antrean?q=' . urlencode((string)$reg['registration_code'])) ?>" class="btn btn-outline-info rounded-pill px-3 btn-sm py-2" title="Cek Posisi Antrean">
                                            <i class="bi bi-display"></i>
                                        </a>
                                        <?php if ($isCancellable): ?>
                                            <button type="button" class="btn btn-outline-danger rounded-pill px-3 btn-sm py-2" data-bs-toggle="modal" data-bs-target="#cancelModal<?= $reg['id'] ?>" title="Batalkan Pendaftaran">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-center mt-2">
                                        <small class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-shield-check text-warning me-1"></i>Harap bawa KTP/KK ke loket verifikasi</small>
                                    </div>

                                <?php elseif ($status === 'Confirmed'): ?>
                                    <!-- Dikonfirmasi Actions -->
                                    <div class="d-flex gap-2">
                                        <a href="<?= App::baseUrl('/pendaftaran/tiket/' . urlencode((string)$reg['registration_code'])) ?>" class="btn btn-primary flex-grow-1 rounded-pill fw-bold btn-sm py-2">
                                            <i class="bi bi-qr-code-scan me-1"></i> Buka E-Ticket Digital
                                        </a>
                                        <a href="<?= App::baseUrl('/cek-antrean?q=' . urlencode((string)$reg['registration_code'])) ?>" class="btn btn-outline-primary rounded-pill px-3 btn-sm py-2" title="Pantau Panggilan Antrean">
                                            <i class="bi bi-megaphone"></i>
                                        </a>
                                        <?php if ($isCancellable): ?>
                                            <button type="button" class="btn btn-outline-danger rounded-pill px-3 btn-sm py-2" data-bs-toggle="modal" data-bs-target="#cancelModal<?= $reg['id'] ?>" title="Batalkan">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-center mt-2">
                                        <small class="text-success fw-semibold" style="font-size: 0.7rem;"><i class="bi bi-geo-alt-fill me-1"></i>Siap dipanggil di ruang tunggu poli</small>
                                    </div>

                                <?php elseif ($status === 'Completed'): ?>
                                    <!-- Selesai Actions -->
                                    <div class="d-grid gap-2">
                                        <div class="d-flex gap-2">
                                            <a href="<?= App::baseUrl('/pendaftaran/tiket/' . urlencode((string)$reg['registration_code'])) ?>" class="btn btn-outline-success flex-grow-1 rounded-pill fw-semibold btn-sm py-2">
                                                <i class="bi bi-file-earmark-check me-1"></i> Arsip Bukti Berobat
                                            </a>
                                            <a href="<?= App::baseUrl('/pendaftaran/baru?poli=' . $reg['health_service_id']) ?>" class="btn btn-primary rounded-pill px-3 btn-sm py-2 fw-semibold" title="Daftar Kunjungan Baru ke Poli Ini">
                                                <i class="bi bi-arrow-repeat me-1"></i> Daftar Lagi
                                            </a>
                                        </div>
                                        <div class="d-flex justify-content-center gap-3 pt-1">
                                            <a href="<?= App::baseUrl('/kuis') ?>" class="small text-decoration-none text-primary fw-semibold" style="font-size: 0.75rem;">
                                                <i class="bi bi-award me-1"></i> Kuis Kesehatan
                                            </a>
                                            <span class="text-muted" style="font-size: 0.75rem;">&bull;</span>
                                            <a href="<?= App::baseUrl('/artikel') ?>" class="small text-decoration-none text-success fw-semibold" style="font-size: 0.75rem;">
                                                <i class="bi bi-journal-text me-1"></i> Tips Hidup Sehat
                                            </a>
                                        </div>
                                    </div>

                                <?php elseif ($status === 'Cancelled'): ?>
                                    <!-- Dibatalkan Actions -->
                                    <div class="d-flex gap-2">
                                        <a href="<?= App::baseUrl('/pendaftaran/baru?poli=' . $reg['health_service_id']) ?>" class="btn btn-primary flex-grow-1 rounded-pill fw-bold btn-sm py-2">
                                            <i class="bi bi-arrow-repeat me-1"></i> Daftar Ulang Sekarang
                                        </a>
                                        <a href="<?= App::baseUrl('/pendaftaran/tiket/' . urlencode((string)$reg['registration_code'])) ?>" class="btn btn-outline-secondary rounded-pill px-3 btn-sm py-2" title="Detail Tiket">
                                            <i class="bi bi-ticket"></i>
                                        </a>
                                    </div>
                                    <div class="text-center mt-2">
                                        <a href="<?= App::baseUrl('/kontak') ?>" class="small text-muted text-decoration-none" style="font-size: 0.7rem;">
                                            <i class="bi bi-question-circle me-1"></i> Butuh bantuan petugas?
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Cancel Modal for Pending or Confirmed -->
                        <?php if ($isCancellable): ?>
                            <div class="modal fade" id="cancelModal<?= $reg['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 shadow">
                                        <div class="modal-header border-bottom-0 pb-0">
                                            <h5 class="modal-title fw-bold text-dark">Batalkan Pendaftaran Antrean</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body py-4 text-center">
                                            <div class="bg-danger-subtle text-danger rounded-circle d-inline-flex p-3 mb-3">
                                                <i class="bi bi-exclamation-triangle-fill fs-2"></i>
                                            </div>
                                            <h5 class="fw-bold text-dark">Apakah Anda yakin ingin membatalkan?</h5>
                                            <p class="text-muted small mb-0 px-3">
                                                Pendaftaran untuk layanan <strong><?= htmlspecialchars((string)$reg['service_name']) ?></strong> pada tanggal <strong><?= $regDateFormatted ?></strong> dengan kode <strong><?= htmlspecialchars((string)$reg['registration_code']) ?></strong> akan dibatalkan dan kuota antrean akan dikembalikan.
                                            </p>
                                        </div>
                                        <div class="modal-footer border-top-0 pt-0 justify-content-center pb-4">
                                            <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Tutup</button>
                                            <form action="<?= App::baseUrl('/pendaftaran/batal/' . $reg['id']) ?>" method="POST" class="d-inline">
                                                <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                                                <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                                                    <i class="bi bi-trash3 me-1"></i> Ya, Batalkan Antrean
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
// Real-time client filter search
document.getElementById('filterKeyword')?.addEventListener('input', function(e) {
    const val = e.target.value.toLowerCase().trim();
    const cards = document.querySelectorAll('.reg-item-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const code = card.getAttribute('data-code') || '';
        const service = card.getAttribute('data-service') || '';
        const complaint = card.getAttribute('data-complaint') || '';

        if (code.includes(val) || service.includes(val) || complaint.includes(val)) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
});

// Copy text helper
function copyText(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const origIcon = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check text-success"></i>';
        setTimeout(() => {
            btn.innerHTML = origIcon;
        }, 2000);
    }).catch(err => {
        console.error('Copy failed', err);
    });
}
</script>

<style>
.hover-bg:hover {
    background-color: #e9ecef !important;
}
.hover-shadow:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.08) !important;
}
.transition-all {
    transition: all 0.25s ease-in-out;
}
.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.hover-primary:hover {
    color: var(--bs-primary) !important;
}
</style>
