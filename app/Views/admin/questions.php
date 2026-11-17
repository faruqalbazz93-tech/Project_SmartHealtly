<?php
use App\Config\App;
?>

<div class="card border-0 shadow-sm rounded-4 bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="bi bi-question-circle-fill text-warning me-2"></i>Bank Soal Kuis Kesehatan Warga</h4>
            <p class="text-muted small mb-0">Daftar pertanyaan edukasi interaktif untuk masyarakat seputar PHBS & pencegahan penyakit</p>
        </div>
        <div>
            <a href="<?= App::baseUrl('/admin/ai-pembuat-soal') ?>" class="btn btn-outline-primary rounded-pill btn-sm px-3 fw-bold me-2">
                <i class="bi bi-robot me-1"></i> Generate AI
            </a>
            <button class="btn btn-primary rounded-pill btn-sm px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddQuestion">
                <i class="bi bi-plus-circle me-1"></i> Tambah Soal Manual
            </button>
        </div>
    </div>

    <div class="d-flex flex-column gap-3">
        <?php foreach ($questions as $index => $q): ?>
            <div class="p-4 bg-light rounded-4 border">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-primary rounded-pill">Soal #<?= $index + 1 ?></span>
                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($q['category_name'] ?? 'Umum') ?></span>
                    <form action="<?= App::baseUrl('/admin/kuis/hapus/' . $q['id']) ?>" method="POST" onsubmit="return confirm('Hapus soal ini?')" class="d-inline">
                        <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
                        <button class="btn btn-sm text-danger p-0 ms-2"><i class="bi bi-trash"></i></button>
                    </form>
                </div>

                <h6 class="fw-bold text-dark mb-3"><?= htmlspecialchars($q['question_text']) ?></h6>

                <div class="row g-2 mb-3">
                    <?php if (!empty($q['options'])): ?>
                        <?php foreach ($q['options'] as $opt): ?>
                            <div class="col-md-6">
                                <div class="p-2 rounded-3 border <?= $opt['is_correct'] ? 'bg-success-subtle border-success text-success-emphasis fw-bold' : 'bg-white' ?> small">
                                    <i class="bi <?= $opt['is_correct'] ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' ?> me-1"></i>
                                    <?= htmlspecialchars($opt['option_text']) ?>
                                    <?php if ($opt['is_correct']): ?>
                                        <span class="badge bg-success float-end">Kunci Benar</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if (!empty($q['explanation'])): ?>
                    <div class="p-2 bg-white rounded-3 small text-muted border">
                        <i class="bi bi-info-circle text-primary me-1"></i>
                        <strong>Penjelasan Medis:</strong> <?= htmlspecialchars($q['explanation']) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    </div>
</div>

<!-- Modal Add Question -->
<div class="modal fade" id="modalAddQuestion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">Tambah Soal Kuis Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= App::baseUrl('/admin/kuis/simpan') ?>" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')) ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kategori Edukasi</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Teks Pertanyaan</label>
                        <textarea name="question_text" class="form-control" rows="3" required></textarea>
                    </div>

                    <hr>
                    <p class="small fw-bold text-muted mb-2">Pilihan Jawaban (Pilih salah satu sebagai kunci jawaban yang benar)</p>
                    
                    <?php for ($i = 0; $i < 4; $i++): ?>
                    <div class="input-group mb-2">
                        <div class="input-group-text bg-white">
                            <input class="form-check-input mt-0" type="radio" name="correct_option" value="<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?> required>
                        </div>
                        <input type="text" name="options[]" class="form-control" placeholder="Pilihan Jawaban <?= $i + 1 ?>" required>
                    </div>
                    <?php endfor; ?>

                    <hr>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Penjelasan Ilmiah (Opsional)</label>
                        <textarea name="explanation" class="form-control" rows="2" placeholder="Mengapa jawaban tersebut benar?"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill fw-bold">Simpan Soal</button>
                </div>
            </form>
        </div>
    </div>
</div>

