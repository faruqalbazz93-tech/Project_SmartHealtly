<?php
use App\Config\App;
use App\Core\Session;
?>

<div class="card border-0 shadow-sm rounded-4 bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="bi bi-hospital text-success me-2"></i>Kelola Poli & Layanan Puskesmas</h4>
            <p class="text-muted small mb-0">Daftar unit layanan medis dan kuota pasien di Puskesmas Astanajapura</p>
        </div>
        <button class="btn btn-primary rounded-pill btn-sm px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddService">
            <i class="bi bi-plus-circle me-1"></i> Tambah Layanan
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Icon</th>
                    <th>Kode</th>
                    <th>Nama Poli</th>
                    <th>Dokter / Nakes</th>
                    <th>Kuota / Hari</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $srv): ?>
                    <tr>
                        <td>
                            <div class="bg-primary-subtle text-primary p-2 rounded-3 text-center fs-5" style="width: 40px;">
                                <i class="bi <?= $srv['icon'] ?? 'bi-hospital' ?>"></i>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($srv['code']) ?></span></td>
                        <td>
                            <strong class="text-dark d-block"><?= htmlspecialchars($srv['name']) ?></strong>
                            <small class="text-muted text-truncate d-inline-block" style="max-width: 250px;"><?= htmlspecialchars($srv['description'] ?? '') ?></small>
                        </td>
                        <td><i class="bi bi-person-badge text-primary me-1"></i> <?= htmlspecialchars($srv['doctor_name'] ?? '-') ?></td>
                        <td><span class="badge bg-secondary-subtle text-dark"><?= $srv['quota_daily'] ?> Pasien</span></td>
                        <td>
                            <span class="badge <?= ($srv['status'] === 'active') ? 'bg-success' : 'bg-secondary' ?> text-uppercase">
                                <?= $srv['status'] ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#modalEditService<?= $srv['id'] ?>">
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="modalEditService<?= $srv['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content rounded-4 border-0">
                                <div class="modal-header border-bottom">
                                    <h5 class="modal-title fw-bold">Edit Poli: <?= htmlspecialchars($srv['name']) ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="<?= App::baseUrl('/admin/layanan/simpan') ?>" method="POST">
                                    <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                                    <input type="hidden" name="id" value="<?= $srv['id'] ?>">
                                    <div class="modal-body p-4">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Kode Layanan</label>
                                            <input type="text" name="code" class="form-control" value="<?= htmlspecialchars($srv['code']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Nama Poli</label>
                                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($srv['name']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Nama Dokter / Penanggung Jawab</label>
                                            <input type="text" name="doctor_name" class="form-control" value="<?= htmlspecialchars($srv['doctor_name'] ?? '') ?>" required>
                                        </div>
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold">Kuota Harian</label>
                                                <input type="number" name="quota_daily" class="form-control" value="<?= $srv['quota_daily'] ?>" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold">Icon Bootstrap</label>
                                                <input type="text" name="icon" class="form-control" value="<?= htmlspecialchars($srv['icon'] ?? 'bi-hospital') ?>" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="active" <?= ($srv['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                                                <option value="inactive" <?= ($srv['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Deskripsi Layanan</label>
                                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($srv['description'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top">
                                        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary rounded-pill fw-bold">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Add Service -->
<div class="modal fade" id="modalAddService" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">Tambah Layanan Poli Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= App::baseUrl('/admin/layanan/simpan') ?>" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kode Layanan</label>
                        <input type="text" name="code" class="form-control" placeholder="POLI-CONTOH" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Poli</label>
                        <input type="text" name="name" class="form-control" placeholder="Poli ..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Dokter / Penanggung Jawab</label>
                        <input type="text" name="doctor_name" class="form-control" placeholder="dr. ..." required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Kuota Harian</label>
                            <input type="number" name="quota_daily" class="form-control" value="50" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Icon Bootstrap</label>
                            <input type="text" name="icon" class="form-control" value="bi-heart-pulse" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Deskripsi Layanan</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Penjelasan singkat mengenai layanan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill fw-bold">Tambah Poli</button>
                </div>
            </form>
        </div>
    </div>
</div>
