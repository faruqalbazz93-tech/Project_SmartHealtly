<?php
use App\Config\App;
use App\Core\Session;

$generatedQuestions = [];
if ($generated) {
    $generatedQuestions = json_decode(html_entity_decode($generated), true);
}
?>

<div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="bi bi-robot text-primary me-2"></i>AI Pembuat Soal Kuis</h4>
            <p class="text-muted small mb-0">Hasilkan pertanyaan edukatif dengan cepat menggunakan kecerdasan buatan.</p>
        </div>
        <a href="<?= App::baseUrl('/admin/kuis') ?>" class="btn btn-outline-secondary rounded-pill btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Bank Soal
        </a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger rounded-3 small">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="<?= App::baseUrl('/admin/ai-pembuat-soal/generate') ?>" method="POST" class="row g-3">
        <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
        
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Topik Materi</label>
            <input type="text" name="topic" class="form-control form-control-lg fs-6" placeholder="Contoh: Tanda bahaya kehamilan" value="<?= htmlspecialchars($topic ?? '') ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Kategori</label>
            <select name="category_id" class="form-select form-select-lg fs-6" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= (isset($category) && $category == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Jumlah Soal</label>
            <input type="number" name="count" class="form-control" min="1" max="10" value="3" required>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Tingkat Kesulitan</label>
            <select name="difficulty" class="form-select">
                <option value="Mudah">Mudah</option>
                <option value="Sedang" selected>Sedang</option>
                <option value="Sulit">Sulit</option>
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100 fw-bold" id="btnGenerate">
                <i class="bi bi-magic me-1"></i> Generate dengan AI
            </button>
        </div>
    </form>
</div>

<?php if (!empty($generatedQuestions) && is_array($generatedQuestions)): ?>
<div class="card border-0 shadow-sm rounded-4 bg-white p-4 border-top border-primary border-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold mb-0">Preview Hasil AI</h5>
        <form action="<?= App::baseUrl('/admin/ai-pembuat-soal/simpan') ?>" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
            <input type="hidden" name="category_id" value="<?= htmlspecialchars($category ?? '') ?>">
            <input type="hidden" name="questions_json" value="<?= htmlspecialchars($generated ?? '') ?>">
            <button type="submit" class="btn btn-success rounded-pill fw-bold px-4 shadow-sm">
                <i class="bi bi-cloud-arrow-up me-1"></i> Simpan ke Bank Soal
            </button>
        </form>
    </div>

    <div class="d-flex flex-column gap-3">
        <?php foreach ($generatedQuestions as $index => $q): ?>
            <div class="p-3 bg-light rounded-3 border">
                <p class="fw-bold text-dark mb-2"><?= $index + 1 ?>. <?= htmlspecialchars($q['question_text']) ?></p>
                
                <div class="row g-2 mb-2">
                    <?php if (isset($q['options']) && is_array($q['options'])): ?>
                        <?php foreach ($q['options'] as $opt): ?>
                            <div class="col-md-6">
                                <div class="p-2 border rounded-2 small <?= !empty($opt['is_correct']) ? 'bg-success-subtle border-success fw-bold text-success-emphasis' : 'bg-white text-muted' ?>">
                                    <i class="bi <?= !empty($opt['is_correct']) ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' ?> me-1"></i>
                                    <?= htmlspecialchars($opt['text']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if (!empty($q['explanation'])): ?>
                    <div class="text-muted small mt-2">
                        <i class="bi bi-info-circle text-info me-1"></i> <strong>Penjelasan:</strong> <?= htmlspecialchars($q['explanation']) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
document.getElementById('btnGenerate').addEventListener('click', function() {
    if(document.querySelector('form').checkValidity()) {
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> AI sedang berpikir...';
        this.classList.add('disabled');
    }
});
</script>
