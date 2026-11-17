<?php
$reg = $reg ?? [];
$queueNumber = $reg['queue_number'] ?? '-';
$regCode = $reg['registration_code'] ?? '-';
$serviceName = $reg['service_name'] ?? '-';
$doctorName = $reg['doctor_name'] ?? '-';
$regDate = $reg['registration_date'] ?? date('Y-m-d');
$patientName = $reg['patient_name'] ?? '-';
$patientNik = $reg['patient_nik'] ?? '-';
$patientPhone = $reg['patient_phone'] ?? '-';
$status = $reg['status'] ?? 'Pending';
$complaint = $reg['complaint'] ?? '-';
$puskesmasName = $puskesmas['name'] ?? 'SmartHealthy';

$statusColors = [
    'Pending'   => 'warning',
    'Confirmed' => 'primary',
    'Completed' => 'success',
    'Cancelled' => 'danger'
];
$statusColor = $statusColors[$status] ?? 'secondary';
$statusLabels = [
    'Pending'   => 'Menunggu',
    'Confirmed' => 'Dikonfirmasi',
    'Completed' => 'Selesai',
    'Cancelled' => 'Dibatalkan'
];
$statusLabel = $statusLabels[$status] ?? $status;
?>

<?php
use App\Config\App;
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="d-flex justify-content-between align-items-center mb-4 d-print-none flex-wrap gap-2">
                <a href="<?= App::baseUrl('/pendaftaran/riwayat') ?>" class="btn btn-outline-secondary rounded-pill">
                    <i class="bi bi-arrow-left me-2"></i>Kembali ke Riwayat
                </a>
                <div class="d-flex gap-2">
                    <a href="<?= App::baseUrl('/cek-antrean?q=' . urlencode((string)$regCode)) ?>" class="btn btn-outline-primary rounded-pill px-3">
                        <i class="bi bi-display me-1"></i>Pantau Antrean
                    </a>
                    <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="bi bi-printer me-2"></i>Cetak E-Ticket
                    </button>
                </div>
            </div>

            <div class="card border-0 shadow-lg ticket-card rounded-4 overflow-hidden">
                <!-- Header -->
                <div class="bg-primary bg-gradient text-white p-4">
                    <div class="d-flex justify-content-between align-items-center border-bottom border-white border-opacity-25 pb-3 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-hospital-fill fs-2 me-3"></i>
                            <div>
                                <h5 class="mb-0 fw-bold"><?= htmlspecialchars((string)$puskesmasName) ?></h5>
                                <small class="opacity-75">Pendaftaran Layanan Kesehatan</small>
                            </div>
                        </div>
                        <div class="text-end d-none d-sm-block">
                            <!-- QR Code placeholder -->
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=70x70&data=<?= urlencode((string)$regCode) ?>&bgcolor=ffffff&color=000000" alt="QR Code" class="rounded bg-white p-1 shadow-sm">
                        </div>
                    </div>
                    
                    <div class="row align-items-center">
                        <div class="col-8">
                            <div class="text-uppercase text-white-50 small fw-bold mb-1" style="letter-spacing: 1px;">E-Ticket Antrean</div>
                            <h3 class="fw-bold mb-0 font-monospace"><?= htmlspecialchars((string)$regCode) ?></h3>
                        </div>
                        <div class="col-4 text-end">
                            <span class="badge bg-white text-<?= $statusColor ?> fs-6 rounded-pill px-3 py-2 shadow-sm">
                                <?= $statusLabel ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-md-5 bg-light p-4 d-flex flex-column justify-content-center align-items-center border-end border-2 border-white position-relative">
                            <!-- Ticket cutout effect -->
                            <div class="position-absolute top-50 start-100 translate-middle rounded-circle bg-white shadow-inner d-none d-md-block" style="width: 30px; height: 30px; z-index: 10;"></div>
                            
                            <div class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.85rem; letter-spacing: 1px;">Nomor Antrean</div>
                            <div class="display-1 fw-bolder text-primary mb-2">
                                <?= str_pad((string)$queueNumber, 3, '0', STR_PAD_LEFT) ?>
                            </div>
                            <div class="text-center text-muted small px-2 mt-2">
                                Harap tunjukkan e-ticket ini kepada petugas saat kedatangan
                            </div>
                        </div>
                        
                        <div class="col-md-7 p-4 p-md-5">
                            <h6 class="fw-bold text-uppercase text-muted mb-3 pb-2 border-bottom" style="font-size: 0.85rem; letter-spacing: 1px;">Detail Pasien</h6>
                            <div class="row mb-4 g-3">
                                <div class="col-12 col-sm-6">
                                    <div class="text-muted small mb-1">Nama Pasien</div>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars((string)$patientName) ?></div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="text-muted small mb-1">NIK</div>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars((string)$patientNik) ?></div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="text-muted small mb-1">No. Handphone</div>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars((string)$patientPhone) ?></div>
                                </div>
                            </div>

                            <h6 class="fw-bold text-uppercase text-muted mb-3 pb-2 border-bottom" style="font-size: 0.85rem; letter-spacing: 1px;">Detail Layanan</h6>
                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <div class="text-muted small mb-1">Poliklinik Tujuan</div>
                                    <div class="fw-bold text-primary"><?= htmlspecialchars((string)$serviceName) ?></div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="text-muted small mb-1">Tanggal Kunjungan</div>
                                    <div class="fw-bold text-dark"><?= date('d F Y', strtotime((string)$regDate)) ?></div>
                                </div>
                                <div class="col-12">
                                    <div class="text-muted small mb-1">Dokter</div>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars((string)$doctorName) ?></div>
                                </div>
                                <div class="col-12 mt-3">
                                    <div class="text-muted small mb-2">Keluhan Utama</div>
                                    <div class="bg-light p-3 rounded-3 small text-dark fst-italic border">
                                        <?= nl2br(htmlspecialchars((string)$complaint)) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white p-3 border-top d-print-none text-center">
                    <?php if ($status === 'Completed'): ?>
                        <div class="d-flex justify-content-center align-items-center gap-3">
                            <span class="text-success small fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Pemeriksaan Selesai</span>
                            <a href="<?= App::baseUrl('/pendaftaran/baru?poli=' . ($reg['health_service_id'] ?? '')) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="bi bi-arrow-repeat me-1"></i>Daftar Kunjungan Baru
                            </a>
                        </div>
                    <?php elseif ($status === 'Cancelled'): ?>
                        <div class="d-flex justify-content-center align-items-center gap-3">
                            <span class="text-danger small fw-semibold"><i class="bi bi-x-circle-fill me-1"></i>Pendaftaran Telah Dibatalkan</span>
                            <a href="<?= App::baseUrl('/pendaftaran/baru?poli=' . ($reg['health_service_id'] ?? '')) ?>" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
                                <i class="bi bi-arrow-repeat me-1"></i>Daftar Ulang Sekarang
                            </a>
                        </div>
                    <?php else: ?>
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i> Untuk membatalkan atau mengelola antrean ini, silakan kunjungi <a href="<?= App::baseUrl('/pendaftaran/riwayat') ?>" class="text-primary fw-semibold">Riwayat Pendaftaran</a>.
                        </small>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Print specific styles -->
            <style>
                .shadow-inner { box-shadow: inset 0 0 10px rgba(0,0,0,0.1); }
                @media print {
                    @page { margin: 1cm; }
                    body * { visibility: hidden; background: #fff; }
                    .ticket-card, .ticket-card * { visibility: visible; }
                    .ticket-card { position: absolute; left: 0; top: 0; width: 100%; border: 2px dashed #ccc !important; box-shadow: none !important; border-radius: 10px !important; }
                    .bg-primary { background-color: #0d6efd !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    .text-white { color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    .bg-light { background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    .ticket-card .border-white { border-color: #ccc !important; }
                    .shadow-inner { display: none !important; }
                }
            </style>
        </div>
    </div>
</div>
