<?php
use App\Config\App;
use App\Core\Auth;
?>

<!-- Welcome Banner -->
<div class="card border-0 shadow-sm rounded-4 bg-primary text-white p-4 mb-4 position-relative overflow-hidden">
    <div class="position-relative z-2">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 small mb-2">Portal Masyarakat Puskesmas Astanajapura</span>
                <h3 class="fw-bold mb-1 text-white">Selamat Datang, <?= htmlspecialchars($user['name']) ?>!</h3>
                <p class="text-white-75 mb-0 small">
                    <i class="bi bi-geo-alt-fill text-warning me-1"></i> Desa <?= htmlspecialchars($user['village_name'] ?? 'Astanajapura') ?>, Kecamatan Astanajapura
                    <?php if (!empty($user['phone'])): ?>
                        &bull; <i class="bi bi-whatsapp text-success-emphasis me-1"></i> <?= htmlspecialchars($user['phone']) ?>
                    <?php endif; ?>
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= App::baseUrl('/profil') ?>" class="btn btn-outline-light rounded-pill px-3 py-2 btn-sm fw-semibold">
                    <i class="bi bi-person-gear me-1"></i> Profil Saya
                </a>
                <a href="<?= App::baseUrl('/layanan') ?>" class="btn btn-warning rounded-pill px-4 py-2 btn-sm fw-bold shadow">
                    <i class="bi bi-hospital me-1"></i> Menu Layanan
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Verification Status Alert Banner -->
<?php if (!$isFullyVerified): ?>
    <div class="card border-0 shadow-sm rounded-4 border-start border-5 border-warning bg-warning-subtle p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-start gap-3">
                <div class="bg-warning text-dark p-2 rounded-3 fs-4">
                    <i class="bi bi-shield-exclamation"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-1">Status Verifikasi Akun Anda:</h6>
                    <div class="d-flex flex-wrap gap-2 align-items-center mt-1">
                        <!-- WhatsApp Badge -->
                        <?php if ($isPhoneVerified): ?>
                            <span class="badge bg-success rounded-pill px-3 py-1"><i class="bi bi-whatsapp me-1"></i> WhatsApp: Terverifikasi</span>
                        <?php else: ?>
                            <span class="badge bg-danger rounded-pill px-3 py-1"><i class="bi bi-whatsapp me-1"></i> WhatsApp: Belum Verifikasi</span>
                        <?php endif; ?>

                        <!-- Email Badge -->
                        <?php if ($isEmailVerified): ?>
                            <span class="badge bg-success rounded-pill px-3 py-1"><i class="bi bi-envelope me-1"></i> Email: Terverifikasi</span>
                        <?php else: ?>
                            <span class="badge bg-secondary rounded-pill px-3 py-1"><i class="bi bi-envelope me-1"></i> Email: Belum Verifikasi</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <a href="<?= App::baseUrl('/verifikasi') ?>" class="btn btn-warning btn-sm rounded-pill px-4 fw-bold shadow-xs">
                <i class="bi bi-shield-check me-1"></i> Verifikasi Sekarang
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- 8 Menu Layanan & Shortcut Cards -->
<div class="row g-3 mb-4">
    <!-- 1. Menu Layanan Kesehatan -->
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 hover-card">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary-subtle text-primary p-3 rounded-3 fs-4">
                    <i class="bi bi-hospital-fill"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-1">Layanan Poli</h6>
                    <a href="<?= App::baseUrl('/layanan') ?>" class="small text-primary text-decoration-none fw-semibold">Lihat Jadwal &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Pendaftaran Saya -->
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 hover-card">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-success-subtle text-success p-3 rounded-3 fs-4">
                    <i class="bi bi-ticket-perforated-fill"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-1">Pendaftaran Saya</h6>
                    <a href="<?= App::baseUrl('/pendaftaran/riwayat') ?>" class="small text-success text-decoration-none fw-semibold">Riwayat Tiket &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Materi Edukasi Kesehatan -->
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 hover-card">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-info-subtle text-info-emphasis p-3 rounded-3 fs-4">
                    <i class="bi bi-journal-medical"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-1">Materi Promkes</h6>
                    <a href="<?= App::baseUrl('/artikel') ?>" class="small text-info-emphasis text-decoration-none fw-semibold">Baca Edukasi &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Kuis Kesehatan -->
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 hover-card">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-warning-subtle text-warning-emphasis p-3 rounded-3 fs-4">
                    <i class="bi bi-award-fill"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-1">Kuis Kesehatan</h6>
                    <a href="<?= App::baseUrl('/kuis') ?>" class="small text-warning-emphasis text-decoration-none fw-semibold">Mulai Kuis &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Active Registration Card if Available -->
