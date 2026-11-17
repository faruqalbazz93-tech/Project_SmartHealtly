<?php
use App\Config\App;
?>

<div class="card border-0 shadow-sm rounded-4 bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Data Desa Wilayah Puskesmas Astanajapura</h4>
            <p class="text-muted small mb-0">Pemetaan demografi pasien terdaftar dan frekuensi kunjungan per desa di Kecamatan Astanajapura</p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>No.</th>
                    <th>Nama Desa</th>
                    <th>Kecamatan</th>
                    <th>Kodepos</th>
                    <th>Pasien Terdaftar</th>
                    <th>Total Pendaftaran Antrean</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($villages as $index => $v): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td>
                            <strong class="text-dark"><i class="bi bi-pin-map text-danger me-1"></i> Desa <?= htmlspecialchars($v['name']) ?></strong>
                        </td>
                        <td><?= htmlspecialchars($v['district']) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= $v['postal_code'] ?></span></td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary fs-6"><?= $v['total_residents'] ?? 0 ?> Pasien</span>
                        </td>
                        <td>
                            <span class="badge bg-success-subtle text-success fs-6"><?= $v['total_registrations'] ?? 0 ?> Kunjungan</span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
