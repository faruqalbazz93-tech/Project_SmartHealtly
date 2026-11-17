<?php
use App\Config\App;
use App\Core\Session;
use App\Core\Env;
?>

<div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5 mb-4">
    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div class="bg-primary text-white p-3 rounded-4 fs-3">
            <i class="bi bi-shield-check"></i>
        </div>
        <div>
            <h4 class="fw-bold text-dark mb-1">Pusat Verifikasi Akun Pasien</h4>
            <p class="text-muted small mb-0">Lengkapi verifikasi identitas untuk mengamankan data rekam medis Anda di Puskesmas Astanajapura</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- 1. WhatsApp Verification Card -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 <?= !empty($user['phone_verified_at']) ? 'bg-success-subtle border border-success-subtle' : 'bg-light border' ?>">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-whatsapp text-success fs-3"></i>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Nomor WhatsApp</h6>
                            <small class="text-muted"><?= htmlspecialchars($user['phone']) ?></small>
                        </div>
                    </div>
                    <?php if (!empty($user['phone_verified_at'])): ?>
                        <span class="badge bg-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> Terverifikasi</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="bi bi-exclamation-circle-fill me-1"></i> Belum Terverifikasi</span>
                    <?php endif; ?>
                </div>

                <p class="small text-muted mb-4">
                    Nomor WhatsApp digunakan untuk pengiriman nomor antrean resmi, notifikasi pemanggilan poli, dan reset kata sandi.
                </p>

                <div class="mt-auto">
                    <?php if (empty($user['phone_verified_at'])): ?>
                        <div class="d-flex gap-2">
                            <a href="<?= App::baseUrl('/verifikasi/otp') ?>" class="btn btn-success btn-sm rounded-pill px-3 fw-bold">
                                <i class="bi bi-key-fill me-1"></i> Masukkan Kode OTP
                            </a>
                            <form action="<?= App::baseUrl('/verifikasi/otp/resend') ?>" method="POST" class="d-inline">
                                <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                                <button type="submit" class="btn btn-outline-success btn-sm rounded-pill px-3">
                                    <i class="bi bi-arrow-repeat"></i> Kirim Ulang OTP
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <small class="text-success fw-semibold"><i class="bi bi-check2 me-1"></i> Diverifikasi pada <?= date('d M Y H:i', strtotime($user['phone_verified_at'])) ?></small>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 2. Email Verification Card -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 <?= !empty($user['email_verified_at']) ? 'bg-success-subtle border border-success-subtle' : 'bg-light border' ?>">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-envelope text-primary fs-3"></i>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Alamat Email</h6>
                            <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
                        </div>
                    </div>
                    <?php if (!empty($user['email_verified_at'])): ?>
                        <span class="badge bg-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> Terverifikasi</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="bi bi-exclamation-circle-fill me-1"></i> Belum Terverifikasi</span>
                    <?php endif; ?>
                </div>

                <p class="small text-muted mb-4">
                    Email digunakan untuk bukti E-Ticket digital pemeriksaan, materi edukasi kesehatan, dan pemulihan keamanan akun.
                </p>

                <div class="mt-auto">
                    <?php if (empty($user['email_verified_at'])): ?>
                        <form action="<?= App::baseUrl('/verifikasi/email/resend') ?>" method="POST" class="d-inline">
                            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
                                <i class="bi bi-send-fill me-1"></i> Kirim Ulang Email Verifikasi
                            </button>
                        </form>
                    <?php else: ?>
                        <small class="text-success fw-semibold"><i class="bi bi-check2 me-1"></i> Diverifikasi pada <?= date('d M Y H:i', strtotime($user['email_verified_at'])) ?></small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Development Mock Simulator Notice -->
    <?php if (Env::get('APP_ENV') === 'development'): ?>
        <div class="mt-4 pt-3 border-top">
            <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-laptop text-info me-1"></i> Development Simulator (Local Dev Mode):</h6>
            <div class="row g-2">
                <?php if (!empty($lastMockWa)): ?>
                    <div class="col-md-6">
                        <div class="p-3 bg-dark text-white rounded-3 small font-monospace">
                            <strong class="text-warning">Pesan WhatsApp Terakhir:</strong>
                            <p class="mb-1 text-white-75" style="font-size: 0.75rem; white-space: pre-line;"><?= htmlspecialchars($lastMockWa['message']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($lastMockMail)): ?>
                    <div class="col-md-6">
                        <div class="p-3 bg-dark text-white rounded-3 small font-monospace">
                            <strong class="text-info">Email Terakhir:</strong>
                            <p class="mb-1 text-white-75" style="font-size: 0.75rem; white-space: pre-line;"><?= htmlspecialchars($lastMockMail['body']) ?></p>
                            <?php if (!empty($lastMockMail['meta']['verify_url'])): ?>
                                <a href="<?= htmlspecialchars($lastMockMail['meta']['verify_url']) ?>" class="btn btn-sm btn-info rounded-pill py-0" style="font-size: 0.75rem;">
                                    Klik Verifikasi Email Ini
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="mt-4 pt-3 border-top text-end">
        <a href="<?= App::baseUrl('/dashboard') ?>" class="btn btn-outline-primary rounded-pill px-4 btn-sm">
            <i class="bi bi-house me-1"></i> Kembali ke Dashboard
        </a>
    </div>
</div>
