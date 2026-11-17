<?php
use App\Config\App;
use App\Core\Session;

$today = $today ?? date('Y-m-d');
$currentTimeframe = $timeframe ?? 'all';
$currentStatus = $statusFilter ?? 'all';
$currentSearch = $search ?? '';
?>

<!-- Admin Overview Header -->
<div class="card border-0 shadow-sm rounded-4 bg-dark text-white p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold small mb-2">Panel Petugas Puskesmas</div>
            <h3 class="fw-bold mb-1 text-white">Puskesmas Astanajapura Dashboard</h3>
            <p class="text-white-50 mb-0 small">
                Tanggal Hari Ini: <strong><?= date('d F Y') ?></strong> &bull; Pemantauan Seluruh Riwayat Antrean & Layanan (Tersimpan Permanen & Tidak Hilang Berganti Hari)
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= App::baseUrl('/admin/pendaftaran?timeframe=all') ?>" class="btn btn-warning rounded-pill px-4 fw-bold shadow">
                <i class="bi bi-card-checklist me-1"></i> Semua Riwayat Pendaftaran
            </a>
        </div>
    </div>
</div>

<!-- Stats Counter Grid (Akumulatif & Tetap Terdata Walau Berganti Hari) -->
<div class="row g-3 mb-4">
    <!-- Card 1: Total Semua Riwayat Pendaftaran -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white border-start border-4 border-primary h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Total Semua Antrean</span>
                    <h2 class="fw-extrabold text-primary mb-1 mt-1"><?= number_format($stats['all_total'] ?? 0) ?></h2>
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                            <i class="bi bi-calendar-check me-1"></i><?= $stats['today_total'] ?? 0 ?> Hari Ini
                        </span>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                            <i class="bi bi-clock-history me-1"></i><?= $stats['past_total'] ?? 0 ?> Lampau
                        </span>
                        <?php if (($stats['upcoming_total'] ?? 0) > 0): ?>
                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                                <i class="bi bi-calendar-plus me-1"></i><?= $stats['upcoming_total'] ?> Mendatang
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="bg-primary-subtle text-primary p-3 rounded-4 fs-3">
                    <i class="bi bi-person-lines-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Menunggu / Antre -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white border-start border-4 border-warning h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Menunggu / Antre</span>
                    <h2 class="fw-extrabold text-warning-emphasis mb-1 mt-1"><?= number_format($stats['all_pending'] ?? 0) ?></h2>
                    <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">
                        <strong><?= $stats['today_pending'] ?? 0 ?></strong> hari ini &bull; <?= max(0, ($stats['all_pending'] ?? 0) - ($stats['today_pending'] ?? 0)) ?> tanggal lainnya
                    </small>
                </div>
                <div class="bg-warning-subtle text-warning-emphasis p-3 rounded-4 fs-3">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Dikonfirmasi / Dipanggil -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white border-start border-4 border-info h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Dikonfirmasi / Dipanggil</span>
                    <h2 class="fw-extrabold text-info-emphasis mb-1 mt-1"><?= number_format($stats['all_confirmed'] ?? 0) ?></h2>
                    <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">
                        <strong><?= $stats['today_confirmed'] ?? 0 ?></strong> hari ini &bull; aktif di ruang periksa
                    </small>
                </div>
                <div class="bg-info-subtle text-info-emphasis p-3 rounded-4 fs-3">
                    <i class="bi bi-megaphone-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 4: Selesai Berobat -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white border-start border-4 border-success h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Selesai Berobat</span>
                    <h2 class="fw-extrabold text-success mb-1 mt-1"><?= number_format($stats['all_completed'] ?? 0) ?></h2>
                    <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">
                        <strong><?= $stats['today_completed'] ?? 0 ?></strong> hari ini &bull; total riwayat tuntas
                    </small>
                </div>
                <div class="bg-success-subtle text-success p-3 rounded-4 fs-3">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Main Table: Riwayat & Antrean Pasien (Sebelumnya, Hari Ini, dan Seterusnya) -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
            <!-- Header Section with Title & Link -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-clock-history text-primary me-2"></i>Semua Riwayat & Panggilan Antrean Pasien
                    </h5>
                    <p class="text-muted small mb-0">
                        Seluruh data pendaftaran terdata di sini (sebelumnya, hari ini, dan seterusnya) serta tidak hilang jika berganti hari.
                    </p>
                </div>
                <a href="<?= App::baseUrl('/admin/pendaftaran') ?>" class="small text-primary text-decoration-none fw-semibold">
                    Kelola Lengkap &rarr;
                </a>
            </div>

            <!-- Filter Pills Waktu (Tidak Hilang Berganti Hari) -->
            <div class="d-flex flex-wrap gap-2 mb-3 pb-2 border-bottom">
                <a href="<?= App::baseUrl('/admin?timeframe=all&status=' . urlencode($currentStatus)) ?>" 
                   class="btn btn-sm rounded-pill <?= $currentTimeframe === 'all' ? 'btn-primary fw-bold shadow-xs' : 'btn-light border text-muted' ?>">
                    <i class="bi bi-collection me-1"></i> Semua Riwayat 
                    <span class="badge <?= $currentTimeframe === 'all' ? 'bg-white text-primary' : 'bg-secondary' ?> rounded-pill ms-1"><?= $stats['all_total'] ?? 0 ?></span>
                </a>
                <a href="<?= App::baseUrl('/admin?timeframe=today&status=' . urlencode($currentStatus)) ?>" 
                   class="btn btn-sm rounded-pill <?= $currentTimeframe === 'today' ? 'btn-primary fw-bold shadow-xs' : 'btn-light border text-muted' ?>">
                    <i class="bi bi-calendar-check me-1"></i> Hari Ini 
                    <span class="badge <?= $currentTimeframe === 'today' ? 'bg-white text-primary' : 'bg-secondary' ?> rounded-pill ms-1"><?= $stats['today_total'] ?? 0 ?></span>
                </a>
                <a href="<?= App::baseUrl('/admin?timeframe=past&status=' . urlencode($currentStatus)) ?>" 
                   class="btn btn-sm rounded-pill <?= $currentTimeframe === 'past' ? 'btn-primary fw-bold shadow-xs' : 'btn-light border text-muted' ?>">
                    <i class="bi bi-clock-history me-1"></i> Riwayat Lampau 
                    <span class="badge <?= $currentTimeframe === 'past' ? 'bg-white text-primary' : 'bg-secondary' ?> rounded-pill ms-1"><?= $stats['past_total'] ?? 0 ?></span>
                </a>
                <?php if (($stats['upcoming_total'] ?? 0) > 0): ?>
                    <a href="<?= App::baseUrl('/admin?timeframe=upcoming&status=' . urlencode($currentStatus)) ?>" 
                       class="btn btn-sm rounded-pill <?= $currentTimeframe === 'upcoming' ? 'btn-primary fw-bold shadow-xs' : 'btn-light border text-muted' ?>">
                        <i class="bi bi-calendar-plus me-1"></i> Jadwal Mendatang 
                        <span class="badge <?= $currentTimeframe === 'upcoming' ? 'bg-white text-primary' : 'bg-secondary' ?> rounded-pill ms-1"><?= $stats['upcoming_total'] ?></span>
                    </a>
                <?php endif; ?>
            </div>

            <?php if (!empty($registrations)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No.</th>
                                <th>Tgl Kunjungan</th>
                                <th>Pasien / NIK</th>
                                <th>Layanan Poli</th>
                                <th>Desa</th>
                                <th>Status</th>
                                <th>Aksi Cepat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registrations as $reg): 
                                $isToday = ($reg['registration_date'] === $today);
                                $isPast = ($reg['registration_date'] < $today);
                                $isUpcoming = ($reg['registration_date'] > $today);

                                if ($isToday) {
                                    $dateBadge = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1"><i class="bi bi-calendar-check me-1"></i>Hari Ini</span>';
                                } elseif ($isUpcoming) {
                                    $dateBadge = '<span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-1"><i class="bi bi-calendar-plus me-1"></i>Mendatang</span>';
                                } else {
                                    $dateBadge = '<span class="badge bg-light text-secondary border rounded-pill px-2 py-1"><i class="bi bi-clock-history me-1"></i>Lampau</span>';
                                }
                            ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary fs-6 px-2 py-1">#<?= $reg['queue_number'] ?></span>
                                    </td>
                                    <td>
                                        <?= $dateBadge ?>
                                        <div class="text-dark fw-bold mt-1 font-monospace" style="font-size: 0.78rem;">
                                            <?= date('d/m/Y', strtotime($reg['registration_date'])) ?>
                                        </div>
                                        <small class="text-muted d-block" style="font-size: 0.7rem;">
                                            <?= htmlspecialchars($reg['registration_code']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong class="text-dark d-block"><?= htmlspecialchars($reg['patient_name']) ?></strong>
                                        <small class="text-muted font-monospace"><?= htmlspecialchars($reg['patient_nik']) ?></small>
                                        <?php if (!empty($reg['patient_phone'])): ?>
                                            <div class="text-muted" style="font-size: 0.7rem;">
                                                <i class="bi bi-whatsapp text-success me-1"></i><?= htmlspecialchars($reg['patient_phone']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($reg['service_name']) ?></span>
                                        <?php if (!empty($reg['doctor_name'])): ?>
                                            <small class="text-muted d-block" style="font-size: 0.7rem;"><?= htmlspecialchars($reg['doctor_name']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($reg['patient_village'] ?? 'Astanajapura') ?></span>
                                    </td>
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
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle rounded-pill py-0 px-2" style="font-size: 0.75rem;" type="button" data-bs-toggle="dropdown">
                                                Ubah Status
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm small">
                                                <li>
                                                    <a class="dropdown-item" href="<?= App::baseUrl('/pendaftaran/tiket/' . $reg['registration_code']) ?>" target="_blank">
                                                        <i class="bi bi-printer me-2"></i>Cetak E-Ticket
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="<?= App::baseUrl('/admin/pendaftaran/status') ?>" method="POST">
                                                        <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                                                        <input type="hidden" name="id" value="<?= $reg['id'] ?>">
                                                        <input type="hidden" name="status" value="Confirmed">
                                                        <button type="submit" class="dropdown-item text-primary fw-semibold"><i class="bi bi-megaphone me-2"></i>Konfirmasi & Panggil</button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="<?= App::baseUrl('/admin/pendaftaran/status') ?>" method="POST">
                                                        <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                                                        <input type="hidden" name="id" value="<?= $reg['id'] ?>">
                                                        <input type="hidden" name="status" value="Completed">
                                                        <button type="submit" class="dropdown-item text-success fw-semibold"><i class="bi bi-check-circle me-2"></i>Selesai Pelayanan</button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="<?= App::baseUrl('/admin/pendaftaran/status') ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan antrean ini?');">
                                                        <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                                                        <input type="hidden" name="id" value="<?= $reg['id'] ?>">
                                                        <input type="hidden" name="status" value="Cancelled">
                                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-x-circle me-2"></i>Batalkan Antrean</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
                    <p class="small mb-1">Tidak ada riwayat pendaftaran pada filter ini.</p>
                    <a href="<?= App::baseUrl('/admin?timeframe=all') ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 mt-1">
                        Tampilkan Semua Riwayat
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Active Services & Calling Monitor -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-hospital text-primary me-2"></i>Kapasitas Poli Hari Ini</h5>
            <div class="list-group list-group-flush small">
                <?php foreach ($serviceStats as $srv): ?>
                    <div class="list-group-item px-0 py-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-dark"><?= htmlspecialchars($srv['name']) ?></strong>
                            <span class="badge bg-light text-dark border"><?= $srv['registered_today'] ?> / <?= $srv['quota_daily'] ?></span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <?php $percent = min(100, round(($srv['registered_today'] / max(1, $srv['quota_daily'])) * 100)); ?>
                            <div class="progress-bar <?= ($percent >= 90) ? 'bg-danger' : (($percent >= 60) ? 'bg-warning' : 'bg-primary') ?>" style="width: <?= $percent ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Demographics by Village Astanajapura -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Distribusi Pasien Berdasarkan Desa (Kec. Astanajapura)</h5>
        <a href="<?= App::baseUrl('/admin/desa') ?>" class="small text-primary text-decoration-none fw-semibold">Lihat Detail Desa &rarr;</a>
    </div>

    <div class="row g-3">
        <?php foreach ($villageStats as $vStat): ?>
            <div class="col-md-3 col-sm-6">
                <div class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center">
                    <div>
                        <strong class="text-dark d-block small"><?= htmlspecialchars($vStat['village_name']) ?></strong>
                        <small class="text-muted">Astanajapura</small>
                    </div>
                    <span class="badge bg-primary fs-6"><?= $vStat['total_users'] ?> Pasien</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-hospital text-primary me-2"></i>Kapasitas Poli Hari Ini</h5>
            <div class="list-group list-group-flush small">
                <?php foreach ($serviceStats as $srv): ?>
                    <div class="list-group-item px-0 py-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-dark"><?= htmlspecialchars($srv['name']) ?></strong>
                            <span class="badge bg-light text-dark border"><?= $srv['registered_today'] ?> / <?= $srv['quota_daily'] ?></span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <?php $percent = min(100, round(($srv['registered_today'] / max(1, $srv['quota_daily'])) * 100)); ?>
                            <div class="progress-bar <?= ($percent >= 90) ? 'bg-danger' : (($percent >= 60) ? 'bg-warning' : 'bg-primary') ?>" style="width: <?= $percent ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Demographics by Village Astanajapura -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Distribusi Pasien Berdasarkan Desa (Kec. Astanajapura)</h5>
        <a href="<?= App::baseUrl('/admin/desa') ?>" class="small text-primary text-decoration-none fw-semibold">Lihat Detail Desa &rarr;</a>
    </div>

    <div class="row g-3">
        <?php foreach ($villageStats as $vStat): ?>
            <div class="col-md-3 col-sm-6">
                <div class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center">
                    <div>
                        <strong class="text-dark d-block small"><?= htmlspecialchars($vStat['village_name']) ?></strong>
                        <small class="text-muted">Astanajapura</small>
                    </div>
                    <span class="badge bg-primary fs-6"><?= $vStat['total_users'] ?> Pasien</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
