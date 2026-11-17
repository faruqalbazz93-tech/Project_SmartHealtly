<?php

namespace App\Models;

use App\Core\Model;

class QuizAnswer extends Model
{
    protected string $table = 'quiz_answers';

    public function getByAttempt(int $attemptId): array
    {
        $sql = "SELECT ans.*, q.question_text, q.explanation, opt.option_text as selected_option_text
                FROM `{$this->table}` ans
                JOIN `questions` q ON ans.question_id = q.id
                LEFT JOIN `question_options` opt ON ans.selected_option_id = opt.id
                WHERE ans.attempt_id = :attempt_id";
        return $this->query($sql, ['attempt_id' => $attemptId]);
    }
}
