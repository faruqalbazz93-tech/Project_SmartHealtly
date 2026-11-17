<?php
use App\Config\App;
?>

<div class="bg-light py-4 border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= App::baseUrl('/') ?>" class="text-decoration-none">Beranda</a></li>
                <li class="breadcrumb-item"><a href="<?= App::baseUrl('/artikel') ?>" class="text-decoration-none">Edukasi</a></li>
                <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width: 300px;"><?= htmlspecialchars($article['title']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">
        <!-- Article Body -->
        <div class="col-lg-8">
            <article class="bg-white p-4 p-lg-5 rounded-4 shadow-sm">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-semibold">
                        <?= htmlspecialchars($article['category_name']) ?>
                    </span>
                    <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i> <?= date('d F Y', strtotime($article['created_at'])) ?></span>
                    <span class="text-muted small"><i class="bi bi-eye me-1"></i> <?= $article['views_count'] ?> pembaca</span>
                </div>

                <h1 class="fw-bold text-dark mb-4 lh-base"><?= htmlspecialchars($article['title']) ?></h1>

                <div class="p-3 bg-light rounded-3 border-start border-4 border-success mb-4 text-secondary fst-italic">
                    <?= htmlspecialchars($article['summary']) ?>
                </div>

                <div class="article-content lh-lg text-dark">
                    <?= $article['content'] ?>
                </div>

                <hr class="my-5">

                <!-- Author & Puskesmas Note -->
                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-hospital fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Tim Promosi Kesehatan (Promkes) Puskesmas Astanajapura</h6>
                        <small class="text-muted">Dinas Kesehatan Kabupaten Cirebon</small>
                    </div>
                </div>
            </article>
        </div>

        <!-- Sidebar / Related Articles -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-journal-text text-success me-2"></i>Artikel Terkait</h5>
                <div class="list-group list-group-flush">
                    <?php foreach ($relatedArticles as $rel): ?>
                        <?php if ($rel['id'] != $article['id']): ?>
                            <a href="<?= App::baseUrl('/artikel/' . $rel['slug']) ?>" class="list-group-item list-group-item-action px-0 py-3 border-bottom">
                                <h6 class="fw-bold text-dark mb-1 small"><?= htmlspecialchars($rel['title']) ?></h6>
                                <small class="text-muted"><i class="bi bi-calendar3 me-1"></i> <?= date('d M Y', strtotime($rel['created_at'])) ?></small>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Quiz Promo Box -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-primary text-white">
                <div class="text-warning mb-2 fs-3"><i class="bi bi-award-fill"></i></div>
                <h5 class="fw-bold text-white mb-2">Uji Pengetahuan Kesehatan Anda</h5>
                <p class="small text-white-75 mb-3">Ikuti kuis interaktif seputar pola hidup sehat dan pencegahan penyakit di Puskesmas Astanajapura.</p>
                <a href="<?= App::baseUrl('/kuis') ?>" class="btn btn-warning rounded-pill fw-bold btn-sm">
                    Mulai Kuis Interaktif &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
