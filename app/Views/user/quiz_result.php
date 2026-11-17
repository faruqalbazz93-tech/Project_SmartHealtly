<?php
use App\Config\App;
?>

<div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5 mb-4">
    <div class="text-center mb-4">
        <div class="score-circle mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle <?= ($attempt['score'] >= 80) ? 'bg-success text-white' : (($attempt['score'] >= 60) ? 'bg-warning text-dark' : 'bg-danger text-white') ?> shadow" style="width: 110px; height: 110px; font-size: 2.5rem; font-weight: 800;">
            <?= $attempt['score'] ?>
        </div>
        <h3 class="fw-bold text-dark mb-1">
            <?= ($attempt['score'] >= 80) ? 'Luar Biasa! Pemahaman Kesehatan Sangat Baik' : (($attempt['score'] >= 60) ? 'Bagus! Cukup Memahami Pola Hidup Sehat' : 'Terus Belajar & Tingkatkan Pemahaman Kesehatan!') ?>
        </h3>
        <p class="text-muted small">
            Anda berhasil menjawab dengan benar <strong><?= $attempt['correct_answers'] ?></strong> dari <strong><?= $attempt['total_questions'] ?></strong> soal.
        </p>
        <div class="d-flex gap-2 justify-content-center">
            <a href="<?= App::baseUrl('/kuis/mulai') ?>" class="btn btn-primary rounded-pill px-4 btn-sm fw-bold">
                <i class="bi bi-arrow-repeat me-1"></i> Coba Kuis Lagi
            </a>
            <a href="<?= App::baseUrl('/artikel') ?>" class="btn btn-outline-success rounded-pill px-4 btn-sm">
                <i class="bi bi-journal-medical me-1"></i> Baca Artikel Edukasi
            </a>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="fw-bold text-dark mb-3">Pembahasan & Kunci Jawaban:</h5>
    <div class="d-flex flex-column gap-3">
        <?php foreach ($answers as $index => $ans): ?>
            <div class="p-3 rounded-4 border <?= $ans['is_correct'] ? 'border-success-subtle bg-success-subtle' : 'border-danger-subtle bg-danger-subtle' ?>">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge <?= $ans['is_correct'] ? 'bg-success' : 'bg-danger' ?>">Soal <?= $index + 1 ?></span>
                    <span class="small fw-bold <?= $ans['is_correct'] ? 'text-success' : 'text-danger' ?>">
                        <i class="bi <?= $ans['is_correct'] ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?> me-1"></i>
                        <?= $ans['is_correct'] ? 'Jawaban Benar (+20 Poin)' : 'Jawaban Salah' ?>
                    </span>
                </div>
                <h6 class="fw-bold text-dark mb-2"><?= htmlspecialchars($ans['question_text']) ?></h6>
                <p class="small text-dark mb-1">
                    <strong>Jawaban Anda:</strong> <?= htmlspecialchars($ans['selected_option_text'] ?? 'Tidak dijawab') ?>
                </p>
                <?php if (!empty($ans['explanation'])): ?>
                    <div class="p-2 bg-white rounded-3 small text-muted mt-2 border">
                        <i class="bi bi-info-circle text-primary me-1"></i>
                        <strong>Penjelasan Singkat:</strong> <?= htmlspecialchars($ans['explanation']) ?>
                    </div>
                <?php endif; ?>
                
                <div class="mt-2">
                    <button class="btn btn-sm btn-outline-primary rounded-pill btn-ai-explain" data-qid="<?= $ans['question_id'] ?>" data-uid="<?= $ans['selected_option_id'] ?>">
                        <i class="bi bi-robot me-1"></i> Jelaskan dengan AI
                    </button>
                    <div class="ai-explanation-box mt-2 p-3 bg-white border border-primary rounded-3 d-none">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-stars text-warning me-2 fs-5"></i>
                            <strong class="text-primary">Penjelasan Medis AI</strong>
                        </div>
                        <div class="ai-content small text-dark"></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = '<?= Session::csrfToken() ?>';
    
    document.querySelectorAll('.btn-ai-explain').forEach(btn => {
        btn.addEventListener('click', async function() {
            const qId = this.getAttribute('data-qid');
            const uId = this.getAttribute('data-uid');
            const box = this.nextElementSibling;
            const content = box.querySelector('.ai-content');
            
            this.classList.add('disabled');
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Memuat...';
            
            box.classList.remove('d-none');
            content.innerHTML = '<div class="placeholder-glow"><span class="placeholder col-7"></span><span class="placeholder col-4"></span><span class="placeholder col-4"></span><span class="placeholder col-6"></span><span class="placeholder col-8"></span></div>';
            
            try {
                const fd = new FormData();
                fd.append('_csrf_token', csrfToken);
                fd.append('question_id', qId);
                fd.append('user_answer_id', uId);
                
                const response = await fetch('<?= App::baseUrl('/kuis/jelaskan') ?>', {
                    method: 'POST',
                    body: fd
                });
                
                const res = await response.json();
                
                if (res.success) {
                    content.innerHTML = res.explanation;
                    this.classList.add('d-none'); // hide button after success
                } else {
                    content.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-circle me-1"></i> ${res.message}</span>`;
                    this.classList.remove('disabled');
                    this.innerHTML = '<i class="bi bi-robot me-1"></i> Coba Lagi';
                }
            } catch (e) {
                content.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-circle me-1"></i> Terjadi kesalahan jaringan.</span>`;
                this.classList.remove('disabled');
                this.innerHTML = '<i class="bi bi-robot me-1"></i> Coba Lagi';
            }
        });
    });
});
</script>
