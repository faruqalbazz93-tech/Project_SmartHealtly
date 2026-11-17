<?php
use App\Config\App;
?>

<div class="bg-primary text-white py-5">
    <div class="container py-3">
        <h1 class="fw-bold mb-2">Layanan Poli & Jadwal Dokter</h1>
        <p class="lead text-white-75 mb-0">Informasi lengkap jadwal pelayanan kesehatan, dokter penanggung jawab, dan kuota harian di Puskesmas Astanajapura.</p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <?php foreach ($services as $service): ?>
            <?php $serviceSchedules = $schedulesByService[$service['id']] ?? []; ?>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-4 bg-white">
                    <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                        <div class="bg-primary-subtle text-primary p-3 rounded-4 fs-3">
                            <i class="bi <?= $service['icon'] ?? 'bi-hospital' ?>"></i>
                        </div>
                        <div>
                            <span class="badge bg-light text-primary border mb-1"><?= htmlspecialchars($service['code']) ?></span>
                            <h4 class="fw-bold text-dark mb-0"><?= htmlspecialchars($service['name']) ?></h4>
                        </div>
                    </div>

                    <p class="text-muted small mb-3">
                        <?= htmlspecialchars($service['description'] ?? 'Layanan pemeriksaan kesehatan Puskesmas.') ?>
                    </p>

                    <div class="bg-light p-3 rounded-3 mb-3">
                        <div class="row g-2 small">
                            <div class="col-6">
                                <span class="text-muted d-block">Dokter / Nakes:</span>
                                <strong class="text-dark"><i class="bi bi-person-badge text-primary me-1"></i> <?= htmlspecialchars($service['doctor_name'] ?? 'Dokter Jaga') ?></strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block">Maksimal Kuota:</span>
                                <strong class="text-dark"><i class="bi bi-people text-success me-1"></i> <?= $service['quota_daily'] ?> Pasien/Hari</strong>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark small text-uppercase tracking-wider mb-2">Jadwal Operasional:</h6>
                    <?php if (!empty($serviceSchedules)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle small mb-3">
                                <thead class="table-light">
                                    <tr>
                                        <th>Hari</th>
                                        <th>Jam Pelayanan</th>
                                        <th>Kuota</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($serviceSchedules as $sch): ?>
                                        <tr>
                                            <td class="fw-semibold text-primary"><?= htmlspecialchars($sch['day_of_week']) ?></td>
                                            <td><?= substr($sch['start_time'], 0, 5) ?> - <?= substr($sch['end_time'], 0, 5) ?> WIB</td>
                                            <td><span class="badge bg-secondary-subtle text-dark"><?= $sch['max_quota'] ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small fst-italic mb-3">Jadwal reguler: Senin s/d Sabtu (08:00 - 12:00 WIB).</p>
                    <?php endif; ?>

                    <div class="mt-auto pt-2">
                        <a href="<?= App::baseUrl('/pendaftaran/baru?poli=' . $service['id']) ?>" class="btn btn-primary rounded-pill w-100 fw-semibold">
                            <i class="bi bi-calendar-plus me-1"></i> Daftar Antrean di Poli Ini
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
