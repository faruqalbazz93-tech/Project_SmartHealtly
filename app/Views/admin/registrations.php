<?php
use App\Config\App;
use App\Core\Session;
?>

<div class="card border-0 shadow-sm rounded-4 bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="bi bi-people-fill text-primary me-2"></i>Kelola Antrean & Pendaftaran Pasien</h4>
            <p class="text-muted small mb-0">Atur pemanggilan pasien di loket dan poli secara teratur</p>
        </div>
    </div>

    <!-- Timeframe Filter Pills (Semua Riwayat, Hari Ini, Lampau, Mendatang) -->
    <?php
    $statusCounts = $statusCounts ?? ['all' => count($registrations)];
    $currentDate = htmlspecialchars($filterDate ?? '');
    $currentTimeframe = htmlspecialchars($timeframe ?? 'all');
    $currentPoli = htmlspecialchars((string)($filterService ?? ''));
    $currentSearch = htmlspecialchars($search);
    $baseQuery = "tanggal={$currentDate}&timeframe={$currentTimeframe}&poli={$currentPoli}&q={$currentSearch}";
    $today = date('Y-m-d');
    ?>

    <div class="d-flex flex-wrap gap-2 mb-2">
        <a href="<?= App::baseUrl('/admin/pendaftaran?timeframe=all&poli=' . $currentPoli . '&q=' . $currentSearch . '&status=' . $filterStatus) ?>" 
           class="btn btn-sm rounded-pill <?= ($currentTimeframe === 'all' && empty($currentDate)) ? 'btn-dark fw-bold shadow-xs' : 'btn-light border text-muted' ?>">
            <i class="bi bi-collection me-1"></i> Semua Riwayat (Permanen)
        </a>
        <a href="<?= App::baseUrl('/admin/pendaftaran?tanggal=' . $today . '&timeframe=today&poli=' . $currentPoli . '&q=' . $currentSearch . '&status=' . $filterStatus) ?>" 
           class="btn btn-sm rounded-pill <?= ($currentDate === $today || $currentTimeframe === 'today') ? 'btn-primary fw-bold shadow-xs' : 'btn-light border text-muted' ?>">
            <i class="bi bi-calendar-check me-1"></i> Hari Ini
        </a>
        <a href="<?= App::baseUrl('/admin/pendaftaran?timeframe=past&poli=' . $currentPoli . '&q=' . $currentSearch . '&status=' . $filterStatus) ?>" 
           class="btn btn-sm rounded-pill <?= ($currentTimeframe === 'past') ? 'btn-secondary fw-bold shadow-xs' : 'btn-light border text-muted' ?>">
            <i class="bi bi-clock-history me-1"></i> Riwayat Lampau
        </a>
        <a href="<?= App::baseUrl('/admin/pendaftaran?timeframe=upcoming&poli=' . $currentPoli . '&q=' . $currentSearch . '&status=' . $filterStatus) ?>" 
           class="btn btn-sm rounded-pill <?= ($currentTimeframe === 'upcoming') ? 'btn-info fw-bold shadow-xs' : 'btn-light border text-muted' ?>">
            <i class="bi bi-calendar-plus me-1"></i> Jadwal Mendatang
        </a>
    </div>

    <!-- Quick Status Filter Pills -->
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="<?= App::baseUrl('/admin/pendaftaran?' . $baseQuery . '&status=all') ?>" class="btn btn-sm rounded-pill <?= $filterStatus === 'all' ? 'btn-primary fw-bold shadow-xs' : 'btn-light border text-muted' ?>">
            Semua Status <span class="badge <?= $filterStatus === 'all' ? 'bg-white text-primary' : 'bg-secondary' ?> rounded-pill ms-1"><?= $statusCounts['all'] ?? 0 ?></span>
        </a>
        <a href="<?= App::baseUrl('/admin/pendaftaran?' . $baseQuery . '&status=Pending') ?>" class="btn btn-sm rounded-pill <?= $filterStatus === 'Pending' ? 'btn-warning text-dark fw-bold shadow-xs' : 'btn-light border text-muted' ?>">
            <i class="bi bi-hourglass-split me-1"></i> Menunggu <span class="badge <?= $filterStatus === 'Pending' ? 'bg-dark text-white' : 'bg-warning-subtle text-warning-emphasis' ?> rounded-pill ms-1"><?= $statusCounts['Pending'] ?? 0 ?></span>
        </a>
        <a href="<?= App::baseUrl('/admin/pendaftaran?' . $baseQuery . '&status=Confirmed') ?>" class="btn btn-sm rounded-pill <?= $filterStatus === 'Confirmed' ? 'btn-info text-dark fw-bold shadow-xs' : 'btn-light border text-muted' ?>">
            <i class="bi bi-check2-circle me-1"></i> Dikonfirmasi <span class="badge <?= $filterStatus === 'Confirmed' ? 'bg-dark text-white' : 'bg-primary-subtle text-primary' ?> rounded-pill ms-1"><?= $statusCounts['Confirmed'] ?? 0 ?></span>
        </a>
        <a href="<?= App::baseUrl('/admin/pendaftaran?' . $baseQuery . '&status=Completed') ?>" class="btn btn-sm rounded-pill <?= $filterStatus === 'Completed' ? 'btn-success text-white fw-bold shadow-xs' : 'btn-light border text-muted' ?>">
            <i class="bi bi-check-circle me-1"></i> Selesai <span class="badge <?= $filterStatus === 'Completed' ? 'bg-white text-success' : 'bg-success-subtle text-success' ?> rounded-pill ms-1"><?= $statusCounts['Completed'] ?? 0 ?></span>
        </a>
        <a href="<?= App::baseUrl('/admin/pendaftaran?' . $baseQuery . '&status=Cancelled') ?>" class="btn btn-sm rounded-pill <?= $filterStatus === 'Cancelled' ? 'btn-danger text-white fw-bold shadow-xs' : 'btn-light border text-muted' ?>">
            <i class="bi bi-x-circle me-1"></i> Dibatalkan <span class="badge <?= $filterStatus === 'Cancelled' ? 'bg-white text-danger' : 'bg-danger-subtle text-danger' ?> rounded-pill ms-1"><?= $statusCounts['Cancelled'] ?? 0 ?></span>
        </a>
    </div>

    <!-- Filter Form -->
    <form action="<?= App::baseUrl('/admin/pendaftaran') ?>" method="GET" class="p-3 bg-light rounded-4 mb-4 border">
        <div class="row g-3 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label class="form-label small fw-semibold text-dark mb-1">Tanggal Kunjungan</label>
                <input type="date" name="tanggal" value="<?= htmlspecialchars($filterDate) ?>" class="form-control form-control-sm bg-white">
            </div>

            <div class="col-md-3 col-sm-6">
                <label class="form-label small fw-semibold text-dark mb-1">Poli Layanan</label>
                <select name="poli" class="form-select form-select-sm bg-white">
                    <option value="">-- Semua Poli --</option>
                    <?php foreach ($services as $srv): ?>
                        <option value="<?= $srv['id'] ?>" <?= ($filterService == $srv['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($srv['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 col-sm-6">
                <label class="form-label small fw-semibold text-dark mb-1">Status</label>
                <select name="status" class="form-select form-select-sm bg-white">
                    <option value="">-- Semua Status --</option>
                    <option value="Pending" <?= ($filterStatus === 'Pending') ? 'selected' : '' ?>>Menunggu</option>
                    <option value="Confirmed" <?= ($filterStatus === 'Confirmed') ? 'selected' : '' ?>>Dikonfirmasi</option>
                    <option value="Completed" <?= ($filterStatus === 'Completed') ? 'selected' : '' ?>>Selesai</option>
                    <option value="Cancelled" <?= ($filterStatus === 'Cancelled') ? 'selected' : '' ?>>Dibatalkan</option>
                </select>
            </div>

            <div class="col-md-3 col-sm-6">
                <label class="form-label small fw-semibold text-dark mb-1">Cari Pasien / NIK / Kode</label>
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Nama/NIK/Kode" class="form-control form-control-sm bg-white">
            </div>

            <div class="col-md-1 col-sm-12">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill w-100 fw-semibold">
                    <i class="bi bi-funnel-fill"></i>
                </button>
            </div>
        </div>
    </form>

    <!-- Table of Registrations -->
    <?php if (!empty($registrations)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. Antrean</th>
                        <th>Kode Booking</th>
                        <th>Nama Pasien</th>
                        <th>NIK / No HP</th>
                        <th>Desa</th>
                        <th>Poli</th>
                        <th>Keluhan</th>
                        <th>Status</th>
                        <th>Aksi Panggilan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrations as $r): ?>
                        <tr>
                            <td>
                                <span class="badge bg-primary fs-6 px-3 py-1 rounded-pill">#<?= $r['queue_number'] ?></span>
                            </td>
                            <td>
                                <?php
                                $isToday = ($r['registration_date'] === $today);
                                $isPast = ($r['registration_date'] < $today);
                                $isUpcoming = ($r['registration_date'] > $today);

                                if ($isToday) {
                                    $dateBadge = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0" style="font-size:0.68rem;"><i class="bi bi-calendar-check me-1"></i>Hari Ini</span>';
                                } elseif ($isUpcoming) {
                                    $dateBadge = '<span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-0" style="font-size:0.68rem;"><i class="bi bi-calendar-plus me-1"></i>Mendatang</span>';
                                } else {
                                    $dateBadge = '<span class="badge bg-light text-secondary border rounded-pill px-2 py-0" style="font-size:0.68rem;"><i class="bi bi-clock-history me-1"></i>Lampau</span>';
                                }
                                ?>
                                <span class="text-dark fw-bold font-monospace"><?= htmlspecialchars($r['registration_code']) ?></span>
                                <div class="mt-1"><?= $dateBadge ?> <span class="text-dark fw-semibold" style="font-size: 0.72rem;"><?= date('d/m/Y', strtotime($r['registration_date'])) ?></span></div>
                            </td>
                            <td>
                                <strong class="text-dark"><?= htmlspecialchars($r['patient_name']) ?></strong>
                            </td>
                            <td>
                                <div class="text-dark font-monospace"><?= htmlspecialchars($r['patient_nik']) ?></div>
                                <small class="text-muted"><i class="bi bi-whatsapp text-success me-1"></i> <?= htmlspecialchars($r['patient_phone']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($r['patient_village'] ?? 'Astanajapura') ?></span>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark"><?= htmlspecialchars($r['service_name']) ?></span>
                            </td>
                            <td class="text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($r['complaint']) ?>">
                                <?= htmlspecialchars($r['complaint']) ?>
                            </td>
                            <td>
                                <?php
                                $badgeClass = match($r['status']) {
                                    'Completed' => 'bg-success',
                                    'Confirmed' => 'bg-primary',
                                    'Pending'   => 'bg-warning text-dark',
                                    'Cancelled' => 'bg-danger',
                                    default     => 'bg-secondary'
                                };
                                $statusLabels = [
                                    'Pending'   => 'Menunggu',
                                    'Confirmed' => 'Dikonfirmasi',
                                    'Completed' => 'Selesai',
                                    'Cancelled' => 'Dibatalkan'
                                ];
                                $label = $statusLabels[$r['status']] ?? $r['status'];
                                ?>
                                <span class="badge <?= $badgeClass ?> text-uppercase"><?= $label ?></span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle rounded-pill" type="button" data-bs-toggle="dropdown">
                                        Proses
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow small">
                                        <li>
                                            <a class="dropdown-item" href="<?= App::baseUrl('/pendaftaran/tiket/' . $r['registration_code']) ?>" target="_blank">
                                                <i class="bi bi-printer me-2"></i> Cetak Tiket
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="<?= App::baseUrl('/admin/pendaftaran/status') ?>" method="POST">
                                                <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                <input type="hidden" name="status" value="Confirmed">
                                                <button type="submit" class="dropdown-item text-primary fw-semibold"><i class="bi bi-megaphone me-2"></i>Konfirmasi & Panggil</button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="<?= App::baseUrl('/admin/pendaftaran/status') ?>" method="POST">
                                                <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                <input type="hidden" name="status" value="Completed">
                                                <button type="submit" class="dropdown-item text-success fw-semibold"><i class="bi bi-check-circle me-2"></i>Selesai / Pulang</button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="<?= App::baseUrl('/admin/pendaftaran/status') ?>" method="POST" onsubmit="return confirm('Batalkan antrean ini?');">
                                                <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
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
            <i class="bi bi-search fs-1 d-block mb-2 text-muted"></i>
            <p class="small mb-0">Tidak ada data pendaftaran yang sesuai dengan filter yang dipilih.</p>
        </div>
    <?php endif; ?>
</div>
