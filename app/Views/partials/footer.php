<?php
use App\Config\App;
$puskesmas = App::puskesmas();
?>
<footer class="bg-dark text-white pt-5 pb-4 mt-auto border-top border-secondary">
    <div class="container">
        <div class="row g-4 mb-4">
            <!-- Col 1: Brand & About -->
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-heart-pulse-fill"></i>
                    </div>
                    <span class="fs-4 fw-bold text-white">SmartHealthy</span>
                </div>
                <p class="text-secondary small mb-3">
                    Sistem Pendaftaran & Antrean Online Puskesmas Astanajapura. Layanan kesehatan prima, transparan, cepat, dan 100% gratis untuk seluruh masyarakat.
                </p>
                <div class="badge bg-success-subtle text-success-emphasis border border-success-subtle p-2 rounded-pill">
                    <i class="bi bi-shield-check me-1"></i> Pelayanan Standar BPJS / JKN
                </div>
            </div>

            <!-- Col 2: Wilayah Kerja Astanajapura -->
            <div class="col-lg-3 col-md-6">
                <h6 class="text-white fw-bold text-uppercase mb-3 small tracking-wider">Wilayah Pelayanan</h6>
                <p class="text-secondary small mb-2">Melayani 12 Desa di Kec. Astanajapura:</p>
                <div class="d-flex flex-wrap gap-1">
                    <span class="badge bg-secondary-subtle text-light small">Astanajapura</span>
                    <span class="badge bg-secondary-subtle text-light small">Buntet</span>
                    <span class="badge bg-secondary-subtle text-light small">Japurabakti</span>
                    <span class="badge bg-secondary-subtle text-light small">Japuralor</span>
                    <span class="badge bg-secondary-subtle text-light small">Japura Kidul</span>
                    <span class="badge bg-secondary-subtle text-light small">Kanci</span>
                    <span class="badge bg-secondary-subtle text-light small">Kanci Kulon</span>
                    <span class="badge bg-secondary-subtle text-light small">Kendal</span>
                    <span class="badge bg-secondary-subtle text-light small">Mertapada Kulon</span>
                    <span class="badge bg-secondary-subtle text-light small">Mertapada Wetan</span>
                    <span class="badge bg-secondary-subtle text-light small">Munjul</span>
                    <span class="badge bg-secondary-subtle text-light small">Sidamulya</span>
                </div>
            </div>

            <!-- Col 3: Quick Links -->
            <div class="col-lg-2 col-md-6">
                <h6 class="text-white fw-bold text-uppercase mb-3 small tracking-wider">Tautan Cepat</h6>
                <ul class="list-unstyled text-secondary small mb-0">
                    <li class="mb-2"><a href="<?= App::baseUrl('/layanan') ?>" class="text-secondary text-decoration-none hover-white">Layanan Poli</a></li>
                    <li class="mb-2"><a href="<?= App::baseUrl('/cek-antrean') ?>" class="text-secondary text-decoration-none hover-white">Cek Nomor Antrean</a></li>
                    <li class="mb-2"><a href="<?= App::baseUrl('/artikel') ?>" class="text-secondary text-decoration-none hover-white">Edukasi Kesehatan</a></li>
                    <li class="mb-2"><a href="<?= App::baseUrl('/register') ?>" class="text-secondary text-decoration-none hover-white">Daftar Pasien Baru</a></li>
                    <li class="mb-2"><a href="<?= App::baseUrl('/migrate') ?>" class="text-secondary text-decoration-none hover-white"><i class="bi bi-database me-1"></i> Status Database</a></li>
                </ul>
            </div>

            <!-- Col 4: Contact Puskesmas -->
            <div class="col-lg-3 col-md-6">
                <h6 class="text-white fw-bold text-uppercase mb-3 small tracking-wider">Kontak Puskesmas</h6>
                <ul class="list-unstyled text-secondary small mb-0">
                    <li class="mb-2 d-flex align-items-start gap-2">
                        <i class="bi bi-geo-alt-fill text-danger mt-1"></i>
                        <span><?= htmlspecialchars($puskesmas['address']) ?></span>
                    </li>
                    <li class="mb-2 d-flex align-items-center gap-2">
                        <i class="bi bi-telephone-fill text-primary"></i>
                        <span><?= htmlspecialchars($puskesmas['phone']) ?></span>
                    </li>
                    <li class="mb-2 d-flex align-items-center gap-2">
                        <i class="bi bi-whatsapp text-success"></i>
                        <span><?= htmlspecialchars($puskesmas['hotline']) ?> (Hotline/IGD)</span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-envelope-fill text-warning"></i>
                        <span class="text-truncate"><?= htmlspecialchars($puskesmas['email']) ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <hr class="border-secondary my-4">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-secondary">
            <div>
                &copy; <?= date('Y') ?> <strong><?= htmlspecialchars(App::name()) ?></strong> — <?= htmlspecialchars($puskesmas['name']) ?>. Hak Cipta Dilindungi.
            </div>
        </div>
    </div>
</footer>

<!-- SmartHealthy AI Chatbot Partial -->
<?php require_once __DIR__ . '/chatbot.php'; ?>

<!-- Bootstrap 5 Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom App Script -->
<script src="<?= App::baseUrl('/public/js/app.js') ?>"></script>
</body>
</html>

