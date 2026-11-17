<?php
use App\Config\App;
use App\Core\Session;
?>

<div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5">
    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div class="bg-primary text-white p-3 rounded-4 fs-3">
            <i class="bi bi-calendar-plus"></i>
        </div>
        <div>
            <h4 class="fw-bold text-dark mb-1">Form Pendaftaran Layanan Puskesmas</h4>
            <p class="text-muted small mb-0">Langkah 1 dari 2: Pilih poli, tanggal kunjungan, dan masukkan keluhan Anda</p>
        </div>
    </div>

    <!-- Verification Check Warning if Unverified -->
    <?php if (!$isVerified): ?>
        <div class="alert alert-warning border-0 rounded-4 p-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-4 text-warning"></i>
                <div>
                    <strong class="text-dark d-block">Akun Anda Belum Terverifikasi</strong>
                    <small class="text-muted">Untuk kenyamanan pelayanan dan konfirmasi antrean resmi, disarankan memverifikasi WhatsApp atau Email.</small>
                </div>
            </div>
            <a href="<?= App::baseUrl('/verifikasi') ?>" class="btn btn-sm btn-warning rounded-pill px-3 fw-bold">
                Verifikasi Akun
            </a>
        </div>
    <?php endif; ?>

    <!-- Data Pasien Ringkas -->
    <div class="alert alert-info border-0 rounded-3 mb-4">
        <div class="row g-2 small">
            <div class="col-md-4">
                <span class="text-muted d-block">Nama Pasien:</span>
                <strong class="text-dark"><?= htmlspecialchars($user['name']) ?></strong>
            </div>
            <div class="col-md-4">
                <span class="text-muted d-block">No. WhatsApp / HP:</span>
                <strong class="text-dark"><?= htmlspecialchars($user['phone']) ?></strong>
            </div>
            <div class="col-md-4">
                <span class="text-muted d-block">Desa Domisili:</span>
                <strong class="text-dark">Desa <?= htmlspecialchars($user['village_name'] ?? 'Astanajapura') ?></strong>
            </div>
        </div>
    </div>

    <form action="<?= App::baseUrl('/pendaftaran/konfirmasi') ?>" method="POST" id="bookingForm">
        <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
        <input type="hidden" name="schedule_id" id="scheduleIdInput" value="<?= $scheduleToday['id'] ?? '' ?>">

        <!-- 1. Pilih Poli -->
        <div class="mb-4">
            <label class="form-label fw-bold text-dark">1. Pilih Poli / Layanan Kesehatan <span class="text-danger">*</span></label>
            <div class="row g-3">
                <?php foreach ($services as $srv): ?>
                    <div class="col-md-6">
                        <div class="form-check card-select p-3 border rounded-3 h-100 position-relative <?= ($selectedServiceId == $srv['id']) ? 'border-primary bg-primary-subtle' : '' ?>" onclick="selectPoli(<?= $srv['id'] ?>)">
                            <input class="form-check-input position-absolute top-3 end-3" type="radio" name="health_service_id" id="service_<?= $srv['id'] ?>" value="<?= $srv['id'] ?>" <?= ($selectedServiceId == $srv['id']) ? 'checked' : '' ?> required>
                            <label class="form-check-label w-100 pe-4 cursor-pointer" for="service_<?= $srv['id'] ?>">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <i class="bi <?= $srv['icon'] ?? 'bi-hospital' ?> text-primary fs-5"></i>
                                    <strong class="text-dark"><?= htmlspecialchars($srv['name']) ?></strong>
                                </div>
                                <p class="text-muted small mb-1"><?= htmlspecialchars($srv['description'] ?? '') ?></p>
                                <span class="badge bg-light text-dark border small">
                                    <i class="bi bi-person-badge text-primary me-1"></i> <?= htmlspecialchars($srv['doctor_name'] ?? 'Dokter Jaga') ?>
                                </span>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 2. Pilih Tanggal Kunjungan -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label fw-bold text-dark">2. Tanggal Rencana Kunjungan <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar-event text-muted"></i></span>
                    <input type="date" name="registration_date" id="regDateInput" value="<?= htmlspecialchars($selectedDate) ?>" min="<?= date('Y-m-d') ?>" class="form-control bg-light border-start-0" required onchange="fetchQuota()">
                </div>
                <small class="text-muted">Hari pelayanan: <span id="dayNameLabel" class="fw-bold text-primary"><?= $dayName ?></span> (Senin s/d Sabtu).</small>
            </div>

            <!-- Real-time Quota Info Card -->
            <div class="col-md-6">
                <label class="form-label fw-bold text-dark">Ketersediaan Kuota Hari Ini</label>
                <div class="p-3 bg-light rounded-3 border" id="quotaBox">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-muted">Sisa Kuota Tersedia:</span>
                        <span class="badge <?= $quotaInfo['is_full'] ? 'bg-danger' : 'bg-success' ?> fs-6 px-3 py-1 rounded-pill" id="quotaBadge">
                            <?= $quotaInfo['available_quota'] ?> / <?= $quotaInfo['total_quota'] ?> Pasien
                        </span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <?php $p = min(100, round(($quotaInfo['used_quota'] / max(1, $quotaInfo['total_quota'])) * 100)); ?>
                        <div class="progress-bar <?= ($p >= 100) ? 'bg-danger' : (($p >= 75) ? 'bg-warning' : 'bg-success') ?>" id="quotaProgressBar" style="width: <?= $p ?>%"></div>
                    </div>
                    <small class="text-muted mt-2 d-block" id="scheduleNote">
                        <i class="bi bi-clock-history me-1"></i> Jam Buka: 08:00 - 12:00 WIB
                    </small>
                </div>
            </div>
        </div>

        <!-- 3. Keluhan Medis -->
        <div class="mb-4">
            <label class="form-label fw-bold text-dark">3. Keluhan Utama & Gejala Pasien <span class="text-danger">*</span></label>
            <textarea name="complaint" class="form-control bg-light" rows="3" placeholder="Contoh: Demam tinggi sejak 2 hari yang lalu, flu, batuk, dan badan terasa lemas..." minlength="5" required></textarea>
            <small class="text-muted">Tuliskan gejala yang Anda rasakan untuk memudahkan persiapan medis dokter pemeriksa.</small>
        </div>

        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
            <a href="<?= App::baseUrl('/dashboard') ?>" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-1"></i> Batal
            </a>
            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow" id="submitBtn">
                Lanjut ke Konfirmasi <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </div>
    </form>
