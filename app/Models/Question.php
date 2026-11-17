<?php

namespace App\Models;

use App\Core\Model;

class Question extends Model
{
    protected string $table = 'questions';

    public function getActiveQuestionsWithCategory(): array
    {
        $sql = "SELECT q.*, c.name as category_name 
                FROM `{$this->table}` q
                LEFT JOIN `health_categories` c ON q.category_id = c.id
                WHERE q.status = 'active'
                ORDER BY q.id ASC";
        return $this->query($sql);
    }

    public function getQuizSet(int $limit = 5): array
    {
        $sql = "SELECT q.*, c.name as category_name 
                FROM `{$this->table}` q
                LEFT JOIN `health_categories` c ON q.category_id = c.id
                WHERE q.status = 'active'
                ORDER BY RAND() LIMIT {$limit}";
        
        $questions = $this->query($sql);
        $optionModel = new QuestionOption();

        foreach ($questions as &$q) {
            $q['options'] = $optionModel->getByQuestion((int) $q['id']);
        }

        return $questions;
    }
}
