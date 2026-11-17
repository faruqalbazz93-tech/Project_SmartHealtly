<?php
use App\Config\App;
?>

<div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
    <div class="row align-items-center g-4">
        <div class="col-lg-8">
            <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-warning-subtle text-warning-emphasis small mb-2 fw-semibold">
                <i class="bi bi-award-fill"></i>
                <span>Edukasi PHBS & Pencegahan Penyakit</span>
            </div>
            <h3 class="fw-bold text-dark mb-2">Kuis Interaktif Kesehatan Warga</h3>
            <p class="text-muted small mb-3">
                Uji dan tingkatkan pemahaman Anda tentang pola hidup sehat, pencegahan stunting, DBD, dan pengendalian hipertensi di lingkungan keluarga Astanajapura.
            </p>
            <a href="<?= App::baseUrl('/kuis/mulai') ?>" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                <i class="bi bi-play-circle-fill me-1"></i> Mulai Kuis Sekarang (5 Soal)
            </a>
        </div>
        <div class="col-lg-4 text-center">
            <div class="p-4 bg-light rounded-4 border">
                <i class="bi bi-patch-check-fill text-success display-4 mb-2 d-block"></i>
                <h6 class="fw-bold text-dark mb-1">Dapatkan Nilai Kesehatan</h6>
                <small class="text-muted">Ketahui pemahaman Anda seputar PHBS</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- User Past Attempts -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-clock-history text-primary me-2"></i>Riwayat Pengerjaan Anda</h5>
            <?php if (!empty($userAttempts)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Skor</th>
                                <th>Jawaban Benar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userAttempts as $att): ?>
                                <tr>
                                    <td><?= date('d M Y H:i', strtotime($att['created_at'])) ?></td>
                                    <td>
                                        <span class="badge <?= ($att['score'] >= 80) ? 'bg-success' : (($att['score'] >= 60) ? 'bg-warning text-dark' : 'bg-danger') ?> fs-6">
                                            <?= $att['score'] ?>/100
                                        </span>
                                    </td>
                                    <td><?= $att['correct_answers'] ?> dari <?= $att['total_questions'] ?> soal</td>
                                    <td>
                                        <a href="<?= App::baseUrl('/kuis/hasil/' . $att['id']) ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                                            Lihat Hasil
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <p class="small mb-0">Anda belum pernah mengikuti kuis kesehatan.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Leaderboard / Info -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-trophy-fill text-warning me-2"></i>Peringkat Warga Sehat</h5>
            <?php if (!empty($leaderboard)): ?>
                <div class="list-group list-group-flush small">
                    <?php foreach ($leaderboard as $idx => $lb): ?>
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-dark border rounded-circle" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center;">
                                    <?= $idx + 1 ?>
                                </span>
                                <div>
                                    <strong class="text-dark d-block"><?= htmlspecialchars($lb['user_name']) ?></strong>
                                    <small class="text-muted"><?= htmlspecialchars($lb['village_name'] ?? 'Astanajapura') ?></small>
                                </div>
                            </div>
                            <span class="badge bg-success-subtle text-success fs-6 fw-bold"><?= $lb['score'] ?> Poin</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="small text-muted mb-0">Belum ada data peringkat.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