</div>

<script>
let currentPoliId = <?= $selectedServiceId ?>;

function selectPoli(poliId) {
    currentPoliId = poliId;
    document.querySelectorAll('.card-select').forEach(card => {
        card.classList.remove('border-primary', 'bg-primary-subtle');
    });
    const radio = document.getElementById('service_' + poliId);
    if (radio) {
        radio.checked = true;
        radio.closest('.card-select').classList.add('border-primary', 'bg-primary-subtle');
    }
    fetchQuota();
}

function fetchQuota() {
    const dateInput = document.getElementById('regDateInput').value;
    if (!dateInput) return;

    fetch('<?= App::baseUrl("/api/kuota") ?>?service_id=' + currentPoliId + '&date=' + dateInput)
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                document.getElementById('dayNameLabel').textContent = data.day_name;
                
                const quotaBadge = document.getElementById('quotaBadge');
                const progressBar = document.getElementById('quotaProgressBar');
                const submitBtn = document.getElementById('submitBtn');
                const scheduleNote = document.getElementById('scheduleNote');

                if (data.schedule) {
                    document.getElementById('scheduleIdInput').value = data.schedule.id;
                    scheduleNote.innerHTML = `<i class="bi bi-clock-history me-1"></i> Jam Layanan: ${data.schedule.start_time.substring(0, 5)} - ${data.schedule.end_time.substring(0, 5)} WIB`;
                } else {
                    document.getElementById('scheduleIdInput').value = '';
                    scheduleNote.innerHTML = `<i class="bi bi-clock-history me-1"></i> Jam Layanan: 08:00 - 12:00 WIB (Reguler)`;
                }

                if (data.day_name === 'Minggu') {
                    quotaBadge.className = 'badge bg-danger fs-6 px-3 py-1 rounded-pill';
                    quotaBadge.textContent = 'Puskesmas Tutup (Minggu)';
                    submitBtn.disabled = true;
                    return;
                }

                if (data.quota.is_full) {
                    quotaBadge.className = 'badge bg-danger fs-6 px-3 py-1 rounded-pill';
                    quotaBadge.textContent = 'Kuota Penuh (0/' + data.quota.total_quota + ')';
                    progressBar.style.width = '100%';
                    progressBar.className = 'progress-bar bg-danger';
                    submitBtn.disabled = true;
                } else {
                    quotaBadge.className = 'badge bg-success fs-6 px-3 py-1 rounded-pill';
                    quotaBadge.textContent = data.quota.available_quota + ' / ' + data.quota.total_quota + ' Pasien';
                    const pct = Math.min(100, Math.round((data.quota.used_quota / data.quota.total_quota) * 100));
                    progressBar.style.width = pct + '%';
                    progressBar.className = pct >= 75 ? 'progress-bar bg-warning' : 'progress-bar bg-success';
                    submitBtn.disabled = false;
                }
            }
        })
        .catch(err => console.error('Error fetching quota:', err));
}
</script>
