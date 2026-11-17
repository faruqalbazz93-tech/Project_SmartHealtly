<?php

namespace App\Models;

use App\Core\Model;

class HealthArticle extends Model
{
    protected string $table = 'health_articles';

    public function getPublished(int $limit = 10, ?int $categoryId = null): array
    {
        $sql = "SELECT a.*, c.name as category_name, c.slug as category_slug
                FROM `{$this->table}` a
                JOIN `health_categories` c ON a.category_id = c.id
                WHERE a.status = 'published'";

        $params = [];
        if ($categoryId) {
            $sql .= " AND a.category_id = :cat_id";
            $params['cat_id'] = $categoryId;
        }

        $sql .= " ORDER BY a.created_at DESC LIMIT {$limit}";

        return $this->query($sql, $params);
    }

    public function findBySlug(string $slug): ?array
    {
        $sql = "SELECT a.*, c.name as category_name, c.slug as category_slug
                FROM `{$this->table}` a
                JOIN `health_categories` c ON a.category_id = c.id
                WHERE a.slug = :slug LIMIT 1";

        return $this->queryOne($sql, ['slug' => $slug]);
    }

    public function incrementViews(int $id): void
    {
        $sql = "UPDATE `{$this->table}` SET views_count = views_count + 1 WHERE id = :id";
        $this->execute($sql, ['id' => $id]);
    }

    public function getAllWithCategory(): array
    {
        $sql = "SELECT a.*, c.name as category_name
                FROM `{$this->table}` a
                JOIN `health_categories` c ON a.category_id = c.id
                ORDER BY a.created_at DESC";
        return $this->query($sql);
    }
}
