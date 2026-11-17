-- ==========================================================
-- Seeder Data: SmartHealthy
-- Data Awal Puskesmas Astanajapura, Kabupaten Cirebon
-- ==========================================================

USE `smarthealthy`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Seeder Desa Wilayah Astanajapura (12 Desa)
TRUNCATE TABLE `villages`;
INSERT INTO `villages` (`id`, `name`, `district`, `postal_code`) VALUES
(1, 'Astanajapura', 'Astanajapura', '45181'),
(2, 'Buntet', 'Astanajapura', '45181'),
(3, 'Japurabakti', 'Astanajapura', '45181'),
(4, 'Japuralor', 'Astanajapura', '45181'),
(5, 'Japura Kidul', 'Astanajapura', '45181'),
(6, 'Kanci', 'Astanajapura', '45181'),
(7, 'Kanci Kulon', 'Astanajapura', '45181'),
(8, 'Kendal', 'Astanajapura', '45181'),
(9, 'Mertapada Kulon', 'Astanajapura', '45181'),
(10, 'Mertapada Wetan', 'Astanajapura', '45181'),
(11, 'Munjul', 'Astanajapura', '45181'),
(12, 'Sidamulya', 'Astanajapura', '45181');

-- 2. Seeder Users (Admin, Petugas, dan Pasien Demo Terverifikasi)
TRUNCATE TABLE `users`;
INSERT INTO `users` (`id`, `nik`, `name`, `email`, `phone`, `address`, `village_id`, `gender`, `birth_date`, `role`, `password`, `email_verified_at`, `phone_verified_at`, `is_active`) VALUES
(1, '3209140101900001', 'Admin Puskesmas Astanajapura', 'admin@smarthealthy.id', '081234567890', 'Komplek Puskesmas Astanajapura', 1, 'L', '1990-01-01', 'admin', '$2y$10$rPwYHQ7zgSlM29jQpEOB9eHAIYqY/Rabs4ioazxc1ZBFmsFBnYO/a', '2026-01-01 08:00:00', '2026-01-01 08:00:00', 1),
(2, '3209141205950002', 'Ahmad Fauzi', 'pasien@smarthealthy.id', '085712345678', 'Blok Manis RT 02 RW 01, Desa Buntet', 2, 'L', '1995-05-12', 'user', '$2y$10$Y2GnlKE/9c1vfZkQ9kfygu76iXaumZU43drYGmwA6GhtMJHRW/lG2', '2026-01-01 09:00:00', '2026-01-01 09:00:00', 1),
(3, '3209144809980003', 'Siti Rahmawati', 'siti@smarthealthy.id', '087812345679', 'Blok Pahing RT 03 RW 02, Desa Mertapada Kulon', 9, 'P', '1998-09-08', 'user', '$2y$10$Y2GnlKE/9c1vfZkQ9kfygu76iXaumZU43drYGmwA6GhtMJHRW/lG2', '2026-01-01 09:30:00', '2026-01-01 09:30:00', 1),
(4, '3209141503800004', 'Budi Santoso', 'budi@smarthealthy.id', '081398765432', 'Blok Wage RT 01 RW 04, Desa Kanci', 6, 'L', '1980-03-15', 'user', '$2y$10$Y2GnlKE/9c1vfZkQ9kfygu76iXaumZU43drYGmwA6GhtMJHRW/lG2', NULL, NULL, 1);

