<?php

namespace App\Models;

use App\Core\Model;

class QuestionOption extends Model
{
    protected string $table = 'question_options';

    public function getByQuestion(int $questionId): array
    {
        return $this->where('question_id', $questionId, 'id ASC');
    }

    public function getCorrectOption(int $questionId): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE question_id = :qid AND is_correct = 1 LIMIT 1";
        return $this->queryOne($sql, ['qid' => $questionId]);
    }
}
