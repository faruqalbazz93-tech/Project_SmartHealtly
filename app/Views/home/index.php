<?php
use App\Config\App;
use App\Core\Auth;
?>

<!-- Hero Section -->
<section class="hero-section text-white py-5 position-relative overflow-hidden">
    <div class="container py-lg-4 position-relative z-2">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white bg-opacity-15 border border-white border-opacity-25 text-white small mb-3 backdrop-blur">
                    <i class="bi bi-geo-alt-fill text-warning"></i>
                    <span>Pelayanan Resmi Wilayah Puskesmas Astanajapura, Cirebon</span>
                </div>
                <h1 class="display-4 fw-extrabold mb-3 lh-sm">
                    Pendaftaran Layanan Kesehatan Puskesmas <span class="text-warning text-gradient">Cepat, Mudah & Gratis</span>
                </h1>
                <p class="lead mb-4 text-white-75 fs-6 fs-md-5">
                    Dapatkan nomor antrean online tanpa perlu menunggu lama di loket. Akses layanan poli umum, KIA, lansia, gigi, dan edukasi kesehatan keluarga dari rumah.
                </p>

                <div class="d-flex flex-wrap gap-3 mb-4">
                    <?php if (Auth::check()): ?>
                        <a href="<?= App::baseUrl('/pendaftaran/baru') ?>" class="btn btn-warning btn-lg px-4 py-3 rounded-pill fw-bold shadow-lg d-inline-flex align-items-center gap-2">
                            <i class="bi bi-calendar-check-fill"></i>
                            <span>Daftar Layanan Sekarang</span>
                        </a>
                        <a href="<?= App::baseUrl('/dashboard') ?>" class="btn btn-outline-light btn-lg px-4 py-3 rounded-pill fw-semibold">
                            <i class="bi bi-grid-fill me-1"></i> Dashboard Saya
                        </a>
                    <?php else: ?>
                        <a href="<?= App::baseUrl('/register') ?>" class="btn btn-warning btn-lg px-4 py-3 rounded-pill fw-bold shadow-lg d-inline-flex align-items-center gap-2">
                            <i class="bi bi-person-plus-fill"></i>
                            <span>Daftar Pasien Baru</span>
                        </a>
                        <a href="<?= App::baseUrl('/login') ?>" class="btn btn-outline-light btn-lg px-4 py-3 rounded-pill fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Akun
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Stats Badges -->
                <div class="row g-3 pt-3 border-top border-white border-opacity-15">
                    <div class="col-4">
                        <div class="fw-bold fs-4 text-warning"><?= $stats['total_services'] ?> Poli</div>
                        <div class="small text-white-50">Layanan Aktif</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-4 text-warning"><?= $stats['total_villages'] ?> Desa</div>
                        <div class="small text-white-50">Wilayah Binaan</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-4 text-warning">100%</div>
                        <div class="small text-white-50">Gratis (BPJS/Umum)</div>
                    </div>
                </div>
            </div>

            <!-- Hero Live Queue Widget -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-2xl rounded-4 bg-white text-dark p-4 p-lg-4 glass-card">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary-subtle text-primary p-2 rounded-3">
                                <i class="bi bi-search fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">Cek Status Antrean</h6>
                                <small class="text-muted">Cek status panggilan Anda hari ini</small>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                            <span class="spinner-grow spinner-grow-sm text-success me-1" style="width: 8px; height: 8px;"></span> Live
                        </span>
                    </div>

                    <form action="<?= App::baseUrl('/cek-antrean') ?>" method="GET" class="mb-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Masukkan Kode Booking / NIK 16 Digit</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-upc-scan text-muted"></i></span>
                                <input type="text" name="q" class="form-control bg-light border-start-0 py-2" placeholder="Contoh: REG-AST... atau NIK" required>
                                <button class="btn btn-primary px-3" type="submit">
                                    <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <h6 class="fw-bold small text-uppercase text-muted tracking-wider mb-2">Pantauan Antrean Poli Hari Ini</h6>
                    <div class="list-group list-group-flush small" style="max-height: 200px; overflow-y: auto;">
                        <?php foreach (array_slice($services, 0, 4) as $srv): ?>
                            <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center border-bottom-dashed">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi <?= $srv['icon'] ?? 'bi-hospital' ?> text-primary"></i>
                                    <span class="fw-semibold text-truncate" style="max-width: 170px;"><?= htmlspecialchars($srv['name']) ?></span>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-primary rounded-pill">Panggilan: #<?= $srv['current_calling_number'] ?? '0' ?></span>
                                    <div class="text-muted" style="font-size: 0.75rem;">Total: <?= $srv['registered_today'] ?> pasien</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 4 Langkah Alur Pendaftaran -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase px-3 py-2 rounded-pill mb-2">Alur Pelayanan</span>
            <h2 class="fw-bold text-dark">4 Langkah Mudah Berobat di Puskesmas</h2>
            <p class="text-muted">Proses pendaftaran digital yang dirancang untuk menghemat waktu warga Astanajapura.</p>
        </div>

        <div class="row g-4 text-center">
            <div class="col-md-3 col-sm-6">
                <div class="p-4 rounded-4 bg-light h-100 step-card">
                    <div class="step-icon bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center fw-bold fs-4 shadow-sm" style="width: 60px; height: 60px;">
                        1
                    </div>
                    <h5 class="fw-bold mb-2">Daftar / Masuk</h5>
                    <p class="text-muted small mb-0">Buat akun pasien baru dengan NIK KTP Anda dan pilih asal Desa di wilayah Astanajapura.</p>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="p-4 rounded-4 bg-light h-100 step-card">
                    <div class="step-icon bg-info text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center fw-bold fs-4 shadow-sm" style="width: 60px; height: 60px;">
                        2
                    </div>
                    <h5 class="fw-bold mb-2">Pilih Poli & Tanggal</h5>
                    <p class="text-muted small mb-0">Pilih poli layanan kesehatan yang dituju serta tanggal rencana kunjungan pemeriksaan.</p>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="p-4 rounded-4 bg-light h-100 step-card">
                    <div class="step-icon bg-warning text-dark rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center fw-bold fs-4 shadow-sm" style="width: 60px; height: 60px;">
                        3
                    </div>
                    <h5 class="fw-bold mb-2">Dapatkan E-Ticket</h5>
                    <p class="text-muted small mb-0">Sistem menerbitkan nomor antrean resmi dan kode booking yang dapat disimpan di ponsel.</p>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="p-4 rounded-4 bg-light h-100 step-card">
                    <div class="step-icon bg-success text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center fw-bold fs-4 shadow-sm" style="width: 60px; height: 60px;">
                        4
                    </div>
                    <h5 class="fw-bold mb-2">Datang & Diperiksa</h5>
                    <p class="text-muted small mb-0">Tunjukkan tiket kepada petugas loket dan langsung menuju ruang poli untuk diperiksa dokter.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Layanan Poli Puskesmas -->