-- 3. Seeder Layanan / Poli Kesehatan
TRUNCATE TABLE `health_services`;
INSERT INTO `health_services` (`id`, `code`, `name`, `description`, `doctor_name`, `quota_daily`, `icon`, `status`) VALUES
(1, 'POLI-UMUM', 'Poli Umum', 'Pemeriksaan kesehatan dasar, pengobatan umum, rujukan medis awal, dan konsultasi kesehatan keluarga.', 'dr. H. Rahmat Hidayat, M.Kes', 60, 'bi-hospital', 'active'),
(2, 'POLI-GIGI', 'Poli Gigi & Mulut', 'Pemeriksaan gigi, pencabutan gigi, penambalan, pembersihan karang gigi (scaling), dan pengobatan infeksi mulut.', 'drg. Nurul Annisa', 30, 'bi-emoji-smile', 'active'),
(3, 'POLI-KIA', 'Poli KIA & KB', 'Pelayanan Kesehatan Ibu dan Anak, pemeriksaan kehamilan (ANC), imunisasi bayi, KB, dan deteksi dini risiko tinggi kehamilan.', 'Bdn. Hj. Siti Maryam, S.ST', 40, 'bi-heart-pulse', 'active'),
(4, 'POLI-ANAK', 'Poli Anak & MTBS', 'Manajemen Terpadu Balita Sakit (MTBS), pemeriksaan tumbuh kembang balita, pengobatan infeksi saluran napas, dan diare.', 'dr. Farhan Maulana, Sp.A', 40, 'bi-person-hearts', 'active'),
(5, 'POLI-LANSIA', 'Poli Lansia & PTM', 'Pelayanan kesehatan khusus lanjut usia, pemantauan hipertensi, diabetes melitus, jantung, dan skrining penyakit tidak menular.', 'dr. Maya Sartika', 35, 'bi-person-walking', 'active'),
(6, 'POLI-GIZI', 'Poli Konseling Gizi', 'Konsultasi gizi balita kurus/stunting, diet hipertensi, diabetes, gizi ibu hamil, dan edukasi pola makan seimbang.', 'Nuraini, S.Gz', 25, 'bi-apple', 'active'),
(7, 'LAB-PUSKESMAS', 'Laboratorium Sederhana', 'Pemeriksaan darah lengkap, gula darah, asam urat, kolesterol, tes urine, tes kehamilan, dan pemeriksaan dahak TB.', 'Ahmad Sanusi, A.Md.AK', 50, 'bi-eyedropper', 'active'),
(8, 'FARMASI', 'Farmasi & Apotek', 'Pelayanan peresepan obat gratis standar JKN/BPJS dan pasien umum dengan edukasi aturan pakai obat.', 'Apt. Dian Permata, S.Farm', 100, 'bi-capsule', 'active');

-- 4. Seeder Jadwal Operasional Poli
TRUNCATE TABLE `schedules`;
INSERT INTO `schedules` (`health_service_id`, `day_of_week`, `start_time`, `end_time`, `max_quota`, `is_active`) VALUES
(1, 'Senin', '08:00:00', '12:00:00', 60, 1),
(1, 'Selasa', '08:00:00', '12:00:00', 60, 1),
(1, 'Rabu', '08:00:00', '12:00:00', 60, 1),
(1, 'Kamis', '08:00:00', '12:00:00', 60, 1),
(1, 'Jumat', '08:00:00', '11:00:00', 40, 1),
(1, 'Sabtu', '08:00:00', '11:30:00', 40, 1),
(2, 'Senin', '08:30:00', '12:00:00', 30, 1),
(2, 'Selasa', '08:30:00', '12:00:00', 30, 1),
(2, 'Rabu', '08:30:00', '12:00:00', 30, 1),
(2, 'Kamis', '08:30:00', '12:00:00', 30, 1),
(3, 'Senin', '08:00:00', '12:00:00', 40, 1),
(3, 'Rabu', '08:00:00', '12:00:00', 40, 1),
(3, 'Jumat', '08:00:00', '11:00:00', 30, 1),
(5, 'Selasa', '08:00:00', '11:30:00', 35, 1),
(5, 'Kamis', '08:00:00', '11:30:00', 35, 1);

