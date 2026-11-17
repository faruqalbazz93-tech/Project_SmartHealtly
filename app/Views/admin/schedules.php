<?php
use App\Config\App;
use App\Core\Session;
?>

<div class="card border-0 shadow-sm rounded-4 bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="bi bi-calendar-week text-info me-2"></i>Jadwal Dokter & Jam Pelayanan Poli</h4>
            <p class="text-muted small mb-0">Atur hari operasional, jam mulai/selesai, serta kapasitas kuota harian dokter</p>
        </div>
        <button class="btn btn-primary rounded-pill btn-sm px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddSchedule">
            <i class="bi bi-plus-circle me-1"></i> Tambah Jadwal
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Layanan Poli</th>
                    <th>Dokter / Petugas</th>
                    <th>Hari Pelayanan</th>
                    <th>Jam Operasional</th>
                    <th>Maks Kuota</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedules as $sch): ?>
                    <tr>
                        <td>
                            <strong class="text-dark"><i class="bi <?= $sch['icon'] ?? 'bi-hospital' ?> text-primary me-1"></i> <?= htmlspecialchars($sch['service_name']) ?></strong>
                        </td>
                        <td><?= htmlspecialchars($sch['doctor_name'] ?? '-') ?></td>
                        <td><span class="badge bg-primary-subtle text-primary fw-bold"><?= htmlspecialchars($sch['day_of_week']) ?></span></td>
                        <td><?= substr($sch['start_time'], 0, 5) ?> - <?= substr($sch['end_time'], 0, 5) ?> WIB</td>
                        <td><span class="badge bg-light text-dark border"><?= $sch['max_quota'] ?> Orang</span></td>
                        <td>
                            <form action="<?= App::baseUrl('/admin/jadwal/hapus/' . $sch['id']) ?>" method="POST" onsubmit="return confirm('Hapus jadwal ini?');" class="d-inline">
                                <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Add Schedule -->
<div class="modal fade" id="modalAddSchedule" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">Tambah Jadwal Poli & Dokter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= App::baseUrl('/admin/jadwal/simpan') ?>" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih Layanan Poli</label>
                        <select name="health_service_id" class="form-select" required>
                            <?php foreach ($services as $srv): ?>
                                <option value="<?= $srv['id'] ?>"><?= htmlspecialchars($srv['name']) ?> (<?= htmlspecialchars($srv['doctor_name']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Hari Pelayanan</label>
                        <select name="day_of_week" class="form-select" required>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Jam Mulai</label>
                            <input type="time" name="start_time" value="08:00" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Jam Selesai</label>
                            <input type="time" name="end_time" value="12:00" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Batas Kuota Pasien</label>
                        <input type="number" name="max_quota" value="50" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill fw-bold">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>
