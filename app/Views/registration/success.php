<?php
$reg = $reg ?? [];
$queueNumber = $reg['queue_number'] ?? '-';
$regCode = $reg['registration_code'] ?? '-';
$serviceName = $reg['service_name'] ?? '-';
$doctorName = $reg['doctor_name'] ?? '-';
$regDate = $reg['registration_date'] ?? date('Y-m-d');
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden text-center">
                <div class="bg-success text-white py-4 position-relative">
                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-opacity-25 bg-white" style="clip-path: polygon(0 0, 100% 0, 100% 15%, 0 45%);"></div>
                    <div class="mb-3">
                        <i class="bi bi-check-circle-fill" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Pendaftaran Berhasil!</h3>
                    <p class="mb-0 opacity-75">Antrean Anda telah tercatat dalam sistem</p>
                </div>
                <div class="card-body p-4 p-md-5">
                    <p class="text-muted mb-4">Terima kasih telah mendaftar layanan kesehatan secara online. Berikut adalah rincian antrean Anda:</p>
                    
                    <div class="bg-light rounded-4 p-4 mb-4 border border-success-subtle border-2">
                        <div class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.85rem; letter-spacing: 1px;">Nomor Antrean</div>
                        <div class="display-3 fw-bold text-success mb-2">#<?= str_pad((string)$queueNumber, 3, '0', STR_PAD_LEFT) ?></div>
                        <div class="badge bg-success bg-opacity-10 text-success fs-6 fw-normal px-3 py-2 rounded-pill">
                            Kode: <?= htmlspecialchars((string)$regCode) ?>
                        </div>
                    </div>

                    <div class="row g-3 text-start mb-4">
                        <div class="col-6">
                            <div class="text-muted small">Poli Tujuan</div>
                            <div class="fw-bold"><?= htmlspecialchars((string)$serviceName) ?></div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Tanggal Kunjungan</div>
                            <div class="fw-bold"><?= date('d F Y', strtotime((string)$regDate)) ?></div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">Dokter</div>
                            <div class="fw-bold"><?= htmlspecialchars((string)$doctorName) ?></div>
                        </div>
                    </div>

                    <div class="alert alert-info rounded-3 text-start d-flex align-items-center mb-4 border-0 bg-info bg-opacity-10">
                        <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
                        <div class="small text-dark">
                            Harap hadir <strong>15 menit sebelum</strong> waktu layanan dan tunjukkan E-Ticket kepada petugas pendaftaran.
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="<?= \App\Config\App::baseUrl('/pendaftaran/tiket/' . urlencode((string)$regCode)) ?>" class="btn btn-primary py-2 rounded-pill fw-bold">
                            <i class="bi bi-ticket-detailed me-2"></i>Lihat E-Ticket
                        </a>
                        <a href="<?= \App\Config\App::baseUrl('/pendaftaran/riwayat') ?>" class="btn btn-outline-secondary py-2 rounded-pill fw-bold">
                            Riwayat Pendaftaran
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