-- 5. Seeder Kategori Edukasi Kesehatan
TRUNCATE TABLE `health_categories`;
INSERT INTO `health_categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Kesehatan Ibu & Anak', 'kesehatan-ibu-anak', 'Panduan tumbuh kembang, imunisasi, gizi balita, dan pencegahan stunting di pedesaan.'),
(2, 'Pola Hidup Bersih & Sehat (PHBS)', 'phbs', 'Edukasi kebiasaan hidup sehat masyarakat Astanajapura, cuci tangan, dan sanitasi lingkungan.'),
(3, 'Penyakit Tidak Menular (PTM)', 'penyakit-tidak-menular', 'Pencegahan hipertensi, diabetes melitus, asam urat, dan stroke pada usia produktif serta lansia.'),
(4, 'Pencegahan Penyakit Menular', 'penyakit-menular', 'Antisipasi Demam Berdarah (DBD), TBC, Infeksi Saluran Pernapasan (ISPA), dan penyakit musiman.');

-- 6. Seeder Artikel Edukasi Kesehatan
TRUNCATE TABLE `health_articles`;
INSERT INTO `health_articles` (`id`, `category_id`, `title`, `slug`, `summary`, `content`, `views_count`, `status`) VALUES
(1, 1, 'Pencegahan Stunting pada 1000 Hari Pertama Kehidupan di Astanajapura', 'pencegahan-stunting-1000-hpk-astanajapura', 'Pentingnya nutrisi seimbang bagi ibu hamil dan baduta untuk mencetak generasi cerdas dan sehat di wilayah Cirebon.', '<p>Stunting adalah kondisi gagal tumbuh pada anak balita akibat kekurangan gizi kronis, terutama pada <strong>1.000 Hari Pertama Kehidupan (HPK)</strong> yaitu sejak janin dalam kandungan hingga anak berusia 2 tahun.</p><h4>Langkah Pencegahan Utama:</h4><ul><li>Pemeriksaan kehamilan rutin (minimal 6 kali) di Puskesmas Astanajapura atau Posyandu desa.</li><li>Konsumsi tablet tambah darah (TTD) bagi ibu hamil dan remaja putri.</li><li>Pemberian ASI Eksklusif selama 6 bulan pertama tanpa makanan tambahan.</li><li>Pemberian MP-ASI bergizi seimbang berbahan pangan lokal mulai usia 6 bulan.</li><li>Memastikan sanitasi air bersih dan jamban sehat di lingkungan rumah.</li></ul><p>Warga dapat berkonsultasi langsung secara gratis di <em>Poli Konseling Gizi Puskesmas Astanajapura</em> setiap hari kerja.</p>', 142, 'published'),
(2, 2, 'Gerakan PHBS: 5 Langkah Efektif Bebas Jentik Nyamuk DBD di Lingkungan Desa', 'gerakan-phbs-bebas-jentik-nyamuk-dbd', 'Kenali tanda bahaya Demam Berdarah Dengue dan terapkan metode 3M Plus di rumah Anda.', '<p>Memasuki musim penghujan, potensi perkembangbiakan nyamuk <em>Aedes aegypti</em> meningkat di lingkungan pemukiman. Puskesmas Astanajapura mengajak seluruh warga untuk aktif melakukan Gerakan Satu Rumah Satu Jumantik.</p><h4>Metode 3M Plus:</h4><ol><li><strong>Menguras</strong> tempat penampungan air seperti bak mandi dan tempayan secara berkala.</li><li><strong>Menutup</strong> rapat tempat-tempat penampungan air minum atau tandon.</li><li><strong>Mendaur ulang</strong> atau memanfaatkan kembali barang bekas yang berpotensi menampung air hujan.</li><li><strong>Plus:</strong> Menaburkan bubuk larvasida (abate) yang dapat diperoleh gratis di Puskesmas.</li></ol>', 98, 'published'),
(3, 3, 'Waspada Tekanan Darah Tinggi: Skrining Gratis Rutin di Posbindu PTM', 'waspada-tekanan-darah-tinggi-skrining-gratis', 'Hipertensi sering dijuluki Silent Killer. Mari ketahui batas normal tensi dan cara mengendalikannya.', '<p>Hipertensi terjadi ketika tekanan darah berada pada angka 140/90 mmHg atau lebih tinggi secara konsisten. Seringkali hipertensi tidak menunjukkan gejala awal, sehingga pemeriksaan rutin sangatlah penting.</p><h4>Tips Mengontrol Tekanan Darah dengan CERDIK:</h4><ul><li><strong>C:</strong> Cek kesehatan secara berkala di Puskesmas Astanajapura.</li><li><strong>E:</strong> Enyahkan asap rokok.</li><li><strong>R:</strong> Rajin aktivitas fisik minimal 30 menit per hari.</li><li><strong>D:</strong> Diet seimbang, batasi garam maksimal 1 sendok teh per hari.</li><li><strong>I:</strong> Istirahat cukup (7-8 jam per malam).</li><li><strong>K:</strong> Kelola stres dengan baik.</li></ul>', 175, 'published');

-- 7. Seeder Bank Soal Kuis Kesehatan
TRUNCATE TABLE `questions`;
INSERT INTO `questions` (`id`, `category_id`, `question_text`, `explanation`, `points`, `status`) VALUES
(1, 1, 'Berapa lama durasi ideal pemberian Air Susu Ibu (ASI) Eksklusif pada bayi?', 'ASI Eksklusif diberikan selama 6 bulan pertama tanpa tambahan makanan atau minuman lain, kemudian dilanjutkan bersama MP-ASI hingga anak berusia 2 tahun.', 20, 'active'),
(2, 2, 'Berapa langkah cuci tangan pakai sabun (CTPS) yang direkomendasikan oleh WHO?', 'WHO merekomendasikan 6 langkah cuci tangan dengan air mengalir dan sabun selama minimal 20-40 detik.', 20, 'active'),
(3, 3, 'Berapa batas maksimal konsumsi garam harian yang dianjurkan per orang?', 'Kementerian Kesehatan menganjurkan konsumsi garam maksimal 1 sendok teh (5 gram) per orang per hari untuk mencegah hipertensi.', 20, 'active'),
(4, 2, 'Apa kepanjangan dari prinsip 3M Plus dalam pencegahan DBD?', '3M Plus adalah Menguras, Menutup, dan Mendaur ulang barang bekas, plus langkah pencegahan gigitan nyamuk lainnya.', 20, 'active'),
(5, 1, 'Kapan periode emas 1000 Hari Pertama Kehidupan (HPK) berlangsung?', '1000 HPK dihitung sejak awal masa kehamilan (270 hari) hingga anak mencapai usia 2 tahun (730 hari).', 20, 'active');

-- 8. Seeder Opsi Jawaban Soal Kuis
TRUNCATE TABLE `question_options`;
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(1, '3 Bulan', 0),
(1, '6 Bulan', 1),
(1, '9 Bulan', 0),
(1, '12 Bulan', 0),
(2, '4 Langkah', 0),
(2, '6 Langkah', 1),
(2, '8 Langkah', 0),
(2, '10 Langkah', 0),
(3, '1 Sendok Teh (5 gram)', 1),
(3, '3 Sendok Teh (15 gram)', 0),
(3, '1 Sendok Makan', 0),
(3, 'Bebas tanpa batas', 0),
(4, 'Mencuci, Mengeringkan, Menggiling', 0),
(4, 'Menguras, Menutup, Mendaur ulang', 1),
(4, 'Menyapu, Mengepel, Membakar', 0),
(4, 'Melihat, Meraba, Menerawang', 0),
(5, 'Sejak lahir hingga usia 5 tahun', 0),
(5, 'Sejak awal kehamilan hingga anak usia 2 tahun', 1),
(5, 'Dari usia 1 tahun sampai 3 tahun', 0),
(5, 'Masa remaja usia 10-18 tahun', 0);

-- 9. Seeder Pendaftaran Antrean Demo (Status: Completed, Confirmed, Pending)
TRUNCATE TABLE `registrations`;
INSERT INTO `registrations` (`id`, `registration_code`, `user_id`, `health_service_id`, `schedule_id`, `registration_date`, `queue_number`, `status`, `complaint`, `notes`) VALUES
(1, 'REG-AST-20260918-001', 2, 1, 1, CURRENT_DATE(), 1, 'Completed', 'Demam dan flu sejak 2 hari yang lalu, badan terasa lemas.', 'Pasien telah diperiksa, diberikan paracetamol dan vitamin.'),
(2, 'REG-AST-20260918-002', 3, 3, 7, CURRENT_DATE(), 2, 'Confirmed', 'Pemeriksaan kehamilan trimester kedua rutin.', 'Menunggu di ruang konsultasi KIA.'),
(3, 'REG-AST-20260918-003', 4, 5, 9, CURRENT_DATE(), 3, 'Pending', 'Kontrol rutin tensi darah tinggi dan cek gula darah berkala.', 'Antrean nomor 003 Poli Lansia.');

-- 10. Seeder Notifikasi Pengguna & Pasien
TRUNCATE TABLE `notifications`;
INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `target_url`, `is_read`, `created_at`, `read_at`) VALUES
(1, 2, 'account', 'Selamat Datang di Portal SmartHealthy', 'Halo Ahmad Fauzi, akun Anda telah berhasil didaftarkan di sistem antrean online Puskesmas Astanajapura. Nikmati kemudahan pendaftaran poli dan tiket digital.', '/profil', 1, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 2, 'registration', 'Pendaftaran Antrean Berhasil Disimpan', 'Pendaftaran Anda di Poli Umum untuk tanggal hari ini berhasil disimpan dengan Nomor Antrean #001. Kode Booking: REG-AST-20260918-001.', '/pendaftaran/tiket/REG-AST-20260918-001', 1, DATE_SUB(NOW(), INTERVAL 5 HOUR), DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(3, 2, 'registration', 'Pemeriksaan Selesai', 'Pemeriksaan Anda di Poli Umum telah selesai. Terima kasih atas kunjungan Anda di Puskesmas Astanajapura. Simpan bukti e-Ticket untuk arsip Anda.', '/pendaftaran/tiket/REG-AST-20260918-001', 0, DATE_SUB(NOW(), INTERVAL 2 HOUR), NULL),
(4, 2, 'schedule', 'Pengingat Kunjungan Hari Ini: Poli Umum', 'Halo Ahmad Fauzi, hari ini adalah jadwal kunjungan Anda di Poli Umum dengan Nomor Antrean #001. Harap hadir 15 menit sebelum waktu pelayanan.', '/pendaftaran/tiket/REG-AST-20260918-001', 0, DATE_SUB(NOW(), INTERVAL 4 HOUR), NULL),
(5, 2, 'education', 'Materi Edukasi Kesehatan Baru: Pencegahan Stunting 1000 HPK', 'Puskesmas Astanajapura menerbitkan artikel edukasi baru mengenai Kesehatan Ibu & Anak. Simak panduan lengkap pencegahan stunting pada balita.', '/artikel/pencegahan-stunting-1000-hpk-astanajapura', 0, DATE_SUB(NOW(), INTERVAL 1 DAY), NULL),
(6, 3, 'account', 'Alamat Email Berhasil Diverifikasi', 'Alamat email akun SmartHealthy Anda telah berhasil diverifikasi. Akun Anda kini memiliki akses penuh ke seluruh fitur kesehatan.', '/profil', 1, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(7, 3, 'registration', 'Pendaftaran Antrean Dikonfirmasi', 'Pendaftaran Anda di Poli KIA & KB (#002) telah DIKONFIRMASI oleh petugas Puskesmas Astanajapura. Silakan menunggu di ruang konsultasi KIA.', '/pendaftaran/tiket/REG-AST-20260918-002', 0, DATE_SUB(NOW(), INTERVAL 1 HOUR), NULL),
(8, 3, 'announcement', 'Pengumuman Pelayanan Imunisasi Balita', 'Puskesmas Astanajapura menyelenggarakan pelayanan imunisasi dasar lengkap serentak di Posyandu wilayah Desa Mertapada Kulon.', '/layanan', 0, DATE_SUB(NOW(), INTERVAL 6 HOUR), NULL),
(9, 1, 'announcement', 'Pemberitahuan Sistem SmartHealthy', 'Selamat datang di Panel Administrasi Petugas Puskesmas Astanajapura. Pantau antrean pasien dan publikasikan jadwal layanan secara berkala.', '/admin', 0, NOW(), NULL);

-- 11. Seeder Deduplication Keys
TRUNCATE TABLE `notification_dedup`;
INSERT INTO `notification_dedup` (`user_id`, `dedup_key`, `channel`, `created_at`) VALUES
(2, 'account_reg_2', 'app', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 'reg_created_1', 'app', DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(3, 'email_verified_3', 'app', DATE_SUB(NOW(), INTERVAL 2 DAY));

SET FOREIGN_KEY_CHECKS = 1;
