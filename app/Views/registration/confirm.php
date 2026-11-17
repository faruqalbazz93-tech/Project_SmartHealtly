<?php
use App\Config\App;
use App\Core\Session;
?>

<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">
        <div class="card border-0 shadow-lg rounded-4 bg-white p-4 p-md-5">
            <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                <div class="bg-primary text-white p-3 rounded-4 fs-3">
                    <i class="bi bi-card-checklist"></i>
                </div>
                <div>
                    <h4 class="fw-bold text-dark mb-1">Konfirmasi Pendaftaran Antrean</h4>
                    <p class="text-muted small mb-0">Langkah 2 dari 2: Periksa kembali data pendaftaran Anda sebelum diterbitkan nomor antrean</p>
                </div>
            </div>

            <form action="<?= App::baseUrl('/pendaftaran/simpan') ?>" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="health_service_id" value="<?= $service['id'] ?>">
                <input type="hidden" name="schedule_id" value="<?= $schedule['id'] ?? '' ?>">
                <input type="hidden" name="registration_date" value="<?= htmlspecialchars($regDate) ?>">
                <input type="hidden" name="complaint" value="<?= htmlspecialchars($complaint) ?>">

                <!-- 1. Ringkasan Poli & Jadwal -->
                <div class="p-4 bg-light rounded-4 mb-4 border">
                    <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                        <div class="bg-primary text-white p-3 rounded-3 fs-4">
                            <i class="bi <?= $service['icon'] ?? 'bi-hospital' ?>"></i>
                        </div>
                        <div>
                            <span class="badge bg-primary-subtle text-primary fw-bold mb-1"><?= htmlspecialchars($service['code']) ?></span>
                            <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars($service['name']) ?></h5>
                            <small class="text-muted"><i class="bi bi-person-badge text-primary me-1"></i> Dokter: <?= htmlspecialchars($service['doctor_name'] ?? 'Dokter Jaga') ?></small>
                        </div>
                    </div>

                    <div class="row g-3 small">
                        <div class="col-md-6">
                            <span class="text-muted d-block">Hari & Tanggal Kunjungan:</span>
                            <strong class="text-dark fs-6"><?= $dayName ?>, <?= date('d F Y', strtotime($regDate)) ?></strong>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block">Jam Buka Pelayanan:</span>
                            <strong class="text-dark fs-6">
                                <?= !empty($schedule['start_time']) ? substr($schedule['start_time'], 0, 5) . ' - ' . substr($schedule['end_time'], 0, 5) . ' WIB' : '08:00 - 12:00 WIB' ?>
                            </strong>
                        </div>
                        <div class="col-12 mt-2 pt-2 border-top">
                            <span class="text-muted d-block">Keluhan yang Didaftarkan:</span>
                            <p class="text-dark mb-0 fst-italic">"<?= htmlspecialchars($complaint) ?>"</p>
                        </div>
                    </div>
                </div>

                <!-- 2. Ringkasan Identitas Pasien -->
                <div class="p-4 bg-white rounded-4 mb-4 border">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-person-circle text-primary me-2"></i>Data Identitas Pasien</h6>
                    <div class="row g-2 small">
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Nama Lengkap:</span>
                            <strong class="text-dark"><?= htmlspecialchars($user['name']) ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Nomor WhatsApp:</span>
                            <strong class="text-dark"><?= htmlspecialchars($user['phone']) ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Desa Domisili:</span>
                            <strong class="text-dark">Desa <?= htmlspecialchars($user['village_name'] ?? 'Astanajapura') ?>, Kec. Astanajapura</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Biaya Pemeriksaan:</span>
                            <span class="badge bg-success-subtle text-success fs-6 fw-bold">100% GRATIS</span>
                        </div>
                    </div>
                </div>

                <!-- Ketentuan Pelayanan & Pernyataan -->
                <div class="form-check p-3 bg-light rounded-3 mb-4 border ms-0">
                    <input class="form-check-input ms-2 me-3" type="checkbox" id="agreement" required checked>
                    <label class="form-check-label small text-muted" for="agreement">
                        Saya menyetujui data di atas adalah benar dan bersedia hadir di Puskesmas Astanajapura minimal 15 menit sebelum waktu pelayanan dengan membawa kartu identitas/e-Ticket.
                    </label>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="<?= App::baseUrl('/pendaftaran/baru?poli=' . $service['id'] . '&tanggal=' . $regDate) ?>" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="bi bi-arrow-left me-1"></i> Ubah Data
                    </a>
                    <button type="submit" class="btn btn-success btn-lg rounded-pill px-5 fw-bold shadow">
                        <i class="bi bi-check-circle-fill me-1"></i> Konfirmasi & Ambil Nomor Antrean
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
