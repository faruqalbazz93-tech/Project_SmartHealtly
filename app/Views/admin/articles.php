<?php
use App\Config\App;
use App\Core\Session;
?>

<div class="card border-0 shadow-sm rounded-4 bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="bi bi-journal-text text-secondary me-2"></i>Kelola Artikel Edukasi Kesehatan</h4>
            <p class="text-muted small mb-0">Publikasi materi promosi kesehatan (Promkes) untuk masyarakat desa</p>
        </div>
        <button class="btn btn-primary rounded-pill btn-sm px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddArticle">
            <i class="bi bi-plus-circle me-1"></i> Tulis Artikel Baru
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Judul Artikel</th>
                    <th>Kategori</th>
                    <th>Tanggal Rilis</th>
                    <th>Dibaca</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $art): ?>
                    <tr>
                        <td>
                            <strong class="text-dark d-block"><?= htmlspecialchars($art['title']) ?></strong>
                            <small class="text-muted text-truncate d-inline-block" style="max-width: 300px;"><?= htmlspecialchars($art['summary']) ?></small>
                        </td>
                        <td><span class="badge bg-light text-primary border"><?= htmlspecialchars($art['category_name']) ?></span></td>
                        <td><?= date('d/m/Y', strtotime($art['created_at'])) ?></td>
                        <td><i class="bi bi-eye text-muted me-1"></i> <?= $art['views_count'] ?></td>
                        <td>
                            <span class="badge <?= ($art['status'] === 'published') ? 'bg-success' : 'bg-secondary' ?> text-uppercase">
                                <?= $art['status'] ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= App::baseUrl('/artikel/' . $art['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-info rounded-pill">
                                <i class="bi bi-box-arrow-up-right"></i> Lihat
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Add Article -->
<div class="modal fade" id="modalAddArticle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">Tulis Artikel Edukasi Kesehatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= App::baseUrl('/admin/artikel/simpan') ?>" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Judul Artikel</label>
                        <input type="text" name="title" class="form-control" placeholder="Contoh: Pentingnya Vaksinasi Balita di Posyandu" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Kategori Edukasi</label>
                            <select name="category_id" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Status Publikasi</label>
                            <select name="status" class="form-select">
                                <option value="published">Langsung Publikasikan (Published)</option>
                                <option value="draft">Simpan Draf (Draft)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Ringkasan Singkat (Summary)</label>
                        <textarea name="summary" class="form-control" rows="2" placeholder="Ringkasan 1-2 kalimat untuk preview..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Isi Artikel Lengkap (Mendukung HTML)</label>
                        <textarea name="content" class="form-control font-monospace" rows="8" placeholder="<p>Tuliskan isi artikel edukasi di sini...</p>" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill fw-bold">Publikasikan Artikel</button>
                </div>
            </form>
        </div>
    </div>
</div>
