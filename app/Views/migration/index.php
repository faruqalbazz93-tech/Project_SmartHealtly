<?php
use App\Config\App;
use App\Core\Session;
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary text-white p-3 rounded-4 fs-3">
                            <i class="bi bi-database-check"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold text-dark mb-0">Status & Inisialisasi Database</h3>
                            <small class="text-muted">SmartHealthy Puskesmas Astanajapura</small>
                        </div>
                    </div>
                    <div>
                        <?php if ($dbStatus): ?>
                            <span class="badge bg-success-subtle text-success fs-6 px-3 py-2 rounded-pill border border-success-subtle">
                                <i class="bi bi-check-circle-fill me-1"></i> Database Terhubung
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger fs-6 px-3 py-2 rounded-pill border border-danger-subtle">
                                <i class="bi bi-x-circle-fill me-1"></i> Belum Terhubung
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 rounded-3 mb-4">
                        <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Pesan Koneksi Database:</h6>
                        <p class="small mb-0"><?= htmlspecialchars($error) ?></p>
                        <hr class="my-2">
                        <small class="text-muted d-block">
                            Pastikan service <strong>MySQL</strong> sudah berstatus <em>Running</em> pada panel XAMPP Control Panel.
                        </small>
                    </div>
                <?php endif; ?>

                <!-- 1-Click Action Buttons -->
                <div class="p-4 bg-light rounded-4 mb-4 border">
                    <h5 class="fw-bold text-dark mb-2">Jalankan Migrasi & Seeder (1-Click)</h5>
                    <p class="small text-muted mb-3">
                        Gunakan tombol di bawah ini untuk membuat seluruh 12 tabel skema relasional dan memasukkan data awal desa Astanajapura, akun admin, akun pasien, layanan poli, serta bank soal.
                    </p>

                    <div class="d-flex flex-wrap gap-2">
                        <form action="<?= App::baseUrl('/migrate/run') ?>" method="POST" class="d-inline">
                            <input type="hidden" name="action" value="all">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                <i class="bi bi-play-circle-fill me-1"></i> Jalankan Skema + Seeder Lengkap
                            </button>
                        </form>

                        <form action="<?= App::baseUrl('/migrate/run') ?>" method="POST" class="d-inline">
                            <input type="hidden" name="action" value="migrate">
                            <button type="submit" class="btn btn-outline-primary rounded-pill px-3">
                                <i class="bi bi-arrow-repeat me-1"></i> Reset Skema Tabel Saja
                            </button>
                        </form>

                        <form action="<?= App::baseUrl('/migrate/run') ?>" method="POST" class="d-inline">
                            <input type="hidden" name="action" value="seed">
                            <button type="submit" class="btn btn-outline-success rounded-pill px-3">
                                <i class="bi bi-database-fill-down me-1"></i> Isi Ulang Data Seeder
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Database Status & Table List -->
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-table text-primary me-2"></i>Daftar Tabel Terdeteksi (<?= count($tables) ?> Tabel)</h5>
                
                <?php if (!empty($tables)): ?>
                    <div class="row g-2 mb-4">
                        <?php foreach ($tables as $t): ?>
                            <div class="col-md-4 col-sm-6">
                                <div class="p-2 bg-light border rounded-3 small d-flex align-items-center justify-content-between">
                                    <span><i class="bi bi-file-earmark-spreadsheet text-primary me-2"></i><?= htmlspecialchars($t) ?></span>
                                    <span class="badge bg-success-subtle text-success">OK</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (!empty($stats)): ?>
                        <h6 class="fw-bold text-dark mb-2">Ringkasan Data Awal:</h6>
                        <div class="row g-2 small">
                            <div class="col-md-3 col-6"><div class="p-2 bg-light border rounded-3">Desa Astanajapura: <strong><?= $stats['villages'] ?></strong></div></div>
                            <div class="col-md-3 col-6"><div class="p-2 bg-light border rounded-3">Pengguna Terdaftar: <strong><?= $stats['users'] ?></strong></div></div>
                            <div class="col-md-3 col-6"><div class="p-2 bg-light border rounded-3">Layanan Poli: <strong><?= $stats['health_services'] ?></strong></div></div>
                            <div class="col-md-3 col-6"><div class="p-2 bg-light border rounded-3">Soal Kuis PHBS: <strong><?= $stats['questions'] ?></strong></div></div>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="alert alert-warning border-0 rounded-3 small">
                        <i class="bi bi-info-circle me-1"></i> Belum ada tabel dalam database `smarthealthy`. Silakan klik tombol <strong>"Jalankan Skema + Seeder Lengkap"</strong> di atas.
                    </div>
                <?php endif; ?>

                <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                    <a href="<?= App::baseUrl('/') ?>" class="btn btn-outline-secondary rounded-pill px-4 btn-sm">
                        <i class="bi bi-house me-1"></i> Ke Beranda
                    </a>
                    <a href="<?= App::baseUrl('/login') ?>" class="btn btn-primary rounded-pill px-4 btn-sm fw-bold">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Halaman Masuk
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
