<?php

use App\Config\App;
use App\Core\Session;

$connectionStatus = $connectionStatus ?? [];
$settings = $settings ?? [];
$stats = $stats ?? [];
$schedStats = $schedStats ?? [];
$logs = $logs ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$totalLogs = $totalLogs ?? 0;
$statusFilter = $statusFilter ?? '';
$typeFilter = $typeFilter ?? '';
$dateFilter = $dateFilter ?? '';

$isMasterEnabled = (bool)(int)($settings['whatsapp_enabled'] ?? '1');

// Connection status visual configuration
$connStatusKey = $connectionStatus['status'] ?? 'unconfigured';
$connBadgeClass = $connectionStatus['badge_class'] ?? 'bg-secondary text-white';
$connBadgeIcon = $connectionStatus['badge_icon'] ?? 'bi-dash-circle-fill';
$connLabel = $connectionStatus['mode_label'] ?? 'Belum Dikonfigurasi';
$maskedToken = $connectionStatus['masked_token'] ?? 'Belum Dikonfigurasi';
?>

<div class="container-fluid py-4 px-lg-4">
    <!-- Breadcrumb & Header Navigasi -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="<?= App::baseUrl('/admin') ?>" class="text-decoration-none text-muted">Admin</a></li>
            <li class="breadcrumb-item text-muted">Pengaturan</li>
            <li class="breadcrumb-item active fw-semibold text-success" aria-current="page">WhatsApp Gateway</li>
        </ol>
    </nav>

    <!-- 1. HEADER SECTION -->
    <div class="card border shadow-sm rounded-4 bg-white p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-success text-white rounded-pill px-3 py-1 small fw-semibold">
                        <i class="bi bi-whatsapp me-1"></i> Fonnte WhatsApp API Gateway
                    </span>
                    <span class="text-muted small">&bull; Otomatisasi Notifikasi Puskesmas</span>
                </div>
                <h3 class="fw-bold text-dark mb-1">
                    <i class="bi bi-gear-wide-connected text-success me-2"></i>Pengaturan WhatsApp Gateway
                </h3>
                <p class="text-muted mb-0 small">
                    Kelola integrasi resmi Fonnte API, sakelar peristiwa otomatis, template pesan dinamis, pengingat kunjungan, serta audit riwayat pengiriman pesan WhatsApp.
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="#section-test-send" class="btn btn-outline-success rounded-pill px-3 fw-semibold">
                    <i class="bi bi-send-check me-1"></i> Uji Kirim Pesan
                </a>
                <a href="<?= App::baseUrl('/admin/pengaturan/whatsapp/reminders-run') ?>" class="btn btn-outline-primary rounded-pill px-3 fw-semibold">
                    <i class="bi bi-alarm-fill me-1"></i> Jalankan Scheduler Pengingat
                </a>
                <a href="https://docs.fonnte.com/" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary rounded-pill px-3 fw-semibold">
                    <i class="bi bi-book me-1"></i> Docs Fonnte
                </a>
            </div>
        </div>
    </div>

    <!-- FLASH ALERTS (Aman dari Warning Array to String) -->
    <?php if ($successMessages = Session::getFlash('success')): ?>
        <?php foreach ((array)$successMessages as $msg): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                <div class="flex-grow-1">
                    <?php if (is_array($msg)): ?>
                        <?php foreach ($msg as $subMsg): ?>
                            <div><?= htmlspecialchars((string)$subMsg, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endforeach; ?>
                    <?php elseif (strip_tags((string)$msg) !== (string)$msg): ?>
                        <?= strip_tags((string)$msg, '<strong><b><em><i><code><br>') ?>
                    <?php else: ?>
                        <?= htmlspecialchars((string)$msg, ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($dangerMessages = Session::getFlash('danger')): ?>
        <?php foreach ((array)$dangerMessages as $msg): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                <div class="flex-grow-1">
                    <?php if (is_array($msg)): ?>
                        <?php foreach ($msg as $subMsg): ?>
                            <div><?= htmlspecialchars((string)$subMsg, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endforeach; ?>
                    <?php elseif (strip_tags((string)$msg) !== (string)$msg): ?>
                        <?= strip_tags((string)$msg, '<strong><b><em><i><code><br>') ?>
                    <?php else: ?>
                        <?= htmlspecialchars((string)$msg, ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($infoMessages = Session::getFlash('info')): ?>
        <?php foreach ((array)$infoMessages as $msg): ?>
            <div class="alert alert-info alert-dismissible fade show rounded-4 shadow-sm d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-info-circle-fill fs-5 text-info"></i>
                <div class="flex-grow-1">
                    <?php if (is_array($msg)): ?>
                        <?php foreach ($msg as $subMsg): ?>
                            <div><?= htmlspecialchars((string)$subMsg, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endforeach; ?>
                    <?php elseif (strip_tags((string)$msg) !== (string)$msg): ?>
                        <?= strip_tags((string)$msg, '<strong><b><em><i><code><br>') ?>
                    <?php else: ?>
                        <?= htmlspecialchars((string)$msg, ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- 2. CARD STATUS KONEKSI FONNTE & METRIK UTAMA -->
    <div class="row g-4 mb-4">
        <!-- Status Koneksi Visual -->
        <div class="col-lg-5">
            <div class="card border shadow-sm rounded-4 bg-white p-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold" style="letter-spacing: 0.05em; font-size: 0.72rem;">Status Koneksi API</small>
                        <h5 class="fw-bold text-dark mb-0">Fonnte API Gateway</h5>
                    </div>
                    <span class="badge <?= $connBadgeClass ?> rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1 shadow-sm">
                        <i class="bi <?= $connBadgeIcon ?>"></i>
                        <?= htmlspecialchars($connLabel) ?>
                    </span>
                </div>

                <p class="text-muted small mb-3">
                    <?= htmlspecialchars((string)($connectionStatus['reason'] ?? 'Status koneksi ke server gateway Fonnte.')) ?>
                </p>

                <!-- Detail Perangkat (Respons Resmi API Fonnte) -->
                <div class="bg-light rounded-3 p-3 border mb-3">
                    <div class="row g-2 small">
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size: 0.72rem;">Nama Perangkat:</span>
                            <strong class="text-dark"><?= htmlspecialchars((string)($connectionStatus['device_name'] ?? '-')) ?></strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size: 0.72rem;">Nomor WhatsApp Pengirim:</span>
                            <strong class="text-dark font-monospace"><?= htmlspecialchars((string)($connectionStatus['device_number'] ?? '-')) ?></strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size: 0.72rem;">Status Device:</span>
                            <span class="badge <?= ($connectionStatus['connected'] ?? false) ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?> rounded-pill px-2">
                                <?= htmlspecialchars((string)($connectionStatus['device_status'] ?? 'Unknown')) ?>
                            </span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size: 0.72rem;">Paket / Kuota:</span>
                            <strong class="text-dark"><?= htmlspecialchars((string)($connectionStatus['package'] ?? '-')) ?> / <?= htmlspecialchars((string)($connectionStatus['quota'] ?? '-')) ?></strong>
                        </div>
                        <div class="col-12 mt-2 pt-2 border-top">
                            <span class="text-muted d-block" style="font-size: 0.72rem;">Masa Berlaku Device:</span>
                            <span class="text-dark fw-semibold"><?= htmlspecialchars((string)($connectionStatus['expired'] ?? '-')) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Form Uji Koneksi Cepat -->
                <form action="<?= App::baseUrl('/admin/pengaturan/whatsapp/test-connection') ?>" method="POST" class="mt-auto">
                    <?= \App\Core\Csrf::field() ?>
                    <button type="submit" class="btn btn-outline-dark w-100 rounded-pill py-2 fw-semibold shadow-sm">
                        <i class="bi bi-arrow-repeat me-1"></i> Uji Ulang Koneksi Sekarang
                    </button>
                </form>
            </div>
        </div>

        <!-- 3 Metrik Cepat -->
        <div class="col-lg-7">
            <div class="row g-3 h-100">
                <!-- Metrik 1: Total Terkirim -->
                <div class="col-sm-4">
                    <div class="card border shadow-sm rounded-4 bg-white p-3 h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-check2-all fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size: 0.75rem;">Total Terkirim</small>
                                <h4 class="fw-bold text-dark mb-0"><?= number_format(($stats['sent'] ?? 0) + ($stats['delivered'] ?? 0) + ($stats['read'] ?? 0)) ?></h4>
                            </div>
                        </div>
                        <small class="text-muted mt-2 pt-2 border-top" style="font-size: 0.72rem;">
                            <?= number_format($stats['delivered'] ?? 0) ?> Diterima &bull; <?= number_format($stats['read'] ?? 0) ?> Dibaca
                        </small>
                    </div>
                </div>

                <!-- Metrik 2: Antrean Pengingat -->
                <div class="col-sm-4">
                    <div class="card border shadow-sm rounded-4 bg-white p-3 h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-clock-history fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size: 0.75rem;">Antrean Jadwal</small>
                                <h4 class="fw-bold text-dark mb-0"><?= number_format($schedStats['pending_count'] ?? 0) ?></h4>
                            </div>
                        </div>
                        <small class="text-muted mt-2 pt-2 border-top" style="font-size: 0.72rem;">
                            <?= number_format($schedStats['sent_count'] ?? 0) ?> Terkirim &bull; <?= number_format($schedStats['cancelled_count'] ?? 0) ?> Batal
                        </small>
                    </div>
                </div>

                <!-- Metrik 3: Gagal / Terlewati -->
                <div class="col-sm-4">
                    <div class="card border shadow-sm rounded-4 bg-white p-3 h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-danger-subtle text-danger p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-shield-x fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size: 0.75rem;">Gagal / Ditolak</small>
                                <h4 class="fw-bold text-danger mb-0"><?= number_format(($stats['failed'] ?? 0) + ($stats['rejected'] ?? 0)) ?></h4>
                            </div>
                        </div>
                        <small class="text-muted mt-2 pt-2 border-top" style="font-size: 0.72rem;">
                            <?= number_format($stats['failed'] ?? 0) ?> Gagal &bull; <?= number_format($stats['rejected'] ?? 0) ?> Tanpa Izin
                        </small>
                    </div>
                </div>

                <!-- Banner Informasi Keamanan & Enkripsi Token -->
                <div class="col-12">
                    <div class="card border-0 bg-success-subtle text-success-emphasis rounded-4 p-3 shadow-none">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shield-lock-fill fs-5 text-success"></i>
                            <div class="small">
                                <strong>Keamanan Kredensial Terjamin:</strong> API Token disimpan secara aman di backend, tidak pernah dipublikasikan ke frontend/JavaScript, dan nomor telepon disamarkan (masked) untuk kepatuhan privasi data pasien.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN FORM: PENGATURAN API, EVENT SWITCHES & TEMPLATE -->
    <form action="<?= App::baseUrl('/admin/pengaturan/whatsapp/settings') ?>" method="POST" class="mb-4">
        <?= \App\Core\Csrf::field() ?>

        <div class="row g-4 mb-4">
            <!-- 3. CARD KONFIGURASI API FONNTE -->
            <div class="col-lg-6">
                <div class="card border shadow-sm rounded-4 bg-white p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold text-dark mb-1">
                                <i class="bi bi-key-fill text-warning me-2"></i>Konfigurasi API Fonnte
                            </h5>
                            <small class="text-muted">Masukkan token API dari akun WhatsApp Business Fonnte Anda.</small>
                        </div>
                    </div>

                    <!-- Token Status Saat Ini -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small">Token Aktif Saat Ini:</span>
                            <span class="badge bg-dark-subtle text-dark font-monospace px-2 py-1"><?= htmlspecialchars($maskedToken) ?></span>
                        </div>
                        <small class="text-muted" style="font-size: 0.75rem;">
                            Token disimpan secara terlindungi di backend. Untuk mengganti token, ketikkan token baru di bawah lalu simpan.
                        </small>
                    </div>

                    <!-- Input Token dengan Fitur Show / Hide -->
                    <div class="mb-4">
                        <label for="fonnte_token" class="form-label fw-semibold text-dark small mb-1">
                            API Key / Token Fonnte Baru
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   id="fonnte_token" 
                                   name="fonnte_token" 
                                   class="form-control font-monospace" 
                                   placeholder="Tempelkan API Token Fonnte di sini..." 
                                   autocomplete="off">
                            <button type="button" class="btn btn-outline-secondary" id="btnToggleToken" title="Tampilkan / Sembunyikan API Key">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2">
                            Dapatkan API Token resmi melalui portal resmi <a href="https://fonnte.com" target="_blank" rel="noopener noreferrer" class="text-decoration-none fw-semibold text-success">Fonnte.com</a>. Kosongkan kolom jika tidak ingin mengubah token saat ini.
                        </small>
                    </div>

                    <!-- Tombol Aksi Simpan & Uji -->
                    <div class="pt-3 border-top mt-auto d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                            <i class="bi bi-save me-1"></i> Simpan Konfigurasi
                        </button>
                        <button type="button" class="btn btn-outline-dark rounded-pill px-3 fw-semibold" onclick="document.getElementById('formTestConnQuick').submit();">
                            <i class="bi bi-wifi me-1"></i> Uji Koneksi
                        </button>
                    </div>
                </div>
            </div>

            <!-- 4. CARD PENGATURAN NOTIFIKASI OTOMATIS (EVENT SWITCHES) -->
            <div class="col-lg-6">
                <div class="card border shadow-sm rounded-4 bg-white p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold text-dark mb-1">
                                <i class="bi bi-toggle-on text-success me-2"></i>Pengaturan Notifikasi Otomatis
                            </h5>
                            <small class="text-muted">Aktifkan atau nonaktifkan pengiriman untuk setiap jenis peristiwa sistem.</small>
                        </div>
                        <!-- Master Switch -->
                        <div class="form-check form-switch fs-4 m-0" title="Sakelar Utama Pengiriman WhatsApp">
                            <input class="form-check-input" type="checkbox" role="switch" id="whatsapp_enabled" name="whatsapp_enabled" value="1" <?= $isMasterEnabled ? 'checked' : '' ?>>
                        </div>
                    </div>

                    <!-- List Switches untuk 8 Peristiwa -->
                    <div class="list-group list-group-flush border rounded-3 mb-3">
                        <!-- 1. Pendaftaran Berhasil -->
                        <label class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                            <div>
                                <strong class="d-block text-dark small">1. Pendaftaran Berhasil</strong>
                                <small class="text-muted">Kirim notifikasi seketika pasien selesai mendaftar.</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="notify_registration_enabled" value="1" <?= (bool)(int)($settings['notify_registration_enabled'] ?? '1') ? 'checked' : '' ?>>
                            </div>
                        </label>

                        <!-- 2. Penerbitan Nomor Antrean / Pendaftaran -->
                        <label class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                            <div>
                                <strong class="d-block text-dark small">2. Pengiriman Nomor Antrean / Tiket</strong>
                                <small class="text-muted">Sertakan nomor antrean dan kode registrasi ke nomor pasien.</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="notify_ticket_enabled" value="1" <?= (bool)(int)($settings['notify_ticket_enabled'] ?? '1') ? 'checked' : '' ?>>
                            </div>
                        </label>

                        <!-- 3. Perubahan Status Pendaftaran -->
                        <label class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                            <div>
                                <strong class="d-block text-dark small">3. Perubahan Status Pendaftaran</strong>
                                <small class="text-muted">Kirim update status (Dikonfirmasi, Dipanggil, Selesai).</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="notify_status_change_enabled" value="1" <?= (bool)(int)($settings['notify_status_change_enabled'] ?? '1') ? 'checked' : '' ?>>
                            </div>
                        </label>

                        <!-- 4. Pengingat H-1 -->
                        <label class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                            <div>
                                <strong class="d-block text-dark small">4. Pengingat Jadwal Kunjungan (H-1)</strong>
                                <small class="text-muted">Pengingat 1 hari sebelum kunjungan pada jam yang ditentukan.</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="notify_reminder_h1_enabled" value="1" <?= (bool)(int)($settings['notify_reminder_h1_enabled'] ?? '1') ? 'checked' : '' ?>>
                            </div>
                        </label>

                        <!-- 5. Pengingat 2 Jam Sebelum Kunjungan -->
                        <label class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                            <div>
                                <strong class="d-block text-dark small">5. Pengingat 2 Jam Sebelum Kunjungan</strong>
                                <small class="text-muted">Pengingat menjelang dibukanya poli pelayanan.</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="notify_reminder_h2_enabled" value="1" <?= (bool)(int)($settings['notify_reminder_h2_enabled'] ?? '1') ? 'checked' : '' ?>>
                            </div>
                        </label>

                        <!-- 6. Perubahan Jadwal -->
                        <label class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                            <div>
                                <strong class="d-block text-dark small">6. Penyesuaian Jadwal Layanan / Dokter</strong>
                                <small class="text-muted">Pemberitahuan perubahan jam operasional atau dokter poli.</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="notify_schedule_change_enabled" value="1" <?= (bool)(int)($settings['notify_schedule_change_enabled'] ?? '1') ? 'checked' : '' ?>>
                            </div>
                        </label>

                        <!-- 7. Pembatalan Pendaftaran -->
                        <label class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                            <div>
                                <strong class="d-block text-dark small">7. Pembatalan Pendaftaran Antrean</strong>
                                <small class="text-muted">Konfirmasi saat pendaftaran dibatalkan oleh pasien atau admin.</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="notify_cancellation_enabled" value="1" <?= (bool)(int)($settings['notify_cancellation_enabled'] ?? '1') ? 'checked' : '' ?>>
                            </div>
                        </label>

                        <!-- 8. Pengumuman Admin -->
                        <label class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                            <div>
                                <strong class="d-block text-dark small">8. Siaran Pengumuman Resmi Puskesmas</strong>
                                <small class="text-muted">Broadcast ke pasien ketika admin mempublikasikan pengumuman penting.</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="notify_announcement_enabled" value="1" <?= (bool)(int)($settings['notify_announcement_enabled'] ?? '1') ? 'checked' : '' ?>>
                            </div>
                        </label>
                    </div>

                    <!-- Pengaturan Waktu Scheduler -->
                    <div class="bg-light p-3 rounded-3 border">
                        <small class="text-muted fw-bold d-block mb-2 text-uppercase" style="font-size: 0.72rem;">Waktu Eksekusi Scheduler (Asia/Jakarta WIB):</small>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-dark mb-1">Jam Pengingat H-1</label>
                                <input type="time" name="reminder_h1_time" class="form-control form-control-sm" value="<?= htmlspecialchars((string)($settings['reminder_h1_time'] ?? '08:00')) ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold text-dark mb-1">Offset Pengingat (Jam)</label>
                                <input type="number" name="reminder_h2_offset_hours" min="1" max="12" class="form-control form-control-sm" value="<?= (int)($settings['reminder_h2_offset_hours'] ?? 2) ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. CARD TEMPLATE PESAN WHATSAPP -->
        <div class="card border shadow-sm rounded-4 bg-white p-4 mb-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-file-earmark-text text-primary me-2"></i>Template Pesan WhatsApp
                    </h5>
                    <p class="text-muted small mb-0">
                        Atur format teks pesan notifikasi. Klik badge placeholder di bawah untuk menyisipkan variabel otomatis ke template pesan.
                    </p>
                </div>
            </div>

            <!-- Toolbar Placeholder Helper -->
            <div class="bg-light p-3 rounded-3 border mb-3">
                <small class="text-muted fw-bold d-block mb-2" style="font-size: 0.75rem;">
                    <i class="bi bi-tag-fill text-primary me-1"></i> Klik Placeholder untuk Menyisipkan ke Template Aktif:
                </small>
                <div class="d-flex flex-wrap gap-1">
                    <button type="button" class="btn btn-sm btn-white border rounded-pill shadow-xs placeholder-btn" data-tag="{nama}"><i class="bi bi-plus text-primary"></i> <code>{nama}</code></button>
                    <button type="button" class="btn btn-sm btn-white border rounded-pill shadow-xs placeholder-btn" data-tag="{nomor_pendaftaran}"><i class="bi bi-plus text-primary"></i> <code>{nomor_pendaftaran}</code></button>
                    <button type="button" class="btn btn-sm btn-white border rounded-pill shadow-xs placeholder-btn" data-tag="{layanan}"><i class="bi bi-plus text-primary"></i> <code>{layanan}</code></button>
                    <button type="button" class="btn btn-sm btn-white border rounded-pill shadow-xs placeholder-btn" data-tag="{tanggal}"><i class="bi bi-plus text-primary"></i> <code>{tanggal}</code></button>
                    <button type="button" class="btn btn-sm btn-white border rounded-pill shadow-xs placeholder-btn" data-tag="{jam}"><i class="bi bi-plus text-primary"></i> <code>{jam}</code></button>
                    <button type="button" class="btn btn-sm btn-white border rounded-pill shadow-xs placeholder-btn" data-tag="{status}"><i class="bi bi-plus text-primary"></i> <code>{status}</code></button>
                    <button type="button" class="btn btn-sm btn-white border rounded-pill shadow-xs placeholder-btn" data-tag="{nama_puskesmas}"><i class="bi bi-plus text-primary"></i> <code>{nama_puskesmas}</code></button>
                    <button type="button" class="btn btn-sm btn-white border rounded-pill shadow-xs placeholder-btn" data-tag="{catatan}"><i class="bi bi-plus text-primary"></i> <code>{catatan}</code></button>
                </div>
            </div>

            <!-- Accordion Template Peristiwa -->
            <div class="accordion" id="accordionTemplates">
                <!-- 1. Pendaftaran Berhasil -->
                <div class="accordion-item border rounded-3 mb-2 overflow-hidden">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button py-3 fw-semibold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            <i class="bi bi-ticket-perforated-fill text-success me-2 fs-5"></i>
                            1. Template Pendaftaran Berhasil & Terbit Nomor
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionTemplates">
                        <div class="accordion-body p-3 bg-light-subtle">
                            <textarea id="tpl_registration" name="template_registration" class="form-control font-monospace text-dark template-box" rows="6"><?= htmlspecialchars((string)($settings['template_registration'] ?? '')) ?></textarea>
                            <small class="text-muted mt-1 d-block">Pesan terkirim otomatis saat pasien berhasil menyelesaikan booking antrean.</small>
                        </div>
                    </div>
                </div>

                <!-- 2. Perubahan Status -->
                <div class="accordion-item border rounded-3 mb-2 overflow-hidden">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed py-3 fw-semibold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            <i class="bi bi-arrow-repeat text-info me-2 fs-5"></i>
                            2. Template Perubahan Status Pendaftaran
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionTemplates">
                        <div class="accordion-body p-3 bg-light-subtle">
                            <textarea id="tpl_status" name="template_status_change" class="form-control font-monospace text-dark template-box" rows="5"><?= htmlspecialchars((string)($settings['template_status_change'] ?? '')) ?></textarea>
                            <small class="text-muted mt-1 d-block">Pesan dikirim saat status antrean diubah oleh petugas medis atau administrasi.</small>
                        </div>
                    </div>
                </div>

                <!-- 3. Pengingat H-1 -->
                <div class="accordion-item border rounded-3 mb-2 overflow-hidden">
                    <h2 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed py-3 fw-semibold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            <i class="bi bi-calendar-event text-warning me-2 fs-5"></i>
                            3. Template Pengingat Jadwal Kunjungan (H-1)
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionTemplates">
                        <div class="accordion-body p-3 bg-light-subtle">
                            <textarea id="tpl_h1" name="template_reminder_h1" class="form-control font-monospace text-dark template-box" rows="5"><?= htmlspecialchars((string)($settings['template_reminder_h1'] ?? '')) ?></textarea>
                            <small class="text-muted mt-1 d-block">Scheduler backend mengirim pesan ini secara otomatis 1 hari sebelum kunjungan.</small>
                        </div>
                    </div>
                </div>

                <!-- 4. Pengingat H-2 Jam -->
                <div class="accordion-item border rounded-3 mb-2 overflow-hidden">
                    <h2 class="accordion-header" id="headingFour">
                        <button class="accordion-button collapsed py-3 fw-semibold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                            <i class="bi bi-alarm-fill text-danger me-2 fs-5"></i>
                            4. Template Pengingat 2 Jam Sebelum Kunjungan
                        </button>
                    </h2>
                    <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionTemplates">
                        <div class="accordion-body p-3 bg-light-subtle">
                            <textarea id="tpl_h2" name="template_reminder_h2" class="form-control font-monospace text-dark template-box" rows="5"><?= htmlspecialchars((string)($settings['template_reminder_h2'] ?? '')) ?></textarea>
                            <small class="text-muted mt-1 d-block">Pengingat segera agar pasien dapat mempersiapkan diri menuju Puskesmas.</small>
                        </div>
                    </div>
                </div>

                <!-- 5. Perubahan Jadwal -->
                <div class="accordion-item border rounded-3 mb-2 overflow-hidden">
                    <h2 class="accordion-header" id="headingFive">
                        <button class="accordion-button collapsed py-3 fw-semibold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                            <i class="bi bi-calendar2-range text-secondary me-2 fs-5"></i>
                            5. Template Perubahan Jadwal Layanan / Dokter
                        </button>
                    </h2>
                    <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#accordionTemplates">
                        <div class="accordion-body p-3 bg-light-subtle">
                            <textarea id="tpl_sched" name="template_schedule_change" class="form-control font-monospace text-dark template-box" rows="5"><?= htmlspecialchars((string)($settings['template_schedule_change'] ?? '')) ?></textarea>
                            <small class="text-muted mt-1 d-block">Pemberitahuan perubahan jam praktek dokter atau penyesuaian layanan.</small>
                        </div>
                    </div>
                </div>

                <!-- 6. Pembatalan -->
                <div class="accordion-item border rounded-3 mb-2 overflow-hidden">
                    <h2 class="accordion-header" id="headingSix">
                        <button class="accordion-button collapsed py-3 fw-semibold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                            <i class="bi bi-x-circle text-danger me-2 fs-5"></i>
                            6. Template Pembatalan Pendaftaran
                        </button>
                    </h2>
                    <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#accordionTemplates">
                        <div class="accordion-body p-3 bg-light-subtle">
                            <textarea id="tpl_cancel" name="template_cancellation" class="form-control font-monospace text-dark template-box" rows="5"><?= htmlspecialchars((string)($settings['template_cancellation'] ?? '')) ?></textarea>
                            <small class="text-muted mt-1 d-block">Notifikasi saat tiket dibatalkan dan kuota antrean dikembalikan.</small>
                        </div>
                    </div>
                </div>

                <!-- 7. Pengumuman -->
                <div class="accordion-item border rounded-3 mb-2 overflow-hidden">
                    <h2 class="accordion-header" id="headingSeven">
                        <button class="accordion-button collapsed py-3 fw-semibold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
                            <i class="bi bi-megaphone-fill text-primary me-2 fs-5"></i>
                            7. Template Pengumuman Resmi Puskesmas
                        </button>
                    </h2>
                    <div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="headingSeven" data-bs-parent="#accordionTemplates">
                        <div class="accordion-body p-3 bg-light-subtle">
                            <textarea id="tpl_ann" name="template_announcement" class="form-control font-monospace text-dark template-box" rows="5"><?= htmlspecialchars((string)($settings['template_announcement'] ?? '')) ?></textarea>
                            <small class="text-muted mt-1 d-block">Format siaran informasi kesehatan massal oleh petugas Puskesmas.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Simpan Semua Template -->
            <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                    <i class="bi bi-check-lg me-1"></i> Simpan Seluruh Pengaturan & Template
                </button>
            </div>
        </div>
    </form>

    <!-- Hidden Form for Quick Connection Test -->
    <form id="formTestConnQuick" action="<?= App::baseUrl('/admin/pengaturan/whatsapp/test-connection') ?>" method="POST" class="d-none">
        <?= \App\Core\Csrf::field() ?>
    </form>

    <!-- 6. CARD PENGUJIAN PENGIRIMAN WHATSAPP -->
    <div id="section-test-send" class="card border shadow-sm rounded-4 bg-white p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold text-dark mb-1">
                    <i class="bi bi-send-check text-success me-2"></i>Uji Pengiriman Pesan WhatsApp
                </h5>
                <p class="text-muted small mb-0">
                    Kirimkan pesan uji coba langsung ke nomor WhatsApp Anda untuk memverifikasi fungsionalitas pengiriman Fonnte Gateway.
                </p>
            </div>
        </div>

        <form action="<?= App::baseUrl('/admin/pengaturan/whatsapp/test-send') ?>" method="POST">
            <?= \App\Core\Csrf::field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-dark mb-1">Nomor WhatsApp Tujuan</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-telephone-fill"></i></span>
                        <input type="text" name="phone" class="form-control" placeholder="Contoh: 081234567890" required>
                    </div>
                    <small class="text-muted" style="font-size: 0.72rem;">Format lokal (08xxx) atau internasional (628xxx).</small>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-dark mb-1">Jenis Notifikasi Uji</label>
                    <select name="event_type" class="form-select">
                        <option value="registration">1. Pendaftaran Berhasil</option>
                        <option value="status_change">2. Perubahan Status</option>
                        <option value="reminder_h1">3. Pengingat Kunjungan (H-1)</option>
                        <option value="reminder_h2">4. Pengingat Kunjungan (H-2 Jam)</option>
                        <option value="schedule_change">5. Perubahan Jadwal</option>
                        <option value="cancellation">6. Pembatalan Antrean</option>
                        <option value="announcement">7. Pengumuman Admin</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-dark mb-1">Nama Pasien Uji</label>
                    <input type="text" name="patient_name" class="form-control" value="Pasien Uji Coba">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100 rounded-pill py-2 fw-semibold shadow-sm">
                        <i class="bi bi-send-fill me-1"></i> Kirim Uji
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- 7. CARD TABEL RIWAYAT AUDIT PENGIRIMAN WHATSAPP -->
    <div class="card border shadow-sm rounded-4 bg-white p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div>
                <h5 class="fw-bold text-dark mb-1">
                    <i class="bi bi-clock-history text-primary me-2"></i>Riwayat Pengiriman Notifikasi WhatsApp
                </h5>
                <p class="text-muted small mb-0">
                    Log audit real-time pengiriman pesan dengan nomor tersamar (data privacy compliance).
                </p>
            </div>

            <!-- Filter Bar: Tanggal, Event, Status -->
            <form action="<?= App::baseUrl('/admin/pengaturan/whatsapp') ?>" method="GET" class="d-flex gap-2 flex-wrap">
                <!-- Filter Tanggal -->
                <div class="input-group input-group-sm" style="width: 170px;">
                    <span class="input-group-text bg-light"><i class="bi bi-calendar3"></i></span>
                    <input type="date" name="date" class="form-control form-control-sm" value="<?= htmlspecialchars($dateFilter) ?>" title="Filter Tanggal">
                </div>

                <!-- Filter Event -->
                <select name="type" class="form-select form-select-sm rounded-pill" style="width: 160px;">
                    <option value="">Semua Event</option>
                    <option value="registration" <?= $typeFilter === 'registration' ? 'selected' : '' ?>>Pendaftaran</option>
                    <option value="status_change" <?= $typeFilter === 'status_change' ? 'selected' : '' ?>>Perubahan Status</option>
                    <option value="reminder_h1" <?= $typeFilter === 'reminder_h1' ? 'selected' : '' ?>>Pengingat H-1</option>
                    <option value="reminder_h2" <?= $typeFilter === 'reminder_h2' ? 'selected' : '' ?>>Pengingat H-2</option>
                    <option value="schedule_change" <?= $typeFilter === 'schedule_change' ? 'selected' : '' ?>>Perubahan Jadwal</option>
                    <option value="cancellation" <?= $typeFilter === 'cancellation' ? 'selected' : '' ?>>Pembatalan</option>
                    <option value="announcement" <?= $typeFilter === 'announcement' ? 'selected' : '' ?>>Pengumuman</option>
                </select>

                <!-- Filter Status -->
                <select name="status" class="form-select form-select-sm rounded-pill" style="width: 140px;">
                    <option value="">Semua Status</option>
                    <option value="sent" <?= $statusFilter === 'sent' ? 'selected' : '' ?>>Sent</option>
                    <option value="delivered" <?= $statusFilter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="read" <?= $statusFilter === 'read' ? 'selected' : '' ?>>Read</option>
                    <option value="failed" <?= $statusFilter === 'failed' ? 'selected' : '' ?>>Failed</option>
                    <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    <option value="skipped" <?= $statusFilter === 'skipped' ? 'selected' : '' ?>>Skipped</option>
                </select>

                <button type="submit" class="btn btn-sm btn-dark rounded-pill px-3">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
                <?php if (!empty($dateFilter) || !empty($typeFilter) || !empty($statusFilter)): ?>
                    <a href="<?= App::baseUrl('/admin/pengaturan/whatsapp') ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-2" title="Reset Filter">
                        <i class="bi bi-x-lg"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($logs)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                <p class="mb-0 fw-semibold">Tidak ada riwayat pengiriman yang cocok dengan kriteria filter.</p>
                <small class="text-muted">Coba ubah filter atau kirim pesan pengujian pertama Anda.</small>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light text-muted">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Waktu Kirim</th>
                            <th>Nomor Tujuan (Masked)</th>
                            <th>Penerima</th>
                            <th>Jenis Event</th>
                            <th>Ringkasan Pesan</th>
                            <th>Status</th>
                            <th>Message ID</th>
                            <th>Keterangan / Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $row): ?>
                            <tr>
                                <td class="fw-semibold text-muted">#<?= (int)$row['id'] ?></td>
                                <td class="text-nowrap">
                                    <div class="fw-semibold text-dark"><?= date('d/m/Y', strtotime($row['created_at'])) ?></div>
                                    <small class="text-muted"><?= date('H:i:s', strtotime($row['created_at'])) ?> WIB</small>
                                </td>
                                <td>
                                    <span class="font-monospace fw-semibold text-dark bg-light px-2 py-1 rounded">
                                        <?= htmlspecialchars((string)$row['recipient_number_masked']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($row['user_name'])): ?>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['user_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($row['user_email'] ?? '') ?></small>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2">Uji Sistem / Admin</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= htmlspecialchars((string)$row['message_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="font-monospace text-muted small" title="<?= htmlspecialchars((string)($row['payload_summary'] ?? '')) ?>">
                                        <?= htmlspecialchars(mb_strimwidth((string)($row['template_name'] ?: $row['payload_summary'] ?: '-'), 0, 28, '...')) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $st = (string)$row['delivery_status'];
                                    $badgeClass = match ($st) {
                                        'read'      => 'bg-info text-white',
                                        'delivered' => 'bg-primary text-white',
                                        'sent'      => 'bg-success text-white',
                                        'rejected'  => 'bg-warning text-dark',
                                        'failed'    => 'bg-danger text-white',
                                        'skipped'   => 'bg-secondary text-white',
                                        default     => 'bg-secondary text-white',
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?> rounded-pill px-2 py-1">
                                        <?= ucfirst($st) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="font-monospace text-muted small">
                                        <?= htmlspecialchars(mb_strimwidth((string)($row['message_id'] ?: '-'), 0, 16, '...')) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($row['error_message'])): ?>
                                        <span class="text-danger small" title="<?= htmlspecialchars($row['error_message']) ?>">
                                            <i class="bi bi-exclamation-circle me-1"></i>
                                            <?= htmlspecialchars(mb_strimwidth($row['error_message'], 0, 30, '...')) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-success small"><i class="bi bi-check-circle me-1"></i> Sukses</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Nav -->
            <?php if ($totalPages > 1): ?>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3 border-top mt-3">
                    <small class="text-muted">
                        Menampilkan halaman <strong><?= $page ?></strong> dari <strong><?= $totalPages ?></strong> (Total <?= number_format($totalLogs) ?> pesan)
                    </small>
                    <nav aria-label="Navigasi Halaman Log">
                        <ul class="pagination pagination-sm mb-0">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if ($i === 1 || $i === $totalPages || abs($i - $page) <= 2): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= App::baseUrl('/admin/pengaturan/whatsapp?page=' . $i . '&status=' . urlencode($statusFilter) . '&type=' . urlencode($typeFilter) . '&date=' . urlencode($dateFilter)) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php elseif (abs($i - $page) === 3): ?>
                                    <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- CLIENT JAVASCRIPT: TOKEN SHOW/HIDE & PLACEHOLDER INSERTION -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Fitur Tampilkan / Sembunyikan API Key Token
    const tokenInput = document.getElementById('fonnte_token');
    const btnToggle = document.getElementById('btnToggleToken');
    const toggleIcon = document.getElementById('toggleIcon');

    if (btnToggle && tokenInput && toggleIcon) {
        btnToggle.addEventListener('click', function () {
            if (tokenInput.type === 'password') {
                tokenInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                tokenInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        });
    }

    // 2. Fitur Sisipkan Placeholder ke Textarea Template Aktif
    let lastActiveTextarea = document.querySelector('.template-box');

    document.querySelectorAll('.template-box').forEach(function (tx) {
        tx.addEventListener('focus', function () {
            lastActiveTextarea = this;
        });
    });

    document.querySelectorAll('.placeholder-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const tag = this.getAttribute('data-tag');
            if (!lastActiveTextarea) {
                lastActiveTextarea = document.querySelector('.template-box');
            }
            if (lastActiveTextarea && tag) {
                const start = lastActiveTextarea.selectionStart || lastActiveTextarea.value.length;
                const end = lastActiveTextarea.selectionEnd || lastActiveTextarea.value.length;
                const text = lastActiveTextarea.value;
                lastActiveTextarea.value = text.substring(0, start) + tag + text.substring(end);
                lastActiveTextarea.focus();
                lastActiveTextarea.selectionStart = lastActiveTextarea.selectionEnd = start + tag.length;
            }
        });
    });
});
</script>
