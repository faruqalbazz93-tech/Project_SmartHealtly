<?php
use App\Config\App;
use App\Core\Session;
?>

<div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5">
    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div class="bg-primary text-white p-3 rounded-4 fs-3">
            <i class="bi bi-person-circle"></i>
        </div>
        <div>
            <h4 class="fw-bold text-dark mb-1">Pengaturan Profil</h4>
            <p class="text-muted small mb-0">Kelola identitas diri dan informasi domisili rekam medis Anda</p>
        </div>
    </div>

    <form action="<?= App::baseUrl('/profil/update') ?>" method="POST">
        <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">

        <div class="row g-3">
            <!-- NIK (Readonly) -->
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-dark">NIK KTP (Terdaftar)</label>
                <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($user['nik']) ?>" readonly>
                <small class="text-muted">NIK bersifat permanen dan tidak dapat diubah.</small>
            </div>

            <!-- Email (Readonly) -->
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-dark">Alamat Email</label>
                <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                <small class="text-muted">Email akun pasien.</small>
            </div>

            <!-- Nama Lengkap -->
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-dark">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>

            <!-- No HP / WhatsApp -->
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-dark">Nomor HP / WhatsApp <span class="text-danger">*</span></label>
                <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
            </div>

            <!-- Desa Domisili -->
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-dark">Desa Domisili di Astanajapura</label>
                <select name="village_id" class="form-select">
                    <option value="">-- Pilih Desa --</option>
                    <?php foreach ($villages as $v): ?>
                        <option value="<?= $v['id'] ?>" <?= ($user['village_id'] == $v['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($v['name']) ?> (Kodepos <?= $v['postal_code'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Jenis Kelamin -->
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-dark">Jenis Kelamin</label>
                <select name="gender" class="form-select">
                    <option value="L" <?= ($user['gender'] === 'L') ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="P" <?= ($user['gender'] === 'P') ? 'selected' : '' ?>>Perempuan</option>
                </select>
            </div>

            <!-- Tanggal Lahir -->
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-dark">Tanggal Lahir</label>
                <input type="date" name="birth_date" class="form-control" value="<?= $user['birth_date'] ?? '' ?>">
            </div>

            <!-- Alamat Detail -->
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-dark">Alamat Detail (RT/RW/Blok)</label>
                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="Blok Manis RT 01 RW 02">
            </div>

            <!-- Pengaturan Kata Sandi (Otomatis & Manual) -->
            <div class="col-12 mt-4 pt-4 border-top" id="password-section">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <div>
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="bi bi-shield-lock-fill text-primary me-2"></i>Pengaturan Kata Sandi Akun
                        </h6>
                        <small class="text-muted">Kelola kata sandi akun Anda atau buat sandi baru secara otomatis yang aman dan kuat.</small>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold shadow-xs" id="btnGenPassProfile">
                            <i class="bi bi-magic me-1"></i> Buat Sandi Otomatis
                        </button>
                    </div>
                </div>

                <!-- Notifikasi / Preview Sandi Otomatis -->
                <div id="genPassAlert" class="alert alert-success border-0 rounded-3 p-3 mb-3 d-none align-items-center justify-content-between gap-3 shadow-xs">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <div>
                            <span class="small fw-semibold d-block">Sandi Otomatis Berhasil Dibuat:</span>
                            <code class="fs-6 fw-bold text-dark font-monospace" id="genPassDisplay">--------</code>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold" id="btnCopyGenPass">
                            <i class="bi bi-clipboard-check me-1"></i> Salin Sandi
                        </button>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-dark mb-1">Kata Sandi Baru</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" id="profile_password" name="password" minlength="6" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah" autocomplete="new-password">
                            <button type="button" class="btn btn-outline-secondary" id="btnTogglePass1" title="Lihat/Sembunyikan Sandi">
                                <i class="bi bi-eye" id="toggleIcon1"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-dark mb-1">Konfirmasi Kata Sandi Baru</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" id="profile_password_confirm" name="password_confirm" minlength="6" class="form-control" placeholder="Ulangi kata sandi baru" autocomplete="new-password">
                            <button type="button" class="btn btn-outline-secondary" id="btnTogglePass2" title="Lihat/Sembunyikan Sandi">
                                <i class="bi bi-eye" id="toggleIcon2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Password Strength & Match Meter -->
                    <div class="col-12" id="strengthContainer" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted" style="font-size: 0.75rem;">Kekuatan Sandi: <strong id="strengthLabel" class="text-secondary">-</strong></small>
                            <small id="matchLabel" class="text-muted" style="font-size: 0.75rem;"></small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div id="strengthBar" class="progress-bar bg-danger" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mt-4 pt-3 border-top">
            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                <i class="bi bi-save me-1"></i> Simpan Perubahan Profil & Sandi
            </button>
        </div>
    </form>
</div>

<!-- Script Generator & Manajemen Kata Sandi Otomatis -->
<script>
(function() {
    const passInput = document.getElementById('profile_password');
    const confirmInput = document.getElementById('profile_password_confirm');
    const btnGen = document.getElementById('btnGenPassProfile');
    const alertBox = document.getElementById('genPassAlert');
    const displayCode = document.getElementById('genPassDisplay');
    const btnCopy = document.getElementById('btnCopyGenPass');
    const btnToggle1 = document.getElementById('btnTogglePass1');
    const icon1 = document.getElementById('toggleIcon1');
    const btnToggle2 = document.getElementById('btnTogglePass2');
    const icon2 = document.getElementById('toggleIcon2');
    const strengthContainer = document.getElementById('strengthContainer');
    const strengthBar = document.getElementById('strengthBar');
    const strengthLabel = document.getElementById('strengthLabel');
    const matchLabel = document.getElementById('matchLabel');

    let currentGenerated = '';

    // Function to generate strong automatic password
    function generateStrongPassword(length = 12) {
        const lower = 'abcdefghjkmnpqrstuvwxyz';
        const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        const numbers = '23456789';
        const symbols = '!@#$%&*';

        let password = '';
        // Ensure at least 1 of each character class
        password += upper[Math.floor(Math.random() * upper.length)];
        password += lower[Math.floor(Math.random() * lower.length)];
        password += numbers[Math.floor(Math.random() * numbers.length)];
        password += symbols[Math.floor(Math.random() * symbols.length)];

        const allChars = lower + upper + numbers + symbols;
        for (let i = password.length; i < length; i++) {
            password += allChars[Math.floor(Math.random() * allChars.length)];
        }

        // Shuffle characters
        return password.split('').sort(() => 0.5 - Math.random()).join('');
    }

    // Toggle password visibility
    function toggleVisibility(input, icon) {
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash text-primary';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye text-muted';
        }
    }

    if (btnToggle1) {
        btnToggle1.addEventListener('click', () => toggleVisibility(passInput, icon1));
    }
    if (btnToggle2) {
        btnToggle2.addEventListener('click', () => toggleVisibility(confirmInput, icon2));
    }

    // Evaluate password strength
    function checkStrength(pwd) {
        if (!pwd || pwd.length === 0) {
            strengthContainer.style.display = 'none';
            return;
        }

        strengthContainer.style.display = 'block';
        let score = 0;
        if (pwd.length >= 6) score += 20;
        if (pwd.length >= 10) score += 20;
        if (/[A-Z]/.test(pwd)) score += 20;
        if (/[0-9]/.test(pwd)) score += 20;
        if (/[^A-Za-z0-9]/.test(pwd)) score += 20;

        strengthBar.style.width = score + '%';
        if (score <= 40) {
            strengthBar.className = 'progress-bar bg-danger';
            strengthLabel.textContent = 'Lemah';
            strengthLabel.className = 'text-danger fw-bold';
        } else if (score <= 60) {
            strengthBar.className = 'progress-bar bg-warning';
            strengthLabel.textContent = 'Sedang';
            strengthLabel.className = 'text-warning-emphasis fw-bold';
        } else if (score <= 80) {
            strengthBar.className = 'progress-bar bg-info';
            strengthLabel.textContent = 'Kuat';
            strengthLabel.className = 'text-info-emphasis fw-bold';
        } else {
            strengthBar.className = 'progress-bar bg-success';
            strengthLabel.textContent = 'Sangat Kuat (Aman)';
            strengthLabel.className = 'text-success fw-bold';
        }

        // Check match
        if (confirmInput.value.length > 0) {
            if (pwd === confirmInput.value) {
                matchLabel.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Sandi Cocok</span>';
            } else {
                matchLabel.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>Belum Cocok</span>';
            }
        } else {
            matchLabel.innerHTML = '';
        }
    }

    passInput.addEventListener('input', () => checkStrength(passInput.value));
    confirmInput.addEventListener('input', () => checkStrength(passInput.value));

    // Handle auto-generation button click
    btnGen.addEventListener('click', function() {
        const newPass = generateStrongPassword(12);
        currentGenerated = newPass;

        // Auto-fill both inputs
        passInput.value = newPass;
        confirmInput.value = newPass;

        // Show inputs as text momentarily so user can see it
        passInput.type = 'text';
        confirmInput.type = 'text';
        icon1.className = 'bi bi-eye-slash text-primary';
        icon2.className = 'bi bi-eye-slash text-primary';

        // Show banner alert
        displayCode.textContent = newPass;
        alertBox.classList.remove('d-none');
        alertBox.classList.add('d-flex');

        // Update strength
        checkStrength(newPass);

        // Auto copy to clipboard if supported
        if (navigator.clipboard) {
            navigator.clipboard.writeText(newPass).then(() => {
                btnCopy.innerHTML = '<i class="bi bi-clipboard-check-fill me-1"></i> Tersalin Otomatis!';
                setTimeout(() => {
                    btnCopy.innerHTML = '<i class="bi bi-clipboard me-1"></i> Salin Sandi';
                }, 2500);
            }).catch(() => {});
        }
    });

    // Handle manual copy button
    btnCopy.addEventListener('click', function() {
        if (!currentGenerated && passInput.value) {
            currentGenerated = passInput.value;
        }
        if (currentGenerated && navigator.clipboard) {
            navigator.clipboard.writeText(currentGenerated).then(() => {
                btnCopy.innerHTML = '<i class="bi bi-check2-all me-1"></i> Berhasil Disalin!';
                setTimeout(() => {
                    btnCopy.innerHTML = '<i class="bi bi-clipboard me-1"></i> Salin Sandi';
                }, 2000);
            });
        }
    });
})();
</script>
