<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\HealthArticle;
use App\Models\HealthCategory;

class ArticleController extends Controller
{
    public function index(): void
    {
        $articleModel = new HealthArticle();
        $categoryModel = new HealthCategory();

        $categoryId = $this->input('kategori') ? (int) $this->input('kategori') : null;
        $articles = $articleModel->getPublished(12, $categoryId);
        $categories = $categoryModel->getWithArticleCount();

        $this->render('home/articles', [
            'pageTitle'        => 'Edukasi Kesehatan Masyarakat - SmartHealthy',
            'articles'         => $articles,
            'categories'       => $categories,
            'activeCategoryId' => $categoryId,
        ]);
    }

    public function detail(string $slug): void
    {
        $articleModel = new HealthArticle();
        $article = $articleModel->findBySlug($slug);

        if (!$article) {
            $this->redirect('/artikel');
            return;
        }

        $articleModel->incrementViews((int) $article['id']);
        $relatedArticles = $articleModel->getPublished(3, (int) $article['category_id']);

        $this->render('home/article_detail', [
            'pageTitle'       => $article['title'] . ' - Edukasi SmartHealthy',
            'article'         => $article,
            'relatedArticles' => $relatedArticles,
        ]);
    }
}
