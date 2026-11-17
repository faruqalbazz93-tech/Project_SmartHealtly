<?php
use App\Config\App;
use App\Core\Auth;
use App\Core\Session;
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm py-2">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-white fs-4" href="<?= App::baseUrl('/') ?>">
            <div class="brand-icon bg-white text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px;">
                <i class="bi bi-heart-pulse-fill fs-5"></i>
            </div>
            <span>Smart<span class="text-info-emphasis">Healthy</span></span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                <li class="nav-item">
                    <a class="nav-link text-white px-3" href="<?= App::baseUrl('/') ?>">
                        <i class="bi bi-house-door me-1"></i> Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white-50 px-3 hover-white" href="<?= App::baseUrl('/layanan') ?>">
                        <i class="bi bi-hospital me-1"></i> Layanan & Jadwal
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white-50 px-3 hover-white" href="<?= App::baseUrl('/cek-antrean') ?>">
                        <i class="bi bi-search me-1"></i> Cek Antrean
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white-50 px-3 hover-white" href="<?= App::baseUrl('/artikel') ?>">
                        <i class="bi bi-journal-medical me-1"></i> Edukasi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white-50 px-3 hover-white" href="<?= App::baseUrl('/kontak') ?>">
                        <i class="bi bi-geo-alt me-1"></i> Kontak
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <?php if (Auth::check()): ?>
                    <!-- Notification Bell Dropdown -->
                    <div class="dropdown me-1 position-relative" id="navbarNotifDropdownRoot" data-base-url="<?= App::baseUrl('') ?>" data-csrf="<?= Session::csrfToken() ?>">
                        <button class="btn btn-outline-light rounded-circle p-2 position-relative d-flex align-items-center justify-content-center" 
                                type="button" 
                                id="notifBellButton" 
                                data-bs-toggle="dropdown" 
                                data-bs-auto-close="outside"
                                aria-expanded="false" 
                                style="width: 40px; height: 40px;" 
                                title="Notifikasi">
                            <i class="bi bi-bell-fill fs-6"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white d-none" 
                                  id="navbarNotifBadge" 
                                  style="font-size: 0.65rem;">
                                0
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow-lg rounded-4 p-0 border-0 mt-2" 
                             aria-labelledby="notifBellButton" 
                             style="width: 340px; max-width: 90vw;">
                            <!-- Header -->
                            <div class="p-3 bg-primary text-white rounded-top-4 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-bell-fill"></i>
                                    <span class="fw-bold small">Pemberitahuan</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-link text-white text-decoration-none p-0 small fw-semibold" id="btnMarkAllReadNavbar" style="font-size: 0.75rem;" title="Tandai semua dibaca">
                                        <i class="bi bi-check2-all me-1"></i> Baca Semua
                                    </button>
                                </div>
                            </div>

                            <!-- Notification items list container -->
                            <div class="list-group list-group-flush overflow-auto" id="navbarNotifList" style="max-height: 320px;">
                                <div class="p-4 text-center text-muted small" id="navbarNotifLoading">
                                    <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                                    <div>Memuat notifikasi...</div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="p-2 text-center bg-light rounded-bottom-4 border-top">
                                <a href="<?= App::baseUrl('/notifikasi') ?>" class="text-primary text-decoration-none small fw-bold d-block py-1">
                                    Lihat Semua Notifikasi &rarr;
                                </a>
                            </div>
                        </div>
                    </div>

                    <?php if (Auth::isAdmin()): ?>
                        <a href="<?= App::baseUrl('/admin') ?>" class="btn btn-warning fw-semibold rounded-pill px-3 shadow-sm">
                            <i class="bi bi-speedometer2 me-1"></i> Panel Admin
                        </a>
                    <?php else: ?>
                        <a href="<?= App::baseUrl('/dashboard') ?>" class="btn btn-light fw-semibold rounded-pill px-3 shadow-sm text-primary">
                            <i class="bi bi-grid-fill me-1"></i> Dashboard
                            <?php if (!Auth::isFullyVerified()): ?>
                                <span class="badge bg-warning text-dark ms-1" title="Akun belum terverifikasi penuh" style="font-size: 0.65rem;">!</span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                    <a href="<?= App::baseUrl('/logout') ?>" class="btn btn-outline-light rounded-pill px-3" title="Keluar">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                <?php else: ?>
                    <a href="<?= App::baseUrl('/login') ?>" class="btn btn-outline-light rounded-pill px-3">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                    </a>
                    <a href="<?= App::baseUrl('/register') ?>" class="btn btn-light fw-semibold rounded-pill px-3 shadow-sm text-primary">
                        <i class="bi bi-person-plus-fill me-1"></i> Daftar Pasien
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
