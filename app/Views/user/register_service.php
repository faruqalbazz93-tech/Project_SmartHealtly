<?php
use App\Config\App;
use App\Core\Session;
?>

<div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-lg-5">
    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div class="bg-primary text-white p-3 rounded-4 fs-3">
            <i class="bi bi-calendar-check"></i>
        </div>
        <div>
            <h4 class="fw-bold text-dark mb-1">Form Pendaftaran Layanan Puskesmas</h4>
            <p class="text-muted small mb-0">Pilih poli tujuan dan tanggal rencana kunjungan berobat Anda</p>
        </div>
    </div>

    <!-- Data Pasien Ringkas -->
    <div class="alert alert-info border-0 rounded-3 mb-4">
        <div class="row g-2 small">
            <div class="col-md-4">
                <strong>Nama Pasien:</strong> <?= htmlspecialchars($user['name']) ?>
            </div>
            <div class="col-md-4">
                <strong>NIK:</strong> <?= htmlspecialchars($user['nik']) ?>
            </div>
            <div class="col-md-4">
                <strong>Desa:</strong> <?= htmlspecialchars($user['village_name'] ?? 'Astanajapura') ?>
            </div>
        </div>
    </div>

    <form action="<?= App::baseUrl('/pendaftaran/simpan') ?>" method="POST">
        <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">

        <div class="mb-4">
            <label class="form-label fw-bold text-dark">Pilih Poli / Layanan Kesehatan <span class="text-danger">*</span></label>
            <div class="row g-3">
                <?php foreach ($services as $service): ?>
                    <div class="col-md-6">
                        <div class="form-check card-select p-3 border rounded-3 h-100 position-relative <?= ($selectedServiceId == $service['id']) ? 'border-primary bg-primary-subtle' : '' ?>">
                            <input class="form-check-input position-absolute top-3 end-3" type="radio" name="health_service_id" id="service_<?= $service['id'] ?>" value="<?= $service['id'] ?>" <?= ($selectedServiceId == $service['id'] || (empty($selectedServiceId) && $service['id'] == 1)) ? 'checked' : '' ?> required>
                            <label class="form-check-label w-100 pe-4 cursor-pointer" for="service_<?= $service['id'] ?>">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <i class="bi <?= $service['icon'] ?? 'bi-hospital' ?> text-primary fs-5"></i>
                                    <strong class="text-dark"><?= htmlspecialchars($service['name']) ?></strong>
                                </div>
                                <p class="text-muted small mb-1"><?= htmlspecialchars($service['description'] ?? '') ?></p>
                                <span class="badge bg-light text-dark border small"><i class="bi bi-person-badge me-1"></i> <?= htmlspecialchars($service['doctor_name'] ?? 'Dokter Jaga') ?></span>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <!-- Tanggal Pendaftaran -->
            <div class="col-md-6">
                <label class="form-label fw-bold text-dark">Tanggal Rencana Kunjungan <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar-event text-muted"></i></span>
                    <input type="date" name="registration_date" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" class="form-control bg-light border-start-0" required>
                </div>
                <small class="text-muted">Pilih hari ini atau hari kerja berikutnya (Senin - Sabtu).</small>
            </div>

            <!-- Jam Operasional Info -->
            <div class="col-md-6">
                <label class="form-label fw-bold text-dark">Waktu Pelayanan</label>
                <div class="bg-light p-3 rounded-3 small">
                    <i class="bi bi-clock-history text-primary me-1"></i>
                    <strong>Pukul 08.00 - 12.00 WIB</strong>
                    <p class="text-muted mb-0 mt-1">Harap hadir di Puskesmas Astanajapura minimal 15 menit sebelum nomor antrean dipanggil.</p>
                </div>
            </div>
        </div>

        <!-- Keluhan Pasien -->
        <div class="mb-4">
            <label class="form-label fw-bold text-dark">Keluhan Utama & Gejala Pasien <span class="text-danger">*</span></label>
            <textarea name="complaint" class="form-control bg-light" rows="3" placeholder="Contoh: Demam sejak 2 hari yang lalu disertai batuk dan pusing kepala..." required></textarea>
            <small class="text-muted">Jelaskan keluhan medis Anda secara singkat untuk memudahkan petugas mengarahkan ke tindakan yang tepat.</small>
        </div>

        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
            <a href="<?= App::baseUrl('/dashboard') ?>" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-1"></i> Batal
            </a>
            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow">
                <i class="bi bi-ticket-perforated-fill me-1"></i> Ambil Nomor Antrean Sekarang
            </button>
        </div>
    </form>
</div>