<?php if ($activeRegistration): ?>
    <div class="card border-0 shadow-sm rounded-4 border-start border-5 border-success mb-4 bg-white p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill fw-bold">Antrean Aktif Hari Ini</span>
                <small class="text-muted"><i class="bi bi-calendar-event me-1"></i> <?= date('d M Y', strtotime($activeRegistration['registration_date'])) ?></small>
            </div>
            <span class="badge bg-primary text-uppercase px-3 py-1 rounded-pill">Status: <?= $activeRegistration['status'] ?></span>
        </div>

        <div class="row align-items-center g-3">
            <div class="col-md-4 text-center border-end">
                <span class="text-muted small d-block">Nomor Antrean Anda:</span>
                <div class="display-3 fw-extrabold text-primary">#<?= $activeRegistration['queue_number'] ?></div>
                <div class="badge bg-light text-dark border"><?= htmlspecialchars($activeRegistration['registration_code']) ?></div>
            </div>
            <div class="col-md-5">
                <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($activeRegistration['service_name']) ?></h5>
                <p class="text-muted small mb-2"><i class="bi bi-person-badge text-primary me-1"></i> <?= htmlspecialchars($activeRegistration['doctor_name'] ?? 'Dokter Jaga') ?></p>
                <p class="text-muted small mb-0"><strong>Keluhan:</strong> <?= htmlspecialchars($activeRegistration['complaint']) ?></p>
            </div>
            <div class="col-md-3 text-md-end">
                <a href="<?= App::baseUrl('/pendaftaran/tiket/' . $activeRegistration['registration_code']) ?>" class="btn btn-outline-primary rounded-pill w-100 fw-semibold mb-2">
                    <i class="bi bi-qr-code-scan me-1"></i> Buka E-Ticket
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- 2-Column: Pendaftaran Saya & Ringkasan Profil Pasien -->
<div class="row g-4 mb-4">
    <!-- Col 1: Pendaftaran Saya -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-ticket-perforated text-primary me-2"></i>Pendaftaran Saya</h5>
                <a href="<?= App::baseUrl('/pendaftaran/riwayat') ?>" class="small text-primary text-decoration-none fw-semibold">Lihat Semua</a>
            </div>

            <?php if (!empty($recentRegistrations)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Tanggal</th>
                                <th>Poli</th>
                                <th>No.</th>
                                <th>Status</th>
                                <th>Tiket</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentRegistrations as $reg): ?>
                                <tr>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($reg['registration_code']) ?></span></td>
                                    <td><?= date('d/m/Y', strtotime($reg['registration_date'])) ?></td>
                                    <td><strong><?= htmlspecialchars($reg['service_name']) ?></strong></td>
                                    <td><span class="badge bg-primary">#<?= $reg['queue_number'] ?></span></td>
                                    <td>
                                        <?php
                                        $statusLabels = [
                                            'Pending'   => 'Menunggu',
                                            'Confirmed' => 'Dikonfirmasi',
                                            'Completed' => 'Selesai',
                                            'Cancelled' => 'Dibatalkan'
                                        ];
                                        $badgeClass = match($reg['status']) {
                                            'Completed', 'selesai'   => 'bg-success',
                                            'Confirmed', 'dipanggil' => 'bg-primary',
                                            'Pending', 'menunggu'    => 'bg-warning text-dark',
                                            'Cancelled', 'dibatalkan'=> 'bg-danger',
                                            default                  => 'bg-secondary'
                                        };
                                        $label = $statusLabels[$reg['status']] ?? $reg['status'];
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= $label ?></span>
                                    </td>
                                    <td>
                                        <a href="<?= App::baseUrl('/pendaftaran/tiket/' . $reg['registration_code']) ?>" class="btn btn-sm btn-outline-primary rounded-pill py-0 px-2" style="font-size: 0.75rem;">
                                            E-Ticket
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-clipboard-check fs-2 d-block mb-2 text-muted"></i>
                    <p class="small mb-0">Belum ada riwayat pendaftaran pemeriksaan.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Col 2: Profil Pasien Ringkas -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-person-badge text-primary me-2"></i>Profil Pasien</h5>
                <a href="<?= App::baseUrl('/profil') ?>" class="small text-primary text-decoration-none fw-semibold">Edit Profil</a>
            </div>

            <ul class="list-group list-group-flush small mb-0">
                <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                    <span class="text-muted">Nama:</span>
                    <strong class="text-dark"><?= htmlspecialchars($user['name']) ?></strong>
                </li>
                <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                    <span class="text-muted">Email:</span>
                    <strong class="text-dark"><?= htmlspecialchars($user['email']) ?></strong>
                </li>
                <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                    <span class="text-muted">No. WhatsApp:</span>
                    <strong class="text-dark"><?= htmlspecialchars($user['phone']) ?></strong>
                </li>
                <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                    <span class="text-muted">Desa Domisili:</span>
                    <strong class="text-dark">Desa <?= htmlspecialchars($user['village_name'] ?? 'Astanajapura') ?></strong>
                </li>
                <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                    <span class="text-muted">NIK KTP:</span>
                    <strong class="text-dark"><?= htmlspecialchars($user['nik'] ?? 'Belum diisi') ?></strong>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Materi Kesehatan Edukasi & Kuis Section -->
