<?php
use App\Config\App;
use App\Core\Session;
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
            <div class="text-center mb-4">
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-semibold mb-2">Wilayah Astanajapura</span>
                <h3 class="fw-bold text-dark mb-1">Daftar Akun Pasien Baru</h3>
                <p class="text-muted small">Registrasi akun masyarakat untuk antrean online & edukasi kesehatan</p>
            </div>

            <form action="<?= App::baseUrl('/register') ?>" method="POST" id="registerForm">
                <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">

                <!-- 1. Nama Lengkap -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-dark">Nama Lengkap Pasien <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                        <input type="text" name="name" class="form-control bg-light border-start-0" placeholder="Contoh: Ahmad Fauzi" required autofocus>
                    </div>
                </div>

                <!-- 2. Email -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-dark">Alamat Email <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                        <input type="email" name="email" class="form-control bg-light border-start-0" placeholder="nama@email.com" required>
                    </div>
                    <small class="text-muted" style="font-size: 0.75rem;">Email harus unik & aktif untuk verifikasi akun.</small>
                </div>

                <!-- 3. Nomor WhatsApp -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-dark">Nomor WhatsApp Aktif <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-whatsapp text-success"></i></span>
                        <input type="tel" name="phone" class="form-control bg-light border-start-0" placeholder="08xxxxxxxxxx" required>
                    </div>
                    <small class="text-muted" style="font-size: 0.75rem;">Kode OTP 6 digit akan dikirimkan ke nomor ini.</small>
                </div>

                <!-- 4. Pilihan Desa dari Database -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-dark">Desa Tempat Tinggal (Kec. Astanajapura) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-geo-alt text-danger"></i></span>
                        <select name="village_id" class="form-select bg-light border-start-0" required>
                            <option value="">-- Pilih Desa di Astanajapura --</option>
                            <?php foreach ($villages as $village): ?>
                                <option value="<?= $village['id'] ?>">
                                    Desa <?= htmlspecialchars($village['name']) ?> (Kodepos <?= $village['postal_code'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- 5. Password & Konfirmasi Password (Dengan Generator Otomatis) -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small fw-semibold text-dark mb-0">
                            Pengaturan Kata Sandi <span class="text-danger">*</span>
                        </label>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-0 fw-semibold" id="btnGenPassReg" style="font-size: 0.75rem;">
                            <i class="bi bi-magic me-1"></i> Buat Sandi Otomatis
                        </button>
                    </div>

                    <!-- Alert preview saat generate -->
                    <div id="genPassAlertReg" class="alert alert-success border-0 rounded-3 p-2 mb-2 d-none align-items-center justify-content-between small">
                        <div>
                            <span class="text-muted">Sandi Otomatis:</span> <strong class="font-monospace text-dark" id="genPassDisplayReg">--------</strong>
                        </div>
                        <button type="button" class="btn btn-sm btn-success rounded-pill py-0 px-2" id="btnCopyPassReg" style="font-size: 0.72rem;">
                            <i class="bi bi-clipboard me-1"></i> Salin
                        </button>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-lock"></i></span>
                                <input type="password" id="reg_password" name="password" minlength="6" class="form-control bg-light" placeholder="Kata Sandi (min 6)" required>
                                <button type="button" class="btn btn-outline-secondary" id="btnToggleRegPass1" title="Lihat/Sembunyikan Sandi">
                                    <i class="bi bi-eye" id="toggleRegIcon1"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" id="reg_password_confirm" name="password_confirm" minlength="6" class="form-control bg-light" placeholder="Ulangi Sandi" required>
                                <button type="button" class="btn btn-outline-secondary" id="btnToggleRegPass2" title="Lihat/Sembunyikan Sandi">
                                    <i class="bi bi-eye" id="toggleRegIcon2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 mb-4 border">
                    <div class="d-flex align-items-start gap-2 small text-muted">
                        <i class="bi bi-info-circle text-primary mt-1"></i>
                        <span>
                            Setelah pendaftaran, akun Anda akan berada dalam status <strong>Belum Terverifikasi</strong> hingga proses verifikasi kode OTP WhatsApp atau Email selesai.
                        </span>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary py-2 rounded-pill fw-bold shadow-sm">
                        <i class="bi bi-person-plus-fill me-1"></i> Daftar Akun Pasien
                    </button>
                </div>
            </form>

            <div class="text-center mt-4 pt-3 border-top">
                <p class="small text-muted mb-0">
                    Sudah memiliki akun? 
                    <a href="<?= App::baseUrl('/login') ?>" class="text-primary fw-bold text-decoration-none">Masuk di Sini</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const p1 = document.getElementById('reg_password');
    const p2 = document.getElementById('reg_password_confirm');
    const btnGen = document.getElementById('btnGenPassReg');
    const alertBox = document.getElementById('genPassAlertReg');
    const display = document.getElementById('genPassDisplayReg');
    const btnCopy = document.getElementById('btnCopyPassReg');
    const toggle1 = document.getElementById('btnToggleRegPass1');
    const icon1 = document.getElementById('toggleRegIcon1');
    const toggle2 = document.getElementById('btnToggleRegPass2');
    const icon2 = document.getElementById('toggleRegIcon2');

    let currentPass = '';

    function toggle(inp, ico) {
        if (inp.type === 'password') {
            inp.type = 'text';
            ico.className = 'bi bi-eye-slash text-primary';
        } else {
            inp.type = 'password';
            ico.className = 'bi bi-eye text-muted';
        }
    }

    if (toggle1) toggle1.addEventListener('click', () => toggle(p1, icon1));
    if (toggle2) toggle2.addEventListener('click', () => toggle(p2, icon2));

    if (btnGen) {
        btnGen.addEventListener('click', function() {
            const lower = 'abcdefghjkmnpqrstuvwxyz';
            const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
            const numbers = '23456789';
            const symbols = '!@#$%&*';

            let pwd = upper[Math.floor(Math.random() * upper.length)] +
                      lower[Math.floor(Math.random() * lower.length)] +
                      numbers[Math.floor(Math.random() * numbers.length)] +
                      symbols[Math.floor(Math.random() * symbols.length)];

            const all = lower + upper + numbers + symbols;
            for (let i = pwd.length; i < 12; i++) {
                pwd += all[Math.floor(Math.random() * all.length)];
            }
            pwd = pwd.split('').sort(() => 0.5 - Math.random()).join('');
            currentPass = pwd;

            p1.value = pwd;
            p2.value = pwd;
            p1.type = 'text';
            p2.type = 'text';
            icon1.className = 'bi bi-eye-slash text-primary';
            icon2.className = 'bi bi-eye-slash text-primary';

            display.textContent = pwd;
            alertBox.classList.remove('d-none');
            alertBox.classList.add('d-flex');

            if (navigator.clipboard) {
                navigator.clipboard.writeText(pwd).catch(() => {});
            }
        });
    }

    if (btnCopy) {
        btnCopy.addEventListener('click', function() {
            const txt = currentPass || p1.value;
            if (txt && navigator.clipboard) {
                navigator.clipboard.writeText(txt).then(() => {
                    btnCopy.innerHTML = '<i class="bi bi-check2 me-1"></i> Tersalin!';
                    setTimeout(() => { btnCopy.innerHTML = '<i class="bi bi-clipboard me-1"></i> Salin'; }, 2000);
                });
            }
        });
    }
})();
</script>
