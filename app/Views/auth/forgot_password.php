<?php
use App\Config\App;
use App\Core\Session;
use App\Core\Env;
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
            <div class="text-center mb-4">
                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow-xs" style="width: 60px; height: 60px;">
                    <i class="bi bi-key-fill fs-3"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1">Lupa Kata Sandi?</h4>
                <p class="text-muted small mb-0">Masukkan email Anda untuk menerima tautan pemulihan kata sandi</p>
            </div>

            <form action="<?= App::baseUrl('/lupa-password') ?>" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-dark">Alamat Email Terdaftar</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                        <input type="email" name="email" class="form-control bg-light border-start-0" placeholder="nama@email.com" required autofocus>
                    </div>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary py-2 rounded-pill fw-bold shadow-sm">
                        <i class="bi bi-send-fill me-1"></i> Kirim Tautan Reset Sandi
                    </button>
                </div>
            </form>

            <!-- Dev mode simulator link -->
            <?php 
            $lastMail = \App\Services\MailService::getLastMockMail();
            if (Env::get('APP_ENV') === 'development' && !empty($lastMail) && ($lastMail['meta']['type'] ?? '') === 'password_reset'): 
            ?>
                <div class="mt-4 p-3 bg-dark text-white rounded-3 small">
                    <span class="badge bg-warning text-dark mb-1">Dev Mock: Tautan Reset Terkirim</span>
                    <p class="mb-1 text-white-50" style="font-size: 0.75rem;">Klik untuk langsung membuka form reset:</p>
                    <a href="<?= htmlspecialchars($lastMail['meta']['reset_url']) ?>" class="btn btn-sm btn-outline-warning rounded-pill py-0" style="font-size: 0.75rem;">
                        Buka Form Reset Password &rarr;
                    </a>
                </div>
            <?php endif; ?>

            <div class="text-center mt-4 pt-2 border-top">
                <p class="small text-muted mb-0">
                    Ingat kata sandi Anda? 
                    <a href="<?= App::baseUrl('/login') ?>" class="text-primary fw-bold text-decoration-none">Kembali ke Halaman Masuk</a>
                </p>
            </div>
        </div>
    </div>
</div>
