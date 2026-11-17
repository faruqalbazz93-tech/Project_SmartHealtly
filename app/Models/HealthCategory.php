<?php

namespace App\Models;

use App\Core\Model;

class HealthCategory extends Model
{
    protected string $table = 'health_categories';

    public function findBySlug(string $slug): ?array
    {
        return $this->findBy('slug', $slug);
    }

    public function getWithArticleCount(): array
    {
        $sql = "SELECT c.*, COUNT(a.id) as total_articles
                FROM `{$this->table}` c
                LEFT JOIN `health_articles` a ON a.category_id = c.id AND a.status = 'published'
                GROUP BY c.id
                ORDER BY c.name ASC";
        return $this->query($sql);
    }
}
