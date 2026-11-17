# SmartHealthy - Sistem Informasi Pelayanan Kesehatan Puskesmas Berbasis Web dengan Integrasi AI

[![CI/CD Pipeline](https://github.com/faruqalbazz93-tech/Project_SmartHealtly/actions/workflows/ci.yml/badge.svg)](https://github.com/faruqalbazz93-tech/Project_SmartHealtly/actions/workflows/ci.yml)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.0-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%20%7C%20MariaDB-4479A1?style=flat&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap 5](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![PHPUnit Tests](https://img.shields.io/badge/PHPUnit-31%20Passed-brightgreen?style=flat&logo=php)](https://phpunit.de/)

> **Tugas UAS Tahap 3: Konstruksi Kode, Integrasi AI, Testing & CI/CD**  
> Repositori Resmi Proyek Aplikasi Web Pelayanan Puskesmas Astanajapura, Kabupaten Cirebon.

---

## 1. Nama Aplikasi
**SmartHealthy** (*Smart Puskesmas Healthcare Information & Triage System*)

---

## 2. Deskripsi Aplikasi
**SmartHealthy** adalah sistem informasi pelayanan kesehatan Puskesmas terpadu berbasis web yang mengadopsi arsitektur **MVC (Model-View-Controller)** murni dengan **PHP Native (OOP)**. Aplikasi ini dirancang untuk mengatasi antrean fisik konvensional di fasilitas kesehatan tingkat pertama (FKTP) Puskesmas Astanajapura melalui digitalisasi antrean mandiri, penerbitan e-Ticket, notifikasi status multi-kanal (WhatsApp Gateway Fonnte), artikel promosi kesehatan (Promkes), kuis interaktif perilaku hidup bersih dan sehat (PHBS), serta asisten cerdas **AI Chatbot & Triage Assist** bertenaga Google Gemini.

---

## 3. Tujuan Pembuatan Aplikasi
1. **Mengurangi Penumpukan Antrean**: Membantu pasien mendaftar poli secara online dari rumah sehingga mengurangi waktu tunggu fisik di Puskesmas.
2. **Pemerataan Pelayanan 12 Desa**: Memfasilitasi pendataan masyarakat berbasis domisili desa di wilayah binaan Puskesmas Astanajapura.
3. **Edukasi & Pencegahan Dini**: Menyediakan materi promkes resmi (pencegahan Stunting 1000 HPK, Gerakan 3M Plus DBD, hipertensi) dan kuis kesehatan interaktif.
4. **Integrasi Notifikasi Real-Time**: Mengirimkan pengingat kunjungan (H-1 dan 2 jam sebelum poli) serta informasi status antrean langsung ke WhatsApp pasien.
5. **Penerapan Teknologi Modern & AI**: Memenuhi standar rekayasa perangkat lunak modern dengan pengujian unit otomatis (PHPUnit) dan continuous integration (GitHub Actions).

---

## 4. Fitur-Fitur Aplikasi

### A. Fitur Pasien / Publik
* **Pendaftaran Pasien Online**: Registrasi akun terverifikasi dengan NIK 16-digit, nomor WhatsApp, email, dan pilihan desa domisili.
* **Booking Antrean Poli Mandiri**: Pemilihan tanggal kunjungan, jadwal dokter, dan poli tujuan (Poli Umum, Poli Gigi, KIA/KB, Poli MTBS/Anak, Lansia, Gizi, Laboratorium, Farmasi, IGD 24 Jam).
* **E-Ticket Digital**: Tiket pendaftaran digital lengkap dengan kode booking unik (`REG-AST-YYYYMMDD-XXXX`), nomor antrean, jadwal dokter, dan tombol cetak PDF.
* **Cek Antrean Publik**: Warga dapat memantau status antrean secara langsung tanpa login cukup dengan memasukkan kode booking atau NIK.
* **Asisten AI Chatbot**: Asisten kesehatan interaktif untuk menjawab pertanyaan seputar gejala awal penyakit, jadwal layanan, dan edukasi PHBS secara aman.
* **Artikel Promkes & Kuis PHBS**: Membaca artikel promosi kesehatan dan mengerjakan kuis interaktif dengan skor instan dan pembahasan medis dari AI.
* **Pusat Notifikasi & Riwayat Transaksi**: Notifikasi web interaktif dan log status antrean yang tersimpan permanen.

### B. Fitur Admin & Petugas Puskesmas
* **Dashboard Monitoring Antrean**: Pemantauan statistik harian (*Menunggu*, *Dipanggil*, *Diperiksa*, *Selesai*, *Dibatalkan*) secara real-time.
* **Panggilan Antrean Cepat**: Kontrol status antrean pasien di loket dan ruang periksa poli dengan pembaruan status sekali klik.
* **Manajemen WhatsApp Gateway**:
  * Pengaturan API Token Fonnte yang tersimpan terenkripsi.
  * Uji koneksi perangkat dan pengecekan kuota WhatsApp.
  * Uji pengiriman pesan tes mandiri ke nomor admin/tujuan.
  * Toggle otomatisasi notifikasi (pendaftaran, nomor antrean, perubahan status, pengingat H-1, pengingat 2 jam sebelum kunjungan, pembatalan).
  * Editor template pesan dengan placeholder dinamis (`{nama}`, `{nomor_pendaftaran}`, `{layanan}`, `{tanggal}`, `{jam}`, dll).
  * Tabel riwayat pengiriman WhatsApp dengan masking nomor tujuan demi privasi.
* **Manajemen Master Data**: Pengelolaan layanan poli, jadwal dokter, kapasitas kuota harian, desa binaan, artikel promkes, bank soal kuis, dan log interaksi AI.

---

## 5. Teknologi yang Gunakan

| Komponen | Teknologi / Library |
| :--- | :--- |
| **Backend Core** | PHP Native >= 8.0 (Object-Oriented Programming, Design Pattern MVC) |
| **Database** | MySQL 8.0 / MariaDB dengan PDO (Prepared Statements, Transaction Safe) |
| **Frontend UI** | HTML5, CSS3, JavaScript (Vanilla ES6+), Bootstrap 5.3, Bootstrap Icons |
| **WhatsApp Gateway** | Fonnte WhatsApp Business API |
| **AI Engine** | Google Gemini API (`gemini-2.5-flash` / `gemini-1.5-flash`) & Local Mock Engine |
| **Unit Testing** | PHPUnit 9.6 (Suite 31 Tests) |
| **CI/CD** | GitHub Actions Pipeline (`.github/workflows/ci.yml`) |
| **Local Web Server** | XAMPP (Apache httpd & MySQL Daemon) |

---

## 6. Struktur Folder Proyek

```
SmartHealthy/
├── .github/
│   └── workflows/
│       └── ci.yml               # Pipeline CI/CD GitHub Actions
├── app/
│   ├── Config/                  # Konfigurasi aplikasi & database
│   │   ├── App.php
│   │   └── Database.php
│   ├── Controllers/             # MVC Controllers
│   │   ├── AdminController.php
│   │   ├── ArticleController.php
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── HomeController.php
│   │   ├── NotificationController.php
│   │   ├── QuizController.php
│   │   ├── RegistrationController.php
│   │   └── WhatsAppController.php
│   ├── Core/                    # Framework Engine
│   │   ├── App.php              # Router & Request Dispatcher
│   │   ├── Auth.php             # Authentication & Role Guard
│   │   ├── Controller.php       # Base Controller
│   │   ├── Database.php         # Singleton PDO MySQL Connection
│   │   ├── Env.php              # Environment File (.env) Parser
│   │   ├── Model.php            # Base Model CRUD PDO
│   │   └── Session.php          # Session & Flash Message Manager
│   ├── Models/                  # Data Layer (23 Tables)
│   │   ├── AiLog.php
│   │   ├── HealthArticle.php
│   │   ├── HealthCategory.php
│   │   ├── HealthService.php
│   │   ├── Notification.php
│   │   ├── Question.php
│   │   ├── Registration.php
│   │   ├── Schedule.php
│   │   ├── User.php
│   │   ├── Village.php
│   │   ├── WhatsAppMessageLog.php
│   │   └── WhatsAppSetting.php
│   ├── Services/                # Service Layer
│   │   ├── AiService.php        # Google Gemini AI Integration
│   │   ├── FonnteWhatsAppService.php # WhatsApp Gateway Service
│   │   └── MailService.php
│   └── Views/                   # Blade-like Modular Views
│       ├── admin/               # Halaman kelola antrean, WhatsApp, poli, user
│       ├── auth/                # Halaman login, register, lupa sandi
│       ├── home/                # Landing page, cek antrean, layanan, promkes
│       ├── layouts/             # Template layout admin, user, & publik
│       ├── partials/            # Navbar, sidebar, footer, alert
│       └── user/                # Dashboard pasien, booking, e-ticket, kuis
├── database/
│   ├── migrations/              # DDL skema database
│   ├── seeds/                   # Data master awal
│   └── smarthealthy.sql         # Full Database Dump (Struktur + Seed Demo)
├── public/                      # Public Web Root
│   ├── assets/
│   ├── css/
│   ├── js/
│   └── index.php                # Front Controller
├── tests/                       # Unit Testing Suite (PHPUnit)
│   ├── Unit/
│   │   ├── AiChatbotServiceTest.php
│   │   ├── DatabaseConnectionTest.php
│   │   ├── InputValidationTest.php
│   │   ├── PatientManagementTest.php
│   │   ├── QueueRegistrationTest.php
│   │   ├── UserAuthenticationTest.php
│   │   ├── UserRegistrationTest.php
│   │   └── WhatsAppNotificationTest.php
│   └── bootstrap.php
├── .env.example                 # Template environment variables
├── .gitignore                   # File & direktori yang diabaikan Git
├── composer.json                # Dependensi PHP & autoloader PSR-4
├── index.php                    # Root entrypoint redirector
├── phpunit.xml                  # Konfigurasi runner pengujian PHPUnit
└── README.md                    # Dokumentasi lengkap proyek
```

---

## 7. Persyaratan Instalasi

Pastikan perangkat komputer Anda telah terpasang:
1. **XAMPP** dengan versi **PHP 8.0 atau lebih tinggi** (disarankan PHP 8.0, 8.1, atau 8.2).
2. Ekstensi PHP aktif: `pdo_mysql`, `curl`, `mbstring`, `json`, `openssl`.
3. **Web Browser** modern (Google Chrome, Mozilla Firefox, Microsoft Edge).
4. **Git** (opsional, untuk clone repositori).
5. **Composer** (opsional, jika ingin mengunduh dependensi testing ulang).

---

## 8. Cara Menjalankan Aplikasi Menggunakan XAMPP

1. **Letakkan Folder Proyek di htdocs**:
   Salin atau clone folder repositori ini ke dalam direktori `htdocs` XAMPP Anda:
   ```
   C:\xampp\htdocs\SmartHealty
   ```
   *(Pastikan nama folder adalah `SmartHealty` atau sesuaikan `APP_URL` di file `.env`)*

2. **Jalankan Apache dan MySQL**:
   * Buka aplikasi **XAMPP Control Panel**.
   * Klik tombol **Start** pada modul **Apache**.
   * Klik tombol **Start** pada modul **MySQL**.
   * Pastikan kedua modul berwarna hijau dengan port `80/443` (Apache) dan `3306` (MySQL).

3. **Buka Aplikasi di Browser**:
   Buka peramban Anda dan kunjungi alamat:
   ```
   http://localhost/SmartHealty/
   ```

---

## 9. Cara Membuat Database Melalui phpMyAdmin

1. Buka browser dan akses **phpMyAdmin**:
   ```
   http://localhost/phpmyadmin/
   ```
2. Klik tab menu **New** (Baru) di bilah navigasi sebelah kiri.
3. Masukkan nama database:
   ```
   smarthealthy
   ```
   Pilih collation: `utf8mb4_general_ci`, lalu klik **Create** (Buat).
4. Klik database `smarthealthy` yang baru dibuat.
5. Klik tab **Import** pada bagian atas layar.
6. Klik tombol **Choose File** (Pilih File) dan arahkan ke file:
   ```
   database/smarthealthy.sql
   ```
7. Gulir ke bawah dan klik tombol **Import** (Kirim).
8. Tunggu hingga muncul pesan hijau sukses: *"Import has been successfully finished"*. Seluruh 23 tabel relasional beserta data awal Puskesmas telah siap digunakan.

---

## 10. Cara Mengatur Konfigurasi Database

1. Pada direktori root proyek (`C:\xampp\htdocs\SmartHealty`), duplikasi file `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
2. Buka file `.env` menggunakan teks editor (VS Code, Notepad++, Sublime Text).
3. Sesuaikan parameter koneksi database Anda:
   ```ini
   APP_NAME="SmartHealthy"
   APP_ENV=local
   APP_DEBUG=true
   APP_URL="http://localhost/SmartHealty"

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=smarthealthy
   DB_USERNAME=root
   DB_PASSWORD=
   ```
4. Simpan file `.env`. Aplikasi SmartHealthy otomatis terhubung menggunakan PDO singleton yang aman.

### Akun Bawaan untuk Pengujian (Seeded Demo Accounts):
| Role | Email | Password | Keterangan |
| :--- | :--- | :--- | :--- |
| **Admin Petugas** | `admin@smarthealthy.id` | `Astanajapura@2026!` | Akses penuh dashboard admin & WhatsApp Gateway |
| **Pasien Warga** | `pasien@smarthealthy.id` | `SehatAstanajapura#2026` | Akun pasien terverifikasi dari Desa Astanajapura |

---

## 11. Cara Menghubungkan API AI (Google Gemini)

Aplikasi SmartHealthy dilengkapi fitur asisten kesehatan cerdas menggunakan **Google Gemini API**.

1. **Dapatkan API Key Gratis**:
   Kunjungi [Google AI Studio](https://aistudio.google.com/) dan buat API Key baru.
2. **Masukkan ke File `.env`**:
   Buka file `.env` pada baris integrasi AI:
   ```ini
   AI_PROVIDER=gemini
   AI_MODEL=gemini-2.5-flash
   GEMINI_API_KEY=AIzaSy...masukkan_api_key_anda_disini...
   ```
3. **Mode Simulasi (Mock Fallback)**:
   Jika Anda belum memiliki API Key atau kuota habis, setel:
   ```ini
   AI_PROVIDER=mock
   ```
   Sistem SmartHealthy akan otomatis menggunakan *rule-based medical safety engine* lokal sehingga seluruh fitur aplikasi tetap berjalan 100% normal tanpa error.

---

## 12. Cara Menjalankan Unit Testing

Proyek ini telah dilengkapi **31 skenario Unit Testing** menggunakan **PHPUnit 9.6** yang mencakup pengujian koneksi database PDO, validasi form, hashing autentikasi, transaksi pendaftaran antrean, manajemen profil pasien, AI service fallback, dan WhatsApp template rendering.

Jalankan perintah berikut di terminal:
```bash
# Menggunakan phpunit.phar bawaan:
php phpunit.phar --configuration phpunit.xml --testdox

# Atau jika menggunakan Composer:
vendor/bin/phpunit --configuration phpunit.xml --testdox
```

### Hasil Eksekusi Testing:
```text
PHPUnit 9.6.39 by Sebastian Bergmann and contributors.

AiChatbotService (Tests\Unit\AiChatbotService)
 ✔ Ai service instantiates properly
 ✔ Fallback mock response when key not configured
 ✔ Chat returns string with or without config
 ✔ Health prompt sanitization and safety grounding

DatabaseConnection (Tests\Unit\DatabaseConnection)
 ✔ Pdo connection succeeds
 ✔ Database connection singleton pattern
 ✔ Database error mode is exception
 ✔ Basic table query executes

InputValidation (Tests\Unit\InputValidation)
 ✔ Required validation passes and fails
 ✔ Email format validation
 ✔ Indonesian phone format validation
 ✔ Password confirmation matching
 ✔ Nik sixteen digit format validation

PatientManagement (Tests\Unit\PatientManagement)
 ✔ Find patient with village details
 ✔ Update patient profile information
 ✔ Change user password with bcrypt
 ✔ User cannot change password with incorrect old password

QueueRegistration (Tests\Unit\QueueRegistration)
 ✔ Create registration transaction
 ✔ Queue status transitions
 ✔ Duplicate booking prevention

UserAuthentication (Tests\Unit\UserAuthentication)
 ✔ Admin authentication succeeds
 ✔ Patient authentication succeeds
 ✔ Authentication rejects invalid password
 ✔ Authentication rejects nonexistent email
 ✔ Auth session state

UserRegistration (Tests\Unit\UserRegistration)
 ✔ Patient registration succeeds
 ✔ Duplicate email registration rejected
 ✔ Password is properly hashed with bcrypt
 ✔ Find user by phone number
 ✔ Find user by nik

WhatsAppNotification (Tests\Unit\WhatsAppNotification)
 ✔ Phone formatting indonesian standard
 ✔ Template rendering with placeholders
 ✔ Reminder template rendering
 ✔ Token masking security

OK (31 tests, 99 assertions)
```

---

## 13. Informasi Konfigurasi GitHub Actions (CI/CD)

Continuous Integration (CI) dikonfigurasikan pada file `.github/workflows/ci.yml`. Pipeline ini berjalan secara otomatis setiap kali ada `push` atau `pull_request` ke cabang `main` atau `master`.

### Tahapan Pipeline CI:
1. **Runner Environment**: Ubuntu Linux (`ubuntu-latest`).
2. **Service Container MySQL 8.0**: Menjalankan instans database MySQL resmi secara otomatis di runner GitHub.
3. **Setup PHP 8.0**: Mengaktifkan modul PHP yang diperlukan (`pdo`, `pdo_mysql`, `curl`, `mbstring`, `json`).
4. **Database Import**: Mengimpor struktur dan seed data dari `database/smarthealthy.sql`.
5. **Composer Install**: Menginstal dependensi PHPUnit dengan caching vendor otomatis.
6. **Linter Syntax Check**: Menjalankan `php -l` pada seluruh file controller, model, service, dan core PHP untuk menjamin 0 syntax error.
7. **PHPUnit Execution**: Menjalankan seluruh test suite dengan `AI_PROVIDER=mock` untuk memastikan stabilitas dan keandalan sistem.

---

## 14. Panduan Mengunggah Proyek ke GitHub

Ikuti langkah-langkah di bawah ini untuk mengunggah proyek ke akun GitHub Anda:

1. **Buka Terminal / Git Bash di folder proyek**:
   ```bash
   cd C:\xampp\htdocs\SmartHealty
   ```

2. **Inisialisasi Git & Periksa Status**:
   ```bash
   git init
   git status
   ```
   *(Pastikan file `.env` dan `vendor/` tidak ikut terpilih karena telah diatur di `.gitignore`)*

3. **Tambahkan Seluruh File & Buat Commit Perdana**:
   ```bash
   git add .
   git commit -m "feat: complete SmartHealthy puskesmas system with AI integration, unit testing and CI/CD pipeline"
   ```

4. **Ubah Branch Utama ke `main`**:
   ```bash
   git branch -M main
   ```

5. **Hubungkan ke Repositori GitHub Anda**:
   Buat repositori baru di [GitHub](https://github.com/new) dengan nama `SmartHealthy`, lalu hubungkan remote repository:
   ```bash
   git remote add origin https://github.com/faruqalbazz93-tech/Project_SmartHealtly.git
   ```
   *(Ganti `USERNAME_ANDA` dengan username akun GitHub Anda)*

6. **Push ke GitHub**:
   ```bash
   git push -u origin main
   ```

Setelah proses push selesai, kunjungi repositori GitHub Anda dan buka tab **Actions** untuk melihat workflow CI/CD berjalan secara otomatis hingga mendapatkan status **Passing (Hijau)**.

---

## 📄 Lisensi & Hak Cipta
Dikembangkan untuk keperluan **UAS Tahap 3: Konstruksi Kode, Integrasi AI, Testing & CI/CD**.  
© 2026 **Tim Pengembang SmartHealthy** - Puskesmas Astanajapura. Seluruh Hak Cipta Dilindungi.