<section class="py-5 bg-light">
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
            <div>
                <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase px-3 py-2 rounded-pill mb-2">Poli & Layanan</span>
                <h2 class="fw-bold text-dark mb-1">Layanan Kesehatan Tersedia</h2>
                <p class="text-muted mb-0">Ditangani oleh dokter dan tenaga medis profesional Puskesmas Astanajapura.</p>
            </div>
            <a href="<?= App::baseUrl('/layanan') ?>" class="btn btn-outline-primary rounded-pill px-4 mt-3 mt-md-0 fw-semibold">
                Lihat Semua Jadwal <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($services as $service): ?>
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 service-card p-3">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="service-icon-box bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.5rem;">
                                <i class="bi <?= $service['icon'] ?? 'bi-hospital' ?>"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($service['name']) ?></h6>
                                <small class="text-muted d-block"><?= htmlspecialchars($service['code']) ?></small>
                            </div>
                        </div>
                        <p class="text-muted small mb-3 flex-grow-1">
                            <?= htmlspecialchars($service['description'] ?? 'Layanan pemeriksaan dan pengobatan kesehatan masyarakat.') ?>
                        </p>
                        <div class="border-top pt-2 mt-auto d-flex justify-content-between align-items-center small">
                            <span class="text-muted"><i class="bi bi-person-badge me-1"></i> <?= htmlspecialchars($service['doctor_name'] ?? 'Dokter Jaga') ?></span>
                            <span class="badge bg-light text-dark border">Kuota: <?= $service['quota_daily'] ?>/hari</span>
                        </div>
                        <a href="<?= App::baseUrl('/pendaftaran/baru?poli=' . $service['id']) ?>" class="btn btn-sm btn-primary mt-3 rounded-pill fw-semibold w-100">
                            <i class="bi bi-calendar-plus me-1"></i> Daftar Poli Ini
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Edukasi Kesehatan & Kuis Interaktif -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
            <div>
                <span class="badge bg-success-subtle text-success fw-bold text-uppercase px-3 py-2 rounded-pill mb-2">Edukasi Warga</span>
                <h2 class="fw-bold text-dark mb-1">Informasi & Tips Kesehatan Terbaru</h2>
                <p class="text-muted mb-0">Edukasi promotif & preventif untuk masyarakat sehat bebas penyakit.</p>
            </div>
            <a href="<?= App::baseUrl('/artikel') ?>" class="btn btn-outline-success rounded-pill px-4 mt-3 mt-md-0 fw-semibold">
                Semua Artikel <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($articles as $art): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden article-card">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-primary-subtle text-primary rounded-pill small">
                                    <?= htmlspecialchars($art['category_name'] ?? 'Kesehatan') ?>
                                </span>
                                <small class="text-muted"><i class="bi bi-calendar3 me-1"></i> <?= date('d M Y', strtotime($art['created_at'])) ?></small>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">
                                <a href="<?= App::baseUrl('/artikel/' . $art['slug']) ?>" class="text-dark text-decoration-none hover-primary">
                                    <?= htmlspecialchars($art['title']) ?>
                                </a>
                            </h5>
                            <p class="text-muted small mb-4 flex-grow-1">
                                <?= htmlspecialchars($art['summary']) ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                                <span class="small text-muted"><i class="bi bi-eye me-1"></i> <?= $art['views_count'] ?> kali dibaca</span>
                                <a href="<?= App::baseUrl('/artikel/' . $art['slug']) ?>" class="btn btn-sm btn-link text-primary fw-bold text-decoration-none p-0">
                                    Baca Selengkapnya &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Banner Kuis Interaktif -->
        <div class="card border-0 shadow-sm rounded-4 mt-5 bg-quiz-gradient text-white p-4 p-lg-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white bg-opacity-20 small mb-3">
                        <i class="bi bi-award-fill text-warning"></i>
                        <span>Kuis Kesehatan Berhadiah Pengetahuan</span>
                    </div>
                    <h3 class="fw-bold mb-2">Uji Pengetahuan PHBS & Kesehatan Keluarga Anda</h3>
                    <p class="text-white-75 mb-0">
                        Ikuti kuis interaktif seputar pencegahan stunting, DBD, gizi seimbang, dan hipertensi secara seru dan dapatkan sertifikat nilai kesehatan.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="<?= App::baseUrl('/kuis') ?>" class="btn btn-light btn-lg px-4 py-3 rounded-pill text-primary fw-bold shadow">
                        <i class="bi bi-play-circle-fill me-1"></i> Mulai Kuis Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 12 Desa Wilayah Astanajapura Section -->
<section class="py-5 bg-light">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-4">
            <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase px-3 py-2 rounded-pill mb-2">Cakupan Wilayah</span>
            <h2 class="fw-bold text-dark">Wilayah Kerja Puskesmas Astanajapura</h2>
            <p class="text-muted">Siap melayani seluruh warga dari 12 desa di wilayah Kecamatan Astanajapura, Kabupaten Cirebon.</p>
        </div>

        <div class="row g-3 justify-content-center">
            <?php foreach ($villages as $v): ?>
                <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                    <div class="card border-0 shadow-sm rounded-3 text-center p-3 h-100 bg-white village-card">
                        <div class="text-primary mb-2"><i class="bi bi-geo-alt-fill fs-4"></i></div>
                        <h6 class="fw-bold mb-1 text-dark small"><?= htmlspecialchars($v['name']) ?></h6>
                        <span class="text-muted" style="font-size: 0.75rem;">Kodepos <?= $v['postal_code'] ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
