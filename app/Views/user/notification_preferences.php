<?php
use App\Config\App;
use App\Core\Session;

$user = $user ?? [];
$preference = $preference ?? [];
$logs = $logs ?? [];
$isPhoneVerified = !empty($user['phone_verified_at']);
$isWhatsAppEnabled = !empty($preference['whatsapp_enabled']);
?>

<div class="container py-4">
    <!-- Header Section -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 small fw-semibold">
                        <i class="bi bi-whatsapp me-1"></i> WhatsApp Cloud API
                    </span>
                    <span class="text-muted small">&bull; Puskesmas Astanajapura</span>
                </div>
                <h3 class="fw-bold text-dark mb-1">
                    <i class="bi bi-sliders text-success me-2"></i>Pengaturan Notifikasi WhatsApp
                </h3>
                <p class="text-muted mb-0 small">
                    Kelola preferensi dan izin penerimaan tiket pendaftaran antrean dan pengingat kunjungan Puskesmas via WhatsApp resmi.
                </p>
            </div>
            <div>
                <a href="<?= App::baseUrl('/notifikasi') ?>" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-bell me-1"></i> Pusat Notifikasi
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($successMessages = Session::getFlash('success')): ?>
        <?php foreach ((array)$successMessages as $msg): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-check-circle-fill fs-5"></i>
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

    <?php if ($warningMessages = Session::getFlash('warning')): ?>
        <?php foreach ((array)$warningMessages as $msg): ?>
            <div class="alert alert-warning alert-dismissible fade show rounded-3 shadow-sm d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
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
            <div class="alert alert-info alert-dismissible fade show rounded-3 shadow-sm d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-info-circle-fill fs-5"></i>
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

    <div class="row g-4">
        <!-- Left Column: Status Nomor & Persetujuan (Consent) -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-shield-check text-primary me-2"></i>Status Nomor & Izin Notifikasi
                    </h5>
                    <p class="text-muted small mb-0">Verifikasi nomor WhatsApp dan persetujuan pengiriman pesan resmi.</p>
                </div>
                <div class="card-body p-4">
                    <!-- Nomor WhatsApp -->
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3 mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-telephone-fill fs-5"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Nomor WhatsApp Terdaftar</small>
                                <strong class="fs-6 text-dark"><?= htmlspecialchars((string)($user['phone'] ?? '-')) ?></strong>
                            </div>
                        </div>
                        <div>
                            <?php if ($isPhoneVerified): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 small">
                                    <i class="bi bi-patch-check-fill me-1"></i> Terverifikasi
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-2 small">
                                    <i class="bi bi-exclamation-circle me-1"></i> Belum Diverifikasi
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!$isPhoneVerified): ?>
                        <div class="alert alert-warning border-0 rounded-3 mb-4 small">
                            <div class="d-flex gap-2">
                                <i class="bi bi-shield-exclamation fs-5 flex-shrink-0"></i>
                                <div>
                                    <strong>Nomor WhatsApp Belum Terverifikasi</strong><br>
                                    Demi keamanan privasi data rekam medis Anda, pesan WhatsApp hanya dapat dikirimkan ke nomor yang telah terverifikasi via kode OTP.
                                    <div class="mt-2">
                                        <a href="<?= App::baseUrl('/verifikasi/otp') ?>" class="btn btn-warning btn-sm fw-semibold rounded-pill px-3">
                                            <i class="bi bi-key me-1"></i> Verifikasi Nomor Sekarang
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Status Persetujuan Notifikasi (Consent) -->
                    <div class="border rounded-3 p-3 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold text-dark">Status Izin WhatsApp (Opt-in Consent)</span>
                            <?php if ($isWhatsAppEnabled): ?>
                                <span class="badge bg-success rounded-pill px-3 py-1">
                                    <i class="bi bi-check-lg me-1"></i> Aktif
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary rounded-pill px-3 py-1">
                                    <i class="bi bi-x-circle me-1"></i> Belum Aktif
                                </span>
                            <?php endif; ?>
                        </div>

                        <p class="text-muted small mb-3">
                            Berdasarkan kebijakan Meta WhatsApp Business Platform dan perlindungan data pribadi, SmartHealthy hanya mengirim pesan jika Anda memberikan persetujuan eksplisit.
                        </p>

                        <?php if ($isWhatsAppEnabled && !empty($preference['whatsapp_consent_at'])): ?>
                            <div class="text-muted small mb-3">
                                <i class="bi bi-calendar-check me-1"></i> Persetujuan diberikan pada: 
                                <strong><?= date('d F Y, H:i', strtotime($preference['whatsapp_consent_at'])) ?> WIB</strong>
                            </div>
                        <?php elseif (!empty($preference['whatsapp_consent_revoked_at'])): ?>
                            <div class="text-muted small mb-3">
                                <i class="bi bi-x-circle me-1"></i> Persetujuan dicabut pada: 
                                <strong><?= date('d F Y, H:i', strtotime($preference['whatsapp_consent_revoked_at'])) ?> WIB</strong>
                            </div>
                        <?php endif; ?>

                        <!-- Tombol Toggle Consent -->
                        <form action="<?= App::baseUrl('/pengaturan/notifikasi/consent') ?>" method="POST" class="d-inline">
                            <?= \App\Core\Csrf::field() ?>
                            <?php if ($isWhatsAppEnabled): ?>
                                <input type="hidden" name="action" value="disable">
                                <button type="submit" class="btn btn-outline-danger w-100 rounded-pill py-2 fw-semibold">
                                    <i class="bi bi-bell-slash me-1"></i> Nonaktifkan Notifikasi WhatsApp
                                </button>
                            <?php else: ?>
                                <input type="hidden" name="action" value="enable">
                                <button type="submit" class="btn btn-success w-100 rounded-pill py-2 fw-semibold" <?= !$isPhoneVerified ? 'disabled' : '' ?>>
                                    <i class="bi bi-whatsapp me-1"></i> Berikan Persetujuan & Aktifkan Notifikasi
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- Informasi Transparansi & Kebijakan Privasi -->
                    <div class="bg-light rounded-3 p-3 text-muted small">
                        <strong class="text-dark d-block mb-1">
                            <i class="bi bi-info-circle text-primary me-1"></i> Ketentuan Pengiriman Pesan
                        </strong>
                        <ul class="ps-3 mb-0">
                            <li>Nomor Anda tidak akan pernah dibagikan ke pihak ketiga atau pengiklan.</li>
                            <li>Pesan dikirim melalui WhatsApp Cloud API resmi berlisensi Meta.</li>
                            <li>Anda dapat mencabut izin penerimaan notifikasi kapan saja melalui halaman ini.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Pilihan Jenis Notifikasi -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-check2-square text-success me-2"></i>Pilihan Jenis Notifikasi
                    </h5>
                    <p class="text-muted small mb-0">Tentukan jenis informasi yang ingin Anda terima via WhatsApp.</p>
                </div>
                <div class="card-body p-4">
                    <form action="<?= App::baseUrl('/pengaturan/notifikasi/simpan') ?>" method="POST">
                        <?= \App\Core\Csrf::field() ?>

                        <div class="list-group list-group-flush mb-4">
                            <!-- 1. Pendaftaran Berhasil -->
                            <label class="list-group-item d-flex gap-3 py-3 border-bottom px-0">
                                <input class="form-check-input flex-shrink-0" type="checkbox" name="notify_registration" value="1"
                                    <?= (!empty($preference['notify_registration']) || !isset($preference['notify_registration'])) ? 'checked' : '' ?>
                                    style="width: 1.25em; height: 1.25em;">
                                <span>
                                    <strong class="d-block text-dark">Pendaftaran Antrean & Nomor Tiket</strong>
                                    <small class="text-muted">Notifikasi langsung ketika pendaftaran berhasil disimpan dan kode booking diterbitkan.</small>
                                </span>
                            </label>

                            <!-- 2. Perubahan Status -->
                            <label class="list-group-item d-flex gap-3 py-3 border-bottom px-0">
                                <input class="form-check-input flex-shrink-0" type="checkbox" name="notify_status_change" value="1"
                                    <?= (!empty($preference['notify_status_change']) || !isset($preference['notify_status_change'])) ? 'checked' : '' ?>
                                    style="width: 1.25em; height: 1.25em;">
                                <span>
                                    <strong class="d-block text-dark">Pembaruan Status Pendaftaran</strong>
                                    <small class="text-muted">Pemberitahuan ketika antrean Anda dikonfirmasi, dipanggil, atau selesai diperiksa dokter.</small>
                                </span>
                            </label>

                            <!-- 3. Pengingat Jadwal Kunjungan -->
                            <label class="list-group-item d-flex gap-3 py-3 border-bottom px-0">
                                <input class="form-check-input flex-shrink-0" type="checkbox" name="notify_reminder" value="1"
                                    <?= (!empty($preference['notify_reminder']) || !isset($preference['notify_reminder'])) ? 'checked' : '' ?>
                                    style="width: 1.25em; height: 1.25em;">
                                <span>
                                    <strong class="d-block text-dark">Pengingat Jadwal Kunjungan Poli</strong>
                                    <small class="text-muted">Pengingat otomatis H-1 hari (08:00 WIB) dan 2 jam sebelum layanan agar Anda tidak terlambat datang ke Puskesmas.</small>
                                </span>
                            </label>

                            <!-- 4. Perubahan Jadwal Operasional -->
                            <label class="list-group-item d-flex gap-3 py-3 border-bottom px-0">
                                <input class="form-check-input flex-shrink-0" type="checkbox" name="notify_schedule_change" value="1"
                                    <?= (!empty($preference['notify_schedule_change']) || !isset($preference['notify_schedule_change'])) ? 'checked' : '' ?>
                                    style="width: 1.25em; height: 1.25em;">
                                <span>
                                    <strong class="d-block text-dark">Penyesuaian Jadwal Dokter & Layanan</strong>
                                    <small class="text-muted">Pemberitahuan darurat bila ada perubahan jam buka poli atau dokter yang berhalangan hadir.</small>
                                </span>
                            </label>

                            <!-- 5. Pembatalan Pendaftaran -->
                            <label class="list-group-item d-flex gap-3 py-3 border-bottom px-0">
                                <input class="form-check-input flex-shrink-0" type="checkbox" name="notify_cancellation" value="1"
                                    <?= (!empty($preference['notify_cancellation']) || !isset($preference['notify_cancellation'])) ? 'checked' : '' ?>
                                    style="width: 1.25em; height: 1.25em;">
                                <span>
                                    <strong class="d-block text-dark">Pembatalan Pendaftaran Antrean</strong>
                                    <small class="text-muted">Konfirmasi resmi saat pendaftaran dibatalkan atas permintaan Anda atau oleh petugas.</small>
                                </span>
                            </label>

                            <!-- 6. Pengumuman Resmi Puskesmas -->
                            <label class="list-group-item d-flex gap-3 py-3 border-bottom px-0">
                                <input class="form-check-input flex-shrink-0" type="checkbox" name="notify_announcement" value="1"
                                    <?= (!empty($preference['notify_announcement']) || !isset($preference['notify_announcement'])) ? 'checked' : '' ?>
                                    style="width: 1.25em; height: 1.25em;">
                                <span>
                                    <strong class="d-block text-dark">Pengumuman Penting Puskesmas</strong>
                                    <small class="text-muted">Informasi program vaksinasi, posyandu balita/lansia, donor darah, dan kegiatan kesehatan masyarakat.</small>
                                </span>
                            </label>

                            <!-- 7. Tips & Edukasi Kesehatan -->
                            <label class="list-group-item d-flex gap-3 py-3 px-0">
                                <input class="form-check-input flex-shrink-0" type="checkbox" name="notify_education" value="1"
                                    <?= !empty($preference['notify_education']) ? 'checked' : '' ?>
                                    style="width: 1.25em; height: 1.25em;">
                                <span>
                                    <strong class="d-block text-dark">Edukasi Kesehatan Promkes (Opsional)</strong>
                                    <small class="text-muted">Tips pencegahan penyakit (DBD, Stunting, Hipertensi, TBC) dari tim Promkes Puskesmas Astanajapura.</small>
                                </span>
                            </label>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                                <i class="bi bi-save me-1"></i> Simpan Preferensi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Log Pengiriman WhatsApp Terakhir untuk Akun Ini -->
    <div class="card border-0 shadow-sm rounded-4 mt-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
            <h5 class="fw-bold text-dark mb-1">
                <i class="bi bi-clock-history text-secondary me-2"></i>Riwayat Notifikasi WhatsApp Anda
            </h5>
            <p class="text-muted small mb-0">Daftar pengiriman pesan resmi ke nomor WhatsApp terdaftar Anda.</p>
        </div>
        <div class="card-body p-4">
            <?php if (empty($logs)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-chat-left-dots fs-1 d-block mb-2 text-secondary opacity-50"></i>
                    Belum ada riwayat notifikasi WhatsApp yang dikirimkan ke akun Anda.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Jenis Pesan</th>
                                <th>Template Meta</th>
                                <th>Nomor Tujuan</th>
                                <th>Status Pengiriman</th>
                                <th>Waktu Kirim</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= htmlspecialchars((string)$log['message_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <code class="small text-muted"><?= htmlspecialchars((string)($log['template_name'] ?: 'pesan_umum')) ?></code>
                                    </td>
                                    <td>
                                        <span class="font-monospace small"><?= htmlspecialchars((string)$log['recipient_number_masked']) ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $st = $log['delivery_status'];
                                        $badgeClass = match ($st) {
                                            'read'      => 'bg-info text-white',
                                            'delivered' => 'bg-primary text-white',
                                            'sent'      => 'bg-success text-white',
                                            'rejected'  => 'bg-warning text-dark',
                                            'failed'    => 'bg-danger text-white',
                                            default     => 'bg-secondary text-white',
                                        };
                                        $label = match ($st) {
                                            'read'      => 'Dibaca',
                                            'delivered' => 'Diterima',
                                            'sent'      => 'Terkirim',
                                            'rejected'  => 'Ditolak (Belum Izin)',
                                            'failed'    => 'Gagal',
                                            default     => ucfirst($st),
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?> rounded-pill px-3 py-1 small">
                                            <?= $label ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        <?= date('d/m/Y H:i', strtotime($log['sent_at'])) ?> WIB
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

