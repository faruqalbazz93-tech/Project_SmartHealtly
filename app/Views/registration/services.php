<?php
use App\Config\App;
use App\Core\Auth;
?>

<div class="card border-0 shadow-sm rounded-4 bg-primary text-white p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 small mb-2">Pelayanan Puskesmas Astanajapura</span>
            <h3 class="fw-bold mb-1 text-white">Daftar Layanan Poli Kesehatan</h3>
            <p class="text-white-75 mb-0 small">Pelayanan kesehatan prima, dokter spesialis & umum bersertifikat, 100% gratis standar JKN/BPJS</p>
        </div>
        <div>
            <a href="<?= App::baseUrl('/jadwal') ?>" class="btn btn-outline-light rounded-pill btn-sm px-3">
                <i class="bi bi-calendar-week me-1"></i> Lihat Jadwal Dokter
            </a>
            <a href="<?= App::baseUrl('/pendaftaran/baru') ?>" class="btn btn-warning rounded-pill btn-sm px-4 fw-bold shadow">
                <i class="bi bi-calendar-plus-fill me-1"></i> Daftar Sekarang
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <?php foreach ($services as $srv): ?>
        <?php $schedules = $schedulesByService[$srv['id']] ?? []; ?>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-4 bg-white service-card">
                <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                    <div class="bg-primary-subtle text-primary p-3 rounded-4 fs-3">
                        <i class="bi <?= $srv['icon'] ?? 'bi-hospital' ?>"></i>
                    </div>
                    <div>
                        <span class="badge bg-light text-primary border mb-1"><?= htmlspecialchars($srv['code']) ?></span>
                        <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars($srv['name']) ?></h5>
                    </div>
                </div>

                <p class="text-muted small mb-3">
                    <?= htmlspecialchars($srv['description'] ?? 'Layanan kesehatan masyarakat Puskesmas.') ?>
                </p>

                <div class="bg-light p-3 rounded-3 mb-3">
                    <div class="row g-2 small">
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Dokter / Nakes:</span>
                            <strong class="text-dark"><i class="bi bi-person-badge text-primary me-1"></i> <?= htmlspecialchars($srv['doctor_name'] ?? 'Dokter Jaga') ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Maksimal Kuota:</span>
                            <strong class="text-dark"><i class="bi bi-people text-success me-1"></i> <?= $srv['quota_daily'] ?> Pasien / Hari</strong>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold text-dark small text-uppercase tracking-wider mb-2">Jadwal Praktik:</h6>
                <?php if (!empty($schedules)): ?>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered align-middle small mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Hari</th>
                                    <th>Jam Pelayanan</th>
                                    <th>Kuota</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedules as $sch): ?>
                                    <tr>
                                        <td class="fw-semibold text-primary"><?= htmlspecialchars($sch['day_of_week']) ?></td>
                                        <td><?= substr($sch['start_time'], 0, 5) ?> - <?= substr($sch['end_time'], 0, 5) ?> WIB</td>
                                        <td><span class="badge bg-secondary-subtle text-dark"><?= $sch['max_quota'] ?> Orang</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted small fst-italic mb-3">Jadwal buka: Senin s/d Sabtu (08:00 - 12:00 WIB).</p>
                <?php endif; ?>

                <div class="mt-auto pt-2">
                    <a href="<?= App::baseUrl('/pendaftaran/baru?poli=' . $srv['id']) ?>" class="btn btn-primary rounded-pill w-100 fw-semibold">
                        <i class="bi bi-calendar-plus me-1"></i> Ambil Antrean Poli Ini
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
