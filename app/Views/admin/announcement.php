<?php
use App\Config\App;
use App\Core\Session;

$villages = $villages ?? [];
$services = $services ?? [];
?>

<div class="card border-0 shadow-sm rounded-4 bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-megaphone-fill text-danger me-2"></i>Kirim Pengumuman & Notifikasi Pasien
            </h4>
            <p class="text-muted small mb-0">
                Kirimkan informasi penting, perubahan jadwal darurat, atau penyuluhan kesehatan ke akun pasien terdaftar
            </p>
        </div>
        <a href="<?= App::baseUrl('/notifikasi') ?>" class="btn btn-outline-primary rounded-pill btn-sm px-3 fw-semibold">
            <i class="bi bi-inbox me-1"></i> Lihat Notifikasi
        </a>
    </div>

    <div class="row g-4">
        <!-- Form Column -->
        <div class="col-lg-7">
            <form action="<?= App::baseUrl('/admin/pengumuman/kirim') ?>" method="POST" id="announcementForm">
                <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">

                <!-- 1. Target Pengguna -->
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark small">1. Sasaran Penerima Notifikasi <span class="text-danger">*</span></label>
                    <select name="target_type" id="targetType" class="form-select bg-light" required onchange="toggleTargetSelect(this.value)">
                        <option value="all">Semua Pasien Terdaftar (Broadcast)</option>
                        <option value="village">Pasien Berdasarkan Desa Domisili</option>
                        <option value="service">Pasien di Poli Tertentu (Antrean Aktif)</option>
                    </select>
                </div>

                <!-- Sub-selector for Village -->
                <div class="mb-3 d-none" id="villageSelectBox">
                    <label class="form-label fw-bold text-dark small">Pilih Desa Sasaran:</label>
                    <select name="target_id" id="villageSelect" class="form-select bg-light" disabled>
                        <?php foreach ($villages as $vil): ?>
                            <option value="<?= $vil['id'] ?>"><?= htmlspecialchars($vil['name']) ?> (Kec. <?= htmlspecialchars($vil['district']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Sub-selector for Service -->
                <div class="mb-3 d-none" id="serviceSelectBox">
                    <label class="form-label fw-bold text-dark small">Pilih Poliklinik Sasaran:</label>
                    <select name="target_id" id="serviceSelect" class="form-select bg-light" disabled>
                        <?php foreach ($services as $srv): ?>
                            <option value="<?= $srv['id'] ?>"><?= htmlspecialchars($srv['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 2. Judul Pengumuman -->
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark small">2. Judul Notifikasi <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="notifTitleInput" class="form-control bg-light" placeholder="Contoh: Pengumuman Jadwal Vaksinasi / Pelayanan Libur Nasional" required maxlength="150" oninput="updateLivePreview()">
                </div>

                <!-- 3. Pesan Notifikasi -->
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark small">3. Isi Pesan Pengumuman <span class="text-danger">*</span></label>
                    <textarea name="message" id="notifMessageInput" rows="4" class="form-control bg-light" placeholder="Tuliskan isi pengumuman yang jelas, informatif, dan mudah dipahami masyarakat..." required maxlength="600" oninput="updateLivePreview()"></textarea>
                    <small class="text-muted d-block mt-1">Maksimal 600 karakter.</small>
                </div>

                <!-- 4. Tautan Tujuan (Opsional) -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark small">4. Tautan Tujuan Tindakan (Opsional)</label>
                    <input type="text" name="target_url" class="form-control bg-light" value="/layanan" placeholder="/layanan atau /artikel/slug">
                    <small class="text-muted">Halaman yang akan dibuka saat pasien menekan notifikasi.</small>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm" onclick="return confirm('Kirimkan pengumuman ini kepada target pengguna sekarang?');">
                        <i class="bi bi-send-fill me-1"></i> Kirim Notifikasi Sekarang
                    </button>
                </div>
            </form>
        </div>

        <!-- Preview Column -->
        <div class="col-lg-5">
            <div class="card border-0 bg-light rounded-4 p-4 h-100">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-eye me-2 text-primary"></i>Pratinjau Tampilan di Ponsel Pasien</h6>
                
                <!-- Mockup Notification Card -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-3 mb-3 border-start border-4 border-danger">
                    <div class="d-flex align-items-start gap-2">
                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                            <i class="bi bi-megaphone-fill"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-danger rounded-pill" style="font-size: 0.65rem;">Pengumuman Resmi</span>
                                <small class="text-muted" style="font-size: 0.7rem;">Baru saja</small>
                            </div>
                            <h6 class="fw-bold text-dark mb-1 small" id="previewTitle">Judul Notifikasi Akan Muncul di Sini</h6>
                            <p class="text-muted small mb-0 lh-sm" id="previewMessage" style="font-size: 0.8rem;">
                                Isi pesan pengumuman resmi dari Puskesmas Astanajapura akan tampil di layar pengguna seperti ini...
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Guidelines Box -->
                <div class="alert alert-info border-0 rounded-3 small mb-0">
                    <strong class="d-block mb-1"><i class="bi bi-info-circle-fill me-1"></i>Pedoman Pengiriman:</strong>
                    <ul class="mb-0 ps-3">
                        <li>Gunakan bahasa yang sopan, ramah, dan mudah dipahami.</li>
                        <li>Hindari mengirim pesan berulang-ulang untuk menjaga kenyamanan warga.</li>
                        <li>Jangan membagikan data rekam medis pasien di pengumuman umum.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleTargetSelect(val) {
    const vilBox = document.getElementById('villageSelectBox');
    const srvBox = document.getElementById('serviceSelectBox');
    const vilSelect = document.getElementById('villageSelect');
    const srvSelect = document.getElementById('serviceSelect');

    if (val === 'village') {
        vilBox.classList.remove('d-none');
        srvBox.classList.add('d-none');
        vilSelect.disabled = false;
        srvSelect.disabled = true;
    } else if (val === 'service') {
        vilBox.classList.add('d-none');
        srvBox.classList.remove('d-none');
        vilSelect.disabled = true;
        srvSelect.disabled = false;
    } else {
        vilBox.classList.add('d-none');
        srvBox.classList.add('d-none');
        vilSelect.disabled = true;
        srvSelect.disabled = true;
    }
}

function updateLivePreview() {
    const titleVal = document.getElementById('notifTitleInput').value.trim();
    const msgVal = document.getElementById('notifMessageInput').value.trim();

    document.getElementById('previewTitle').textContent = titleVal || 'Judul Notifikasi Akan Muncul di Sini';
    document.getElementById('previewMessage').textContent = msgVal || 'Isi pesan pengumuman resmi dari Puskesmas Astanajapura akan tampil di layar pengguna seperti ini...';
}
</script>
