<?php
use App\Config\App;
use App\Core\Session;
?>

<div class="card border-0 shadow-sm rounded-4 bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="bi bi-tags-fill text-info me-2"></i>Kelola Kategori Edukasi</h4>
            <p class="text-muted small mb-0">Kelola topik kategori untuk artikel dan kuis kesehatan</p>
        </div>
        <button class="btn btn-primary rounded-pill btn-sm px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddCategory">
            <i class="bi bi-plus-circle me-1"></i> Tambah Kategori
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th width="5%">No</th>
                    <th width="20%">Nama Kategori</th>
                    <th width="20%">Slug</th>
                    <th width="45%">Deskripsi</th>
                    <th width="10%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr><td colspan="5" class="text-center py-3 text-muted">Belum ada data kategori.</td></tr>
                <?php else: ?>
                    <?php foreach ($categories as $index => $cat): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><strong class="text-dark"><?= htmlspecialchars($cat['name']) ?></strong></td>
                            <td><span class="font-monospace text-muted"><?= htmlspecialchars($cat['slug']) ?></span></td>
                            <td><?= htmlspecialchars($cat['description']) ?></td>
                            <td>
                                <form action="<?= App::baseUrl('/admin/kategori/hapus/' . $cat['id']) ?>" method="POST" onsubmit="return confirm('Hapus kategori ini? Data yang terkait mungkin akan kehilangan kategori.')" class="d-inline">
                                    <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                                    <button class="btn btn-sm btn-outline-danger rounded-pill"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Add Category -->
<div class="modal fade" id="modalAddCategory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">Tambah Kategori Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= App::baseUrl('/admin/kategori/simpan') ?>" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Kategori</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Stunting" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Penjelasan singkat mengenai kategori ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill fw-bold">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>
