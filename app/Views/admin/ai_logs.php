<?php
use App\Config\App;
?>

<div class="card border-0 shadow-sm rounded-4 bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="bi bi-robot text-purple me-2" style="color: #6f42c1;"></i>Monitor Log AI & Kesiapan Integrasi</h4>
            <p class="text-muted small mb-0">Pencatatan interaksi AI (Symptom Checker, Triage Assist, Ringkasan Edukasi) sesuai arsitektur sistem</p>
        </div>
        <span class="badge bg-purple-subtle text-purple px-3 py-2 rounded-pill border" style="background-color: #f3e8ff; color: #6f42c1;">
            <i class="bi bi-cpu me-1"></i> Modul AI Placeholder Siap
        </span>
    </div>

    <!-- AI Architecture Notice Box -->
    <div class="alert alert-light border shadow-xs rounded-4 p-4 mb-4">
        <div class="d-flex align-items-start gap-3">
            <div class="bg-primary text-white p-2 rounded-3 fs-4">
                <i class="bi bi-info-circle"></i>
            </div>
            <div>
                <h6 class="fw-bold text-dark mb-1">Status Kesiapan Arsitektur AI SmartHealthy:</h6>
                <p class="small text-muted mb-0">
                    Sesuai petunjuk pengerjaan (fase dasar), tabel <code>ai_logs</code> dan struktur data telah dibuat lengkap dengan kolom <code>feature_type</code>, <code>prompt_payload</code>, <code>response_payload</code>, <code>tokens_used</code>, dan <code>response_time_ms</code>. Modul siap dihubungkan dengan LLM API pada tahap implementasi fitur AI mendatang.
                </p>
            </div>
        </div>
    </div>

    <?php if (!empty($logs)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Fitur AI</th>
                        <th>Prompt</th>
                        <th>Respons</th>
                        <th>Token</th>
                        <th>Latency</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                            <td><?= htmlspecialchars($log['user_name'] ?? 'Guest / Anonim') ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($log['feature_type']) ?></span></td>
                            <td class="text-truncate" style="max-width: 150px;"><?= htmlspecialchars($log['prompt_payload']) ?></td>
                            <td class="text-truncate" style="max-width: 150px;"><?= htmlspecialchars($log['response_payload']) ?></td>
                            <td><?= $log['tokens_used'] ?></td>
                            <td><?= $log['response_time_ms'] ?> ms</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-robot fs-1 d-block mb-2 text-muted"></i>
            <h6 class="fw-bold text-dark">Belum Ada Aktivitas Log AI</h6>
            <p class="small text-muted mb-0">Log akan tercatat secara otomatis ketika fitur AI diaktifkan pada fase selanjutnya.</p>
        </div>
    <?php endif; ?>
</div>
