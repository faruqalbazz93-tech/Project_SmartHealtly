<?php
use App\Config\App;
?>

<div class="card border-0 shadow-sm rounded-4 bg-primary text-white p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 small mb-2">Puskesmas Astanajapura</span>
            <h3 class="fw-bold mb-1 text-white">Jadwal Praktik Dokter & Kuota Pelayanan</h3>
            <p class="text-white-75 mb-0 small">Daftar hari operasional dan alokasi kuota pasien per poli</p>
        </div>
        <a href="<?= App::baseUrl('/pendaftaran/baru') ?>" class="btn btn-warning rounded-pill px-4 btn-sm fw-bold shadow">
            <i class="bi bi-calendar-plus-fill me-1"></i> Daftar Antrean
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 bg-white p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Layanan Poli</th>
                    <th>Dokter / Petugas Medis</th>
                    <th>Hari Pelayanan</th>
                    <th>Jam Pelayanan</th>
                    <th>Alokasi Kuota</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedules as $sch): ?>
                    <tr>
                        <td>
                            <strong class="text-dark"><i class="bi <?= $sch['icon'] ?? 'bi-hospital' ?> text-primary me-2"></i><?= htmlspecialchars($sch['service_name']) ?></strong>
                        </td>
                        <td>
                            <i class="bi bi-person-badge text-muted me-1"></i> <?= htmlspecialchars($sch['doctor_name'] ?? 'Dokter Jaga') ?>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1 rounded-pill">
                                <?= htmlspecialchars($sch['day_of_week']) ?>
                            </span>
                        </td>
                        <td>
                            <?= substr($sch['start_time'], 0, 5) ?> - <?= substr($sch['end_time'], 0, 5) ?> WIB
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-dark"><?= $sch['max_quota'] ?> Pasien</span>
                        </td>
                        <td>
                            <a href="<?= App::baseUrl('/pendaftaran/baru?poli=' . $sch['health_service_id']) ?>" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold">
                                Daftar Poli
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
