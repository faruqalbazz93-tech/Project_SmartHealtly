<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\HealthService;
use App\Models\Schedule;
use App\Models\HealthArticle;
use App\Models\HealthCategory;
use App\Models\Village;
use App\Models\Registration;
use App\Config\App;

class HomeController extends Controller
{
    public function index(): void
    {
        $serviceModel = new HealthService();
        $articleModel = new HealthArticle();
        $villageModel = new Village();
        $regModel = new Registration();

        $today = date('Y-m-d');
        $services = $serviceModel->getWithTodayQueueStats($today);
        $articles = $articleModel->getPublished(3);
        $villages = $villageModel->allOrdered();

        // General Puskesmas stats
        $stats = [
            'total_services' => count($services),
            'total_villages' => count($villages),
            'total_patients' => (new \App\Models\User())->count("role = 'user'"),
            'today_queue'    => $regModel->count("registration_date = :today AND status != 'dibatalkan'", ['today' => $today]),
        ];

        $this->render('home/index', [
            'pageTitle' => 'SmartHealthy - Sistem Pendaftaran Puskesmas Astanajapura',
            'services'  => $services,
            'articles'  => $articles,
            'villages'  => $villages,
            'stats'     => $stats,
            'puskesmas' => App::puskesmas(),
        ]);
    }

    public function services(): void
    {
        $serviceModel = new HealthService();
        $scheduleModel = new Schedule();

        $services = $serviceModel->getActiveServices();
        $schedules = $scheduleModel->getAllWithServices();

        // Group schedules by service
        $schedulesByService = [];
        foreach ($schedules as $s) {
            $schedulesByService[$s['health_service_id']][] = $s;
        }

        $this->render('home/services', [
            'pageTitle'          => 'Layanan Poli & Jadwal Dokter - SmartHealthy',
            'services'           => $services,
            'schedulesByService' => $schedulesByService,
            'puskesmas'          => App::puskesmas(),
        ]);
    }

    public function articles(): void
    {
        $articleModel = new HealthArticle();
        $categoryModel = new HealthCategory();

        $categoryId = $this->input('kategori') ? (int) $this->input('kategori') : null;
        $articles = $articleModel->getPublished(12, $categoryId);
        $categories = $categoryModel->getWithArticleCount();

        $this->render('home/articles', [
            'pageTitle'        => 'Edukasi & Artikel Kesehatan - SmartHealthy',
            'articles'         => $articles,
            'categories'       => $categories,
            'activeCategoryId' => $categoryId,
        ]);
    }

    public function articleDetail(string $slug): void
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

    public function checkQueue(): void
    {
        $query = $this->input('q');
        $result = null;
        $searched = false;

        if ($query) {
            $searched = true;
            $regModel = new Registration();
            
            // Search by registration_code or user NIK
            $sql = "SELECT r.*, 
                           u.name as patient_name, u.nik as patient_nik,
                           hs.name as service_name, hs.doctor_name, hs.icon as service_icon
                    FROM `registrations` r
                    JOIN `users` u ON r.user_id = u.id
                    JOIN `health_services` hs ON r.health_service_id = hs.id
                    WHERE (r.registration_code = :q OR u.nik = :q2) 
                      AND r.registration_date >= CURRENT_DATE()
                    ORDER BY r.registration_date ASC, r.queue_number ASC LIMIT 1";

            $result = $regModel->queryOne($sql, ['q' => $query, 'q2' => $query]);
        }

        $this->render('home/check_queue', [
            'pageTitle' => 'Cek Status & Antrean Puskesmas - SmartHealthy',
            'query'     => $query,
            'result'    => $result,
            'searched'  => $searched,
        ]);
    }

    public function contact(): void
    {
        $this->render('home/contact', [
            'pageTitle' => 'Kontak & Lokasi Puskesmas Astanajapura - SmartHealthy',
            'puskesmas' => App::puskesmas(),
        ]);
    }
}
