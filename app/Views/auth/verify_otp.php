<?php
use App\Config\App;
use App\Core\Session;
use App\Core\Env;
?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
        <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
            <div class="text-center mb-4">
                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow" style="width: 64px; height: 64px;">
                    <i class="bi bi-whatsapp fs-2"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1">Verifikasi Nomor WhatsApp</h4>
                <p class="text-muted small mb-0">
                    Masukkan 6-digit kode OTP yang telah dikirimkan ke nomor WhatsApp:<br>
                    <strong class="text-dark"><?= htmlspecialchars($user['phone']) ?></strong>
                </p>
            </div>

            <!-- OTP Form -->
            <form action="<?= App::baseUrl('/verifikasi/otp') ?>" method="POST" id="otpForm">
                <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">

                <div class="mb-4 text-center">
                    <label class="form-label small fw-semibold text-muted d-block mb-2">Kode OTP (6 Digit Angka)</label>
                    <input type="text" name="otp" id="otpInput" maxlength="6" class="form-control form-control-lg text-center font-monospace fw-bold fs-2 bg-light border-2" placeholder="------" style="letter-spacing: 12px;" required autofocus autocomplete="one-time-code">
                </div>

                <!-- Expiry Timer & Attempts Info -->
                <div class="d-flex justify-content-between align-items-center small text-muted mb-4 px-2">
                    <div>
                        <i class="bi bi-clock-history me-1"></i>
                        Masa berlaku: <strong id="countdownTimer" class="text-danger">05:00</strong>
                    </div>
                    <div>
                        Percobaan: <span class="badge bg-secondary-subtle text-dark"><?= (int)($activeOtp['attempts'] ?? 0) ?>/<?= (int)($activeOtp['max_attempts'] ?? 3) ?></span>
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-success py-2 rounded-pill fw-bold shadow-sm">
                        <i class="bi bi-check-circle-fill me-1"></i> Verifikasi Kode OTP
                    </button>
                </div>
            </form>

            <!-- Resend OTP Form -->
            <form action="<?= App::baseUrl('/verifikasi/otp/resend') ?>" method="POST" class="text-center">
                <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                <p class="small text-muted mb-1">Tidak menerima kode OTP?</p>
                <button type="submit" class="btn btn-link btn-sm text-primary text-decoration-none fw-bold p-0" id="resendBtn">
                    <i class="bi bi-arrow-repeat me-1"></i> Kirim Ulang Kode OTP
                </button>
            </form>

            <!-- Development Mock Simulator Card -->
            <?php if (Env::get('APP_ENV') === 'development' && !empty($lastMockWa)): ?>
                <div class="mt-4 pt-3 border-top">
                    <div class="card bg-dark text-white border-0 rounded-3 p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-warning text-dark small"><i class="bi bi-code-slash me-1"></i> Development Mock Simulator</span>
                            <small class="text-white-50"><?= date('H:i:s', strtotime($lastMockWa['sent_at'] ?? 'now')) ?></small>
                        </div>
                        <p class="small text-white-75 mb-2 font-monospace" style="font-size: 0.75rem;">
                            OTP Dikirim: <strong class="text-warning fs-6"><?= htmlspecialchars($lastMockWa['meta']['otp_code'] ?? '123456') ?></strong>
                        </p>
                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill py-0 small" onclick="document.getElementById('otpInput').value = '<?= htmlspecialchars($lastMockWa['meta']['otp_code'] ?? '') ?>';">
                            Isi OTP Ini Otomatis
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <div class="text-center mt-4 pt-2 border-top">
                <a href="<?= App::baseUrl('/dashboard') ?>" class="small text-muted text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i> Lanjutkan ke Dashboard (Status Belum Terverifikasi)
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Expiry Countdown Timer
document.addEventListener('DOMContentLoaded', () => {
    let timeLeft = 300; // 5 minutes in seconds
    const timerDisplay = document.getElementById('countdownTimer');

    const interval = setInterval(() => {
        if (timeLeft <= 0) {
            clearInterval(interval);
            timerDisplay.textContent = 'Kedaluwarsa';
            timerDisplay.classList.add('text-danger');
        } else {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            timeLeft--;
        }
    }, 1000);
});
</script>
