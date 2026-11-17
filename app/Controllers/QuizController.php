<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;

class QuizController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();

        $attemptModel = new QuizAttempt();
        $userAttempts = $attemptModel->getByUser(Auth::id());
        $leaderboard = $attemptModel->getLeaderboard(5);

        $this->render('user/quiz_index', [
            'pageTitle'    => 'Kuis Edukasi Kesehatan - SmartHealthy',
            'userAttempts' => $userAttempts,
            'leaderboard'  => $leaderboard,
        ], 'layouts/user');
    }

    public function start(): void
    {
        Auth::requireAuth();

        $questionModel = new Question();
        $questions = $questionModel->getQuizSet(5);

        if (empty($questions)) {
            $this->flashAndRedirect('info', 'Belum ada soal kuis kesehatan yang tersedia.', '/kuis');
            return;
        }

        $this->render('user/quiz_play', [
            'pageTitle' => 'Mulai Kuis Kesehatan - SmartHealthy',
            'questions' => $questions,
        ], 'layouts/user');
    }

    public function submit(): void
    {
        Auth::requireAuth();

        if (!$this->validateCsrf()) {
            return;
        }

        $userId = Auth::id();
        $answers = $_POST['answers'] ?? []; // [question_id => selected_option_id]

        if (empty($answers)) {
            $this->flashAndRedirect('danger', 'Mohon jawab minimal satu soal kuis.', '/kuis/mulai');
            return;
        }

        $optionModel = new QuestionOption();
        $attemptModel = new QuizAttempt();
        $answerModel = new QuizAnswer();

        $totalQuestions = count($answers);
        $correctCount = 0;
        $totalScore = 0;
        $processedAnswers = [];

        foreach ($answers as $qId => $selectedOptId) {
            $qId = (int) $qId;
            $selectedOptId = (int) $selectedOptId;

            $option = $optionModel->find($selectedOptId);
            $isCorrect = ($option && $option['is_correct'] == 1) ? 1 : 0;

            if ($isCorrect) {
                $correctCount++;
            }

            $processedAnswers[] = [
                'question_id'        => $qId,
                'selected_option_id' => $selectedOptId,
                'is_correct'         => $isCorrect,
            ];
        }

        $score = $totalQuestions > 0 ? (int) round(($correctCount / $totalQuestions) * 100) : 0;

        $attemptId = $attemptModel->create([
            'user_id'         => $userId,
            'score'           => $score,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctCount,
            'completed_at'    => date('Y-m-d H:i:s'),
        ]);

        if ($attemptId) {
            foreach ($processedAnswers as $ans) {
                $ans['attempt_id'] = $attemptId;
                $answerModel->create($ans);
            }

            Session::setFlash('success', "Kuis selesai! Skor Anda: <strong>{$score}/100</strong> ({$correctCount} dari {$totalQuestions} soal benar).");
            $this->redirect('/kuis/hasil/' . $attemptId);
        } else {
            $this->flashAndRedirect('danger', 'Gagal menyimpan hasil kuis.', '/kuis');
        }
    }

    public function result(int $id): void
    {
        Auth::requireAuth();

        $attemptModel = new QuizAttempt();
        $attempt = $attemptModel->find($id);

        if (!$attempt || ($attempt['user_id'] != Auth::id() && !Auth::isAdmin())) {
            $this->flashAndRedirect('danger', 'Hasil kuis tidak ditemukan.', '/kuis');
            return;
        }

        $answerModel = new QuizAnswer();
        $answers = $answerModel->getByAttempt($id);

        $this->render('user/quiz_result', [
            'pageTitle' => 'Hasil Kuis Kesehatan - SmartHealthy',
            'attempt'   => $attempt,
            'answers'   => $answers,
        ], 'layouts/user');
    }

    public function explainAjax(): void
    {
        header('Content-Type: application/json');
        
        if (!Auth::check()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
            return;
        }

        if (!isset($_POST['_csrf_token']) || $_POST['_csrf_token'] !== Session::csrfToken()) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF Token.']);
            return;
        }

        $userId = Auth::id();
        $rateLimitKey = "ai_explain_user_{$userId}";
        
        // Rate limit: Max 5 AI explains per hour (3600 seconds)
        $rateLimiter = new \App\Core\RateLimiter();
        if ($rateLimiter->tooManyAttempts($rateLimitKey, 5)) {
            echo json_encode(['success' => false, 'message' => 'Anda telah mencapai batas penggunaan AI (5 kali per jam). Silakan coba lagi nanti.']);
            return;
        }

        $questionId = (int) ($this->input('question_id') ?? 0);
        $userAnswerId = (int) ($this->input('user_answer_id') ?? 0);

        if ($questionId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Data pertanyaan tidak valid.']);
            return;
        }

        $questionModel = new Question();
        $optionModel = new QuestionOption();

        $question = $questionModel->find($questionId);
        if (!$question) {
            echo json_encode(['success' => false, 'message' => 'Pertanyaan tidak ditemukan.']);
            return;
        }

        $options = $optionModel->getByQuestion($questionId);
        
        $userAnswerText = "Tidak menjawab / Tidak diketahui";
        $correctAnswerText = "Tidak diketahui";

        foreach ($options as $opt) {
            if ($opt['id'] == $userAnswerId) {
                $userAnswerText = $opt['option_text'];
            }
            if ($opt['is_correct']) {
                $correctAnswerText = $opt['option_text'];
            }
        }

        // Hit Rate Limiter before calling AI
        $rateLimiter->hit($rateLimitKey, 3600);

        try {
            $aiService = new \App\Services\AiService();
            $explanation = $aiService->explainAnswer($question['question_text'], $userAnswerText, $correctAnswerText);
            
            echo json_encode([
                'success' => true,
                'explanation' => $explanation
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Gagal memanggil AI: ' . $e->getMessage()]);
        }
    }
}