<div class="row g-4">
    <!-- Materi Edukasi -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-journal-text text-success me-2"></i>Materi Kesehatan Masyarakat</h5>
                <a href="<?= App::baseUrl('/artikel') ?>" class="small text-success text-decoration-none fw-semibold">Lihat Semua Materi &rarr;</a>
            </div>

            <div class="row g-3">
                <?php foreach ($articles as $art): ?>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 h-100 d-flex flex-column">
                            <span class="badge bg-success-subtle text-success rounded-pill small mb-2 text-truncate"><?= htmlspecialchars($art['category_name']) ?></span>
                            <h6 class="fw-bold text-dark mb-1 small text-truncate-2"><?= htmlspecialchars($art['title']) ?></h6>
                            <p class="text-muted small mb-3 flex-grow-1" style="font-size: 0.75rem;"><?= htmlspecialchars($art['summary']) ?></p>
                            <a href="<?= App::baseUrl('/artikel/' . $art['slug']) ?>" class="btn btn-sm btn-link text-success fw-bold p-0 text-decoration-none small">
                                Baca Materi &rarr;
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Kuis Kesehatan -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 bg-quiz-gradient text-white p-4 h-100">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-award-fill text-warning fs-4"></i>
                <h5 class="fw-bold text-white mb-0">Kuis PHBS</h5>
            </div>
            <p class="small text-white-75 mb-3">
                Uji pemahaman Anda tentang pola hidup bersih dan sehat seputar stunting, DBD, dan tensi darah.
            </p>
            <div class="mt-auto">
                <a href="<?= App::baseUrl('/kuis/mulai') ?>" class="btn btn-light rounded-pill btn-sm fw-bold px-4 text-primary shadow-sm w-100 mb-2">
                    <i class="bi bi-play-circle-fill me-1"></i> Mulai Kuis Interaktif
                </a>
                <a href="<?= App::baseUrl('/kuis') ?>" class="btn btn-outline-light rounded-pill btn-sm w-100" style="font-size: 0.75rem;">
                    Lihat Riwayat & Peringkat
                </a>
            </div>
        </div>
    </div>
</div>
