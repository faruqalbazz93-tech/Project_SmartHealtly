<?php
use App\Config\App;
use App\Core\Session;
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
            <div class="text-center mb-4">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow" style="width: 60px; height: 60px;">
                    <i class="bi bi-shield-lock-fill fs-3"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1">Buat Kata Sandi Baru</h4>
                <p class="text-muted small mb-0">
                    Pengaturan kata sandi untuk akun: <strong><?= htmlspecialchars($resetRecord['user_email']) ?></strong>
                </p>
            </div>

            <form action="<?= App::baseUrl('/reset-password') ?>" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-semibold text-dark">Atur Kata Sandi Baru</span>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-0 fw-semibold" id="btnGenPassReset" style="font-size: 0.75rem;">
                        <i class="bi bi-magic me-1"></i> Buat Sandi Otomatis
                    </button>
                </div>

                <!-- Alert Preview Sandi Baru -->
                <div id="genPassAlertReset" class="alert alert-success border-0 rounded-3 p-2 mb-3 d-none align-items-center justify-content-between small">
                    <div>
                        <span class="text-muted">Sandi Otomatis:</span> <strong class="font-monospace text-dark" id="genPassDisplayReset">--------</strong>
                    </div>
                    <button type="button" class="btn btn-sm btn-success rounded-pill py-0 px-2" id="btnCopyPassReset" style="font-size: 0.72rem;">
                        <i class="bi bi-clipboard me-1"></i> Salin
                    </button>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-dark mb-1">Kata Sandi Baru <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-lock"></i></span>
                        <input type="password" id="reset_password" name="password" minlength="6" class="form-control bg-light" placeholder="Minimal 6 karakter" required autofocus>
                        <button type="button" class="btn btn-outline-secondary" id="btnToggleResetPass1" title="Lihat/Sembunyikan Sandi">
                            <i class="bi bi-eye" id="toggleResetIcon1"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-dark mb-1">Konfirmasi Kata Sandi Baru <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" id="reset_password_confirm" name="password_confirm" minlength="6" class="form-control bg-light" placeholder="Ulangi kata sandi" required>
                        <button type="button" class="btn btn-outline-secondary" id="btnToggleResetPass2" title="Lihat/Sembunyikan Sandi">
                            <i class="bi bi-eye" id="toggleResetIcon2"></i>
                        </button>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary py-2 rounded-pill fw-bold shadow-sm">
                        <i class="bi bi-check-circle-fill me-1"></i> Simpan Kata Sandi Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    const p1 = document.getElementById('reset_password');
    const p2 = document.getElementById('reset_password_confirm');
    const btnGen = document.getElementById('btnGenPassReset');
    const alertBox = document.getElementById('genPassAlertReset');
    const display = document.getElementById('genPassDisplayReset');
    const btnCopy = document.getElementById('btnCopyPassReset');
    const toggle1 = document.getElementById('btnToggleResetPass1');
    const icon1 = document.getElementById('toggleResetIcon1');
    const toggle2 = document.getElementById('btnToggleResetPass2');
    const icon2 = document.getElementById('toggleResetIcon2');

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
