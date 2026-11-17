<?php
use App\Config\App;
use App\Core\Session;
?>

<div class="card border-0 shadow-sm rounded-4 bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="bi bi-clock-history text-primary me-2"></i>Riwayat Pendaftaran Antrean</h4>
            <p class="text-muted small mb-0">Daftar semua permohonan antrean dan pemeriksaan Anda di Puskesmas Astanajapura</p>
        </div>
        <a href="<?= App::baseUrl('/pendaftaran/baru') ?>" class="btn btn-primary rounded-pill btn-sm px-3 fw-bold">
            <i class="bi bi-plus-circle me-1"></i> Daftar Baru
        </a>
    </div>

    <?php if (!empty($registrations)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode Tiket</th>
                        <th>Tanggal Kunjungan</th>
                        <th>Layanan Poli</th>
                        <th>No. Antrean</th>
                        <th>Keluhan</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrations as $r): ?>
                        <tr>
                            <td>
                                <strong class="text-primary"><?= htmlspecialchars($r['registration_code']) ?></strong>
                            </td>
                            <td><?= date('d M Y', strtotime($r['registration_date'])) ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi <?= $r['service_icon'] ?? 'bi-hospital' ?> text-primary"></i>
                                    <span><?= htmlspecialchars($r['service_name']) ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary fs-6 px-3 py-1 rounded-pill">#<?= $r['queue_number'] ?></span>
                            </td>
                            <td class="text-truncate" style="max-width: 180px;" title="<?= htmlspecialchars($r['complaint']) ?>">
                                <?= htmlspecialchars($r['complaint']) ?>
                            </td>
                            <td>
                                <?php
                                $badgeClass = match($r['status']) {
                                    'selesai'   => 'bg-success',
                                    'dipanggil' => 'bg-warning text-dark',
                                    'diperiksa' => 'bg-info text-dark',
                                    'dibatalkan'=> 'bg-danger',
                                    default     => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?> text-uppercase"><?= $r['status'] ?></span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="<?= App::baseUrl('/pendaftaran/tiket/' . $r['registration_code']) ?>" class="btn btn-sm btn-outline-primary rounded-pill" title="Lihat E-Ticket">
                                        <i class="bi bi-qr-code"></i> E-Ticket
                                    </a>
                                    <?php if ($r['status'] === 'menunggu'): ?>
                                        <form action="<?= App::baseUrl('/pendaftaran/batal/' . $r['id']) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pendaftaran ini?');" class="d-inline">
                                            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill" title="Batalkan">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-calendar-x fs-1 d-block mb-3 text-muted"></i>
            <h5 class="text-dark fw-bold">Belum Ada Riwayat Pendaftaran</h5>
            <p class="text-muted small mb-3">Anda belum pernah melakukan pendaftaran pemeriksaan di Puskesmas Astanajapura.</p>
            <a href="<?= App::baseUrl('/pendaftaran/baru') ?>" class="btn btn-primary rounded-pill px-4">Daftar Antrean Sekarang</a>
        </div>
    <?php endif; ?>
</div>
