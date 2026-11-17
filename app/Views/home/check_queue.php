<?php
use App\Config\App;
?>

<div class="bg-primary text-white py-5">
    <div class="container py-3 text-center max-w-700 mx-auto">
        <h1 class="fw-bold mb-2">Cek Status & Nomor Antrean</h1>
        <p class="lead text-white-75 mb-0">Pantau status panggilan antrean Anda hari ini secara real-time tanpa harus berdesakan di loket Puskesmas Astanajapura.</p>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 bg-white mb-4">
                <form action="<?= App::baseUrl('/cek-antrean') ?>" method="GET">
                    <label class="form-label fw-bold text-dark mb-2">Masukkan Kode Pendaftaran / NIK 16 Digit</label>
                    <div class="input-group input-group-lg mb-2">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-upc-scan text-muted"></i></span>
                        <input type="text" name="q" value="<?= htmlspecialchars($query ?? '') ?>" class="form-control bg-light border-start-0" placeholder="Contoh: REG-AST-20260918-001 atau 320914..." required autofocus>
                        <button class="btn btn-primary px-4 fw-bold" type="submit">
                            <i class="bi bi-search me-1"></i> Cari Antrean
                        </button>
                    </div>
                    <small class="text-muted">Kode pendaftaran dapat dilihat pada E-Ticket digital saat Anda selesai mendaftar.</small>
                </form>

                <?php if ($searched): ?>
                    <hr class="my-4">

                    <?php if ($result): ?>
                        <div class="alert alert-success border-0 shadow-sm rounded-4 p-4 mb-0">
                            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill">Kode: <?= htmlspecialchars($result['registration_code']) ?></span>
                                <span class="badge bg-success-subtle text-success fs-6 px-3 py-2 rounded-pill text-uppercase">
                                    Status: <?= htmlspecialchars($result['status']) ?>
                                </span>
                            </div>

                            <div class="text-center py-3 bg-white rounded-3 shadow-xs mb-3">
                                <span class="text-muted small d-block">Nomor Antrean Anda:</span>
                                <div class="display-3 fw-bold text-primary">#<?= $result['queue_number'] ?></div>
                                <div class="fw-bold text-dark fs-5"><?= htmlspecialchars($result['service_name']) ?></div>
                                <small class="text-muted"><i class="bi bi-person-badge me-1"></i> Dokter: <?= htmlspecialchars($result['doctor_name'] ?? 'Dokter Jaga') ?></small>
                            </div>

                            <div class="row g-2 small text-dark">
                                <div class="col-sm-6">
                                    <strong>Nama Pasien:</strong> <?= htmlspecialchars($result['patient_name']) ?>
                                </div>
                                <div class="col-sm-6">
                                    <strong>Tanggal Kunjungan:</strong> <?= date('d M Y', strtotime($result['registration_date'])) ?>
                                </div>
                                <div class="col-12 mt-2">
                                    <strong>Keluhan:</strong> <?= htmlspecialchars($result['complaint']) ?>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <a href="<?= App::baseUrl('/pendaftaran/tiket/' . $result['registration_code']) ?>" class="btn btn-outline-primary rounded-pill btn-sm fw-semibold">
                                    <i class="bi bi-printer me-1"></i> Buka E-Ticket Lengkap
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning border-0 shadow-sm rounded-4 p-4 text-center mb-0">
                            <i class="bi bi-exclamation-circle-fill fs-1 text-warning mb-2 d-block"></i>
                            <h5 class="fw-bold text-dark mb-1">Data Antrean Tidak Ditemukan</h5>
                            <p class="text-muted small mb-0">
                                Tidak ditemukan antrean aktif dengan kode / NIK <strong>"<?= htmlspecialchars($query) ?>"</strong> untuk jadwal hari ini. Pastikan Anda telah terdaftar atau memeriksa ejaan kode pendaftaran.
                            </p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
