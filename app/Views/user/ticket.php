<?php
use App\Config\App;
?>

<div class="row justify-content-center">
    <div class="col-md-9 col-lg-8">
        <!-- Action Buttons (Hidden on Print) -->
        <div class="d-flex justify-content-between align-items-center mb-3 d-print-none">
            <a href="<?= App::baseUrl('/pendaftaran/riwayat') ?>" class="btn btn-outline-secondary rounded-pill btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
            </a>
            <button type="button" class="btn btn-primary rounded-pill btn-sm px-3 fw-bold" onclick="window.print()">
                <i class="bi bi-printer-fill me-1"></i> Cetak / Simpan PDF
            </button>
        </div>

        <!-- E-Ticket Card -->
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white ticket-card" id="printableTicket">
            <!-- Ticket Header -->
            <div class="bg-primary text-white p-4 text-center border-bottom border-4 border-warning position-relative">
                <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                    <i class="bi bi-heart-pulse-fill fs-3 text-warning"></i>
                    <h4 class="fw-bold mb-0 text-white"><?= htmlspecialchars($puskesmas['name']) ?></h4>
                </div>
                <p class="small text-white-75 mb-0"><?= htmlspecialchars($puskesmas['address']) ?></p>
                <div class="badge bg-white text-primary fw-bold mt-2 px-3 py-1 rounded-pill">
                    E-TICKET PENDAFTARAN ANTREAN RESMI
                </div>
            </div>

            <div class="card-body p-4 p-md-5">
                <!-- Big Queue Number -->
                <div class="text-center py-3 bg-light rounded-4 border border-dashed mb-4">
                    <span class="text-muted small text-uppercase tracking-wider fw-bold">Nomor Antrean Anda</span>
                    <div class="display-2 fw-extrabold text-primary my-1">
                        #<?= $reg['queue_number'] ?>
                    </div>
                    <div class="h5 fw-bold text-dark mb-1"><?= htmlspecialchars($reg['service_name']) ?></div>
                    <small class="text-muted"><i class="bi bi-person-badge me-1"></i> Dokter: <?= htmlspecialchars($reg['doctor_name'] ?? 'Dokter Jaga') ?></small>
                </div>

                <!-- Registration Details Table -->
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <span class="text-muted small d-block">Kode Booking:</span>
                        <strong class="text-primary font-monospace fs-6"><?= htmlspecialchars($reg['registration_code']) ?></strong>
                    </div>
                    <div class="col-6 text-end">
                        <span class="text-muted small d-block">Tanggal Pelayanan:</span>
                        <strong class="text-dark"><?= date('d F Y', strtotime($reg['registration_date'])) ?></strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">Nama Pasien:</span>
                        <strong class="text-dark"><?= htmlspecialchars($reg['patient_name']) ?></strong>
                    </div>
                    <div class="col-6 text-end">
                        <span class="text-muted small d-block">NIK Pasien:</span>
                        <strong class="text-dark"><?= htmlspecialchars($reg['patient_nik']) ?></strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">Desa Domisili:</span>
                        <strong class="text-dark"><?= htmlspecialchars($reg['patient_village'] ?? 'Astanajapura') ?></strong>
                    </div>
                    <div class="col-6 text-end">
                        <span class="text-muted small d-block">Status Saat Ini:</span>
                        <span class="badge bg-success text-uppercase"><?= htmlspecialchars($reg['status']) ?></span>
                    </div>
                    <div class="col-12 mt-2 pt-2 border-top">
                        <span class="text-muted small d-block">Keluhan Medis:</span>
                        <p class="text-dark small mb-0"><?= htmlspecialchars($reg['complaint']) ?></p>
                    </div>
                </div>

                <!-- Barcode & Instructions -->
                <div class="text-center p-3 bg-light rounded-3 border">
                    <!-- CSS Barcode Simulation -->
                    <div class="barcode-box mx-auto mb-2 text-dark font-monospace fw-bold" style="letter-spacing: 4px; font-size: 1.25rem;">
                        ||||| | |||| ||| ||||| || ||||
                    </div>
                    <span class="small font-monospace text-muted d-block"><?= htmlspecialchars($reg['registration_code']) ?></span>
                    <small class="text-muted mt-2 d-block" style="font-size: 0.75rem;">
                        * Harap simpan e-Ticket ini dan tunjukkan kepada petugas loket pendaftaran Puskesmas Astanajapura saat Anda tiba.
                    </small>
                </div>
            </div>

            <div class="card-footer bg-light p-3 text-center small text-muted">
                Hotline Puskesmas: <?= htmlspecialchars($puskesmas['hotline']) ?> &bull; Jam Layanan: 08.00 - 12.00 WIB
            </div>
        </div>
    </div>
</div>
