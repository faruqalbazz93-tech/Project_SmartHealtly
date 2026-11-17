<?php
use App\Config\App;
use App\Core\Session;
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
            <div class="text-center mb-4">
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-semibold mb-2">Puskesmas Astanajapura</span>
                <h3 class="fw-bold text-dark mb-1">Masuk ke Akun Pasien</h3>
                <p class="text-muted small">Akses pendaftaran layanan & tiket antrean Anda</p>
            </div>

            <form id="loginForm" action="<?= App::baseUrl('/login') ?>" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-dark">Email / No WhatsApp / NIK</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                        <input type="text" id="email_or_nik" name="email_or_nik" class="form-control bg-light" placeholder="nama@email.com / 08xxx / 3209..." required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small fw-semibold text-dark mb-0">Kata Sandi</label>
                        <a href="<?= App::baseUrl('/lupa-password') ?>" class="small text-primary text-decoration-none fw-semibold">Lupa Kata Sandi?</a>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-lock"></i></span>
                        <input type="password" id="password" name="password" class="form-control bg-light" placeholder="Masukkan kata sandi" required>
                        <button type="button" class="btn btn-outline-secondary" id="btnToggleLoginPass" title="Lihat/Sembunyikan Sandi">
                            <i class="bi bi-eye" id="toggleLoginIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary py-2 rounded-pill fw-bold shadow-sm" id="btnSubmitLogin">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
                    </button>
                </div>
            </form>

            <!-- Pengaturan Sandi & Akun Otomatis (Demo / Testing) -->
            <div class="mt-4 pt-3 border-top">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">Pengaturan Sandi Otomatis:</span>
                    <span class="badge bg-success-subtle text-success small rounded-pill px-2">Siap Pakai</span>
                </div>
                
                <div class="row g-2 mb-2">
                    <!-- Admin Auto Fill & Auto Login -->
                    <div class="col-6">
                        <div class="card border rounded-3 p-2 bg-light text-center h-100">
                            <div class="small fw-bold text-dark mb-1"><i class="bi bi-shield-lock-fill text-warning me-1"></i>Administrator</div>
                            <div class="d-grid gap-1">
                                <button type="button" class="btn btn-primary btn-sm rounded-pill fw-semibold py-1" onclick="quickLogin('admin@smarthealthy.id', 'Astanajapura@2026!')" title="Otomatis isi dan masuk sebagai Admin">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Admin
                                </button>
                                <button type="button" class="btn btn-outline-dark btn-sm rounded-pill py-0" style="font-size: 0.7rem;" onclick="fillCredentials('admin@smarthealthy.id', 'Astanajapura@2026!')">
                                    Isi Sandi Saja
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Pasien Auto Fill & Auto Login -->
                    <div class="col-6">
                        <div class="card border rounded-3 p-2 bg-light text-center h-100">
                            <div class="small fw-bold text-dark mb-1"><i class="bi bi-person-fill text-success me-1"></i>Akun Pasien</div>
                            <div class="d-grid gap-1">
                                <button type="button" class="btn btn-success btn-sm rounded-pill fw-semibold py-1" onclick="quickLogin('pasien@smarthealthy.id', 'SehatAstanajapura#2026')" title="Otomatis isi dan masuk sebagai Pasien">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Pasien
                                </button>
                                <button type="button" class="btn btn-outline-dark btn-sm rounded-pill py-0" style="font-size: 0.7rem;" onclick="fillCredentials('pasien@smarthealthy.id', 'SehatAstanajapura#2026')">
                                    Isi Sandi Saja
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <small class="text-muted d-block text-center" style="font-size: 0.7rem;">
                    Klik tombol di atas untuk mengisi kredensial dan kata sandi secara otomatis.
                </small>
            </div>

            <div class="text-center mt-3 pt-2">
                <p class="small text-muted mb-0">
                    Belum memiliki akun pasien? 
                    <a href="<?= App::baseUrl('/register') ?>" class="text-primary fw-bold text-decoration-none">Daftar Akun Baru</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function fillCredentials(email, password) {
    const emailInput = document.getElementById('email_or_nik');
    const passInput = document.getElementById('password');
    emailInput.value = email;
    passInput.value = password;
    passInput.type = 'text';
    document.getElementById('toggleLoginIcon').className = 'bi bi-eye-slash text-primary';
}

function quickLogin(email, password) {
    fillCredentials(email, password);
    const btn = document.getElementById('btnSubmitLogin');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Memproses Masuk...';
    document.getElementById('loginForm').submit();
}

document.getElementById('btnToggleLoginPass').addEventListener('click', function() {
    const passInput = document.getElementById('password');
    const icon = document.getElementById('toggleLoginIcon');
    if (passInput.type === 'password') {
        passInput.type = 'text';
        icon.className = 'bi bi-eye-slash text-primary';
    } else {
        passInput.type = 'password';
        icon.className = 'bi bi-eye text-muted';
    }
});
</script>
