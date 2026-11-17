<?php
use App\Config\App;
?>

<div class="bg-primary text-white py-5">
    <div class="container py-3">
        <h1 class="fw-bold mb-2">Kontak & Lokasi Pelayanan</h1>
        <p class="lead text-white-75 mb-0">Hubungi kami atau kunjungi langsung gedung Puskesmas Astanajapura untuk pelayanan kesehatan dan administrasi.</p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <!-- Contact Info Cards -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Alamat Puskesmas</h5>
                <p class="text-muted small mb-3">
                    <strong><?= htmlspecialchars($puskesmas['name']) ?></strong><br>
                    <?= htmlspecialchars($puskesmas['address']) ?><br>
                    <?= htmlspecialchars($puskesmas['district']) ?>, <?= htmlspecialchars($puskesmas['regency']) ?>
                </p>

                <h6 class="fw-bold text-dark small text-uppercase tracking-wider mb-2">Saluran Komunikasi:</h6>
                <ul class="list-unstyled small mb-4">
                    <li class="mb-2 d-flex align-items-center gap-2">
                        <i class="bi bi-telephone-fill text-primary"></i>
                        <span>Telepon Kantor: <strong><?= htmlspecialchars($puskesmas['phone']) ?></strong></span>
                    </li>
                    <li class="mb-2 d-flex align-items-center gap-2">
                        <i class="bi bi-whatsapp text-success"></i>
                        <span>Hotline / WhatsApp: <strong><?= htmlspecialchars($puskesmas['hotline']) ?></strong></span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-envelope-fill text-warning"></i>
                        <span>Email: <strong><?= htmlspecialchars($puskesmas['email']) ?></strong></span>
                    </li>
                </ul>

                <h6 class="fw-bold text-dark small text-uppercase tracking-wider mb-2">Jam Buka Pelayanan:</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-borderless small mb-0">
                        <tr>
                            <td>Senin - Kamis</td>
                            <td>: 08.00 - 12.00 WIB</td>
                        </tr>
                        <tr>
                            <td>Jumat</td>
                            <td>: 08.00 - 11.00 WIB</td>
                        </tr>
                        <tr>
                            <td>Sabtu</td>
                            <td>: 08.00 - 11.30 WIB</td>
                        </tr>
                        <tr>
                            <td class="text-danger fw-semibold">UGD / Bersalin</td>
                            <td class="text-danger fw-semibold">: 24 Jam Nonstop</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Google Map / Illustration -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bg-white p-4">
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-map-fill text-primary me-2"></i>Peta Lokasi Puskesmas</h5>
                <div class="ratio ratio-16x9 rounded-3 overflow-hidden border">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3961.800000000000!2d108.6300000!3d-6.8100000!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwNDgnMzYuMCJTIDEwOMKwMzcnNDguMCJF!5e0!3m2!1sid!2sid!4v1600000000000!5m2!1sid!2sid" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </div>
                <small class="text-muted mt-3 d-block">
                    * Terletak strategis di jalur utama Kecamatan Astanajapura, mudah dijangkau dengan angkutan desa dan kendaraan pribadi.
                </small>
            </div>
        </div>
    </div>
</div>
