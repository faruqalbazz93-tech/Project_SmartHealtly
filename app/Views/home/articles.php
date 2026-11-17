<?php
use App\Config\App;
?>

<div class="bg-success text-white py-5">
    <div class="container py-3">
        <h1 class="fw-bold mb-2">Edukasi & Artikel Kesehatan</h1>
        <p class="lead text-white-75 mb-0">Tingkatkan wawasan kesehatan keluarga dengan tips pencegahan penyakit, pola hidup bersih dan gizi seimbang dari Puskesmas Astanajapura.</p>
    </div>
</div>

<div class="container py-5">
    <!-- Category Filter Pills -->
    <div class="d-flex flex-wrap gap-2 mb-4 pb-2 border-bottom">
        <a href="<?= App::baseUrl('/artikel') ?>" class="btn btn-sm rounded-pill px-3 <?= empty($activeCategoryId) ? 'btn-success' : 'btn-outline-secondary' ?>">
            Semua Kategori
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="<?= App::baseUrl('/artikel?kategori=' . $cat['id']) ?>" class="btn btn-sm rounded-pill px-3 <?= ($activeCategoryId == $cat['id']) ? 'btn-success' : 'btn-outline-secondary' ?>">
                <?= htmlspecialchars($cat['name']) ?> (<?= $cat['total_articles'] ?>)
            </a>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">
        <?php if (!empty($articles)): ?>
            <?php foreach ($articles as $art): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden article-card bg-white">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-success-subtle text-success rounded-pill small">
                                    <?= htmlspecialchars($art['category_name']) ?>
                                </span>
                                <small class="text-muted"><i class="bi bi-calendar3 me-1"></i> <?= date('d M Y', strtotime($art['created_at'])) ?></small>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">
                                <a href="<?= App::baseUrl('/artikel/' . $art['slug']) ?>" class="text-dark text-decoration-none hover-success">
                                    <?= htmlspecialchars($art['title']) ?>
                                </a>
                            </h5>
                            <p class="text-muted small mb-4 flex-grow-1">
                                <?= htmlspecialchars($art['summary']) ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                                <span class="small text-muted"><i class="bi bi-eye me-1"></i> <?= $art['views_count'] ?> views</span>
                                <a href="<?= App::baseUrl('/artikel/' . $art['slug']) ?>" class="btn btn-sm btn-link text-success fw-bold text-decoration-none p-0">
                                    Baca Selengkapnya &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-journal-x text-muted display-4 mb-3 d-block"></i>
                <h5 class="text-dark fw-bold">Belum ada artikel pada kategori ini</h5>
                <p class="text-muted small">Silakan pilih kategori lain atau kembali ke semua artikel.</p>
                <a href="<?= App::baseUrl('/artikel') ?>" class="btn btn-outline-success rounded-pill px-4">Semua Artikel</a>
            </div>
        <?php endif; ?>
    </div>
</div>
