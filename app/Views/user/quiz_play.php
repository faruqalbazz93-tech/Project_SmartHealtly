<?php
use App\Config\App;
use App\Core\Session;
?>

<div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-bold mb-1">Kuis Edukasi</span>
            <h4 class="fw-bold text-dark mb-0">Uji Wawasan Kesehatan Anda</h4>
        </div>
        <a href="<?= App::baseUrl('/kuis') ?>" class="btn btn-outline-secondary rounded-pill btn-sm">
            <i class="bi bi-x-lg me-1"></i> Batal
        </a>
    </div>

    <form action="<?= App::baseUrl('/kuis/kirim') ?>" method="POST">
        <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">

        <?php foreach ($questions as $index => $q): ?>
            <div class="p-4 bg-light rounded-4 mb-4 border">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge bg-primary rounded-pill">Soal <?= $index + 1 ?> dari <?= count($questions) ?></span>
                    <?php if (!empty($q['category_name'])): ?>
                        <span class="badge bg-light text-muted border"><?= htmlspecialchars($q['category_name']) ?></span>
                    <?php endif; ?>
                </div>

                <h5 class="fw-bold text-dark mb-3"><?= htmlspecialchars($q['question_text']) ?></h5>

                <div class="d-flex flex-column gap-2">
                    <?php if (!empty($q['options'])): ?>
                        <?php foreach ($q['options'] as $opt): ?>
                            <div class="form-check p-3 bg-white border rounded-3 cursor-pointer hover-shadow-sm">
                                <input class="form-check-input ms-0 me-3" type="radio" name="answers[<?= $q['id'] ?>]" id="opt_<?= $opt['id'] ?>" value="<?= $opt['id'] ?>" required>
                                <label class="form-check-label w-100 cursor-pointer text-dark" for="opt_<?= $opt['id'] ?>">
                                    <?= htmlspecialchars($opt['option_text']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="text-end pt-3">
            <button type="submit" class="btn btn-success btn-lg rounded-pill px-5 fw-bold shadow">
                <i class="bi bi-check2-circle me-1"></i> Selesaikan & Kirim Jawaban
            </button>
        </div>
    </form>
</div>
