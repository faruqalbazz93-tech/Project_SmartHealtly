<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Core\Validator;
use App\Core\RateLimiter;
use App\Core\Env;
use App\Models\User;
use App\Models\Village;
use App\Models\VerificationToken;
use App\Models\PasswordReset;
use App\Services\WhatsAppService;
use App\Services\MailService;

class AuthController extends Controller
{
    /**
     * Display Registration Form
     */
    public function showRegister(): void
    {
        Auth::requireGuest();

        $villageModel = new Village();
        $villages = $villageModel->allOrdered();

        $this->render('auth/register', [
            'pageTitle' => 'Daftar Akun Pasien Baru - SmartHealthy',
            'villages'  => $villages,
        ], 'layouts/auth');
    }

    /**
     * Process Registration
     */
    public function register(): void
    {
        Auth::requireGuest();

        if (!$this->validateCsrf()) {
            return;
        }

        $name = $this->input('name');
        $email = strtolower(trim((string)$this->input('email')));
        $phone = $this->input('phone');
        $villageId = $this->input('village_id');
        $password = $this->input('password');
        $passwordConfirm = $this->input('password_confirm');

        // Server-Side Validation
        $validator = Validator::make([
            'name'             => $name,
            'email'            => $email,
            'phone'            => $phone,
            'village_id'       => $villageId,
            'password'         => $password,
            'password_confirm' => $passwordConfirm,
        ], [
            'name'             => 'required|min:3|max:150',
            'email'            => 'required|email|unique:users,email',
            'phone'            => 'required|whatsapp|unique:users,phone',
            'village_id'       => 'required|exists:villages,id',
            'password'         => 'required|min:6',
            'password_confirm' => 'required|matches:password',
        ]);

        if ($validator->fails()) {
            $this->flashAndRedirect('danger', $validator->firstError(), '/register');
            return;
        }

        $userModel = new User();
        $tokenModel = new VerificationToken();

        // Create unverified user
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$phone);

        $newUserId = $userModel->create([
            'name'              => $name,
            'email'             => $email,
            'phone'             => $cleanPhone,
            'village_id'        => (int) $villageId,
            'role'              => 'user',
            'password'          => $hashedPassword,
            'email_verified_at' => null,
            'phone_verified_at' => null,
            'is_active'         => 1,
        ]);

        if (!$newUserId) {
            $this->flashAndRedirect('danger', 'Gagal mendaftarkan akun. Silakan periksa kembali isian form.', '/register');
            return;
        }

        $user = $userModel->find($newUserId);
        Auth::login($user);

        // Generate and dispatch Email verification token
        $emailToken = $tokenModel->createEmailToken((int)$newUserId);
        MailService::sendVerificationEmail($email, $name, $emailToken);

        // Generate and dispatch WhatsApp OTP
        $otpCode = $tokenModel->createWhatsAppOtp((int)$newUserId);
        WhatsAppService::sendOtp($cleanPhone, $otpCode, $name);

        // Send Welcome & Account Created in-app notification
        \App\Services\NotificationService::notifyAccountRegistered((int)$newUserId, $name);

        Session::setFlash('success', "Akun Anda berhasil dibuat! Silakan verifikasi nomor WhatsApp Anda dengan memasukkan 6-digit kode OTP.");
        $this->redirect('/verifikasi/otp');
    }

    /**
     * Display Verification Hub
     */
    public function showVerificationHub(): void
    {
        Auth::requireAuth();

        $user = Auth::user();
        $tokenModel = new VerificationToken();
        $activeOtp = $tokenModel->getLatestActiveOtp((int)$user['id']);

        $this->render('auth/verify_hub', [
            'pageTitle' => 'Pusat Verifikasi Akun - SmartHealthy',
            'user'      => $user,
            'activeOtp' => $activeOtp,
            'lastMockWa' => WhatsAppService::getLastMockMessage(),
            'lastMockMail' => MailService::getLastMockMail(),
        ], 'layouts/user');
    }

    /**
     * Display WhatsApp OTP Verification Form
     */
    public function showVerifyOtp(): void
    {
        Auth::requireAuth();

        $user = Auth::user();
        if (Auth::isPhoneVerified()) {
            $this->flashAndRedirect('info', 'Nomor WhatsApp Anda sudah terverifikasi.', '/dashboard');
            return;
        }

        $tokenModel = new VerificationToken();
        $activeOtp = $tokenModel->getLatestActiveOtp((int)$user['id']);

        // If no active OTP exists, generate one automatically
        if (!$activeOtp) {
            $newOtp = $tokenModel->createWhatsAppOtp((int)$user['id']);
            WhatsAppService::sendOtp($user['phone'], $newOtp, $user['name']);
            $activeOtp = $tokenModel->getLatestActiveOtp((int)$user['id']);
        }

        $this->render('auth/verify_otp', [
            'pageTitle'    => 'Verifikasi OTP WhatsApp - SmartHealthy',
            'user'         => $user,
            'activeOtp'    => $activeOtp,
            'lastMockWa'   => WhatsAppService::getLastMockMessage(),
        ], 'layouts/auth');
    }

    /**
     * Process WhatsApp OTP Verification
     */
    public function verifyOtp(): void
    {
        Auth::requireAuth();

        if (!$this->validateCsrf()) {
            return;
        }

        $userId = Auth::id();
        $otp = trim((string)$this->input('otp'));

        if (empty($otp) || strlen($otp) !== 6 || !ctype_digit($otp)) {
            $this->flashAndRedirect('danger', 'Masukkan 6 digit angka kode OTP WhatsApp.', '/verifikasi/otp');
            return;
        }

        $tokenModel = new VerificationToken();
        $result = $tokenModel->verifyWhatsAppOtp($userId, $otp);

        if ($result['status']) {
            $userModel = new User();
            $userModel->markPhoneVerified($userId);
            \App\Services\NotificationService::notifyPhoneVerified((int)$userId);
            
            Session::setFlash('success', 'Selamat! Nomor WhatsApp Anda telah berhasil diverifikasi.');
            $this->redirect('/dashboard');
        } else {
            $this->flashAndRedirect('danger', $result['message'], '/verifikasi/otp');
        }
    }

    /**
     * Resend WhatsApp OTP
     */
    public function resendOtp(): void
    {
        Auth::requireAuth();

        if (!$this->validateCsrf()) {
            return;
        }

        $userId = Auth::id();
        $user = Auth::user();

        // Rate limit: max 3 resends per 60 seconds
        $rateKey = "resend_otp_{$userId}";
        $maxResends = (int) Env::get('RATE_LIMIT_OTP_MAX', 3);
        $decay = (int) Env::get('RATE_LIMIT_OTP_DECAY', 60);

        if (RateLimiter::tooManyAttempts($rateKey, $maxResends)) {
            $seconds = RateLimiter::availableIn($rateKey);
            $this->flashAndRedirect('warning', "Terlalu banyak permintaan OTP. Silakan tunggu {$seconds} detik sebelum mengirim ulang.", '/verifikasi/otp');
            return;
        }

        RateLimiter::hit($rateKey, $decay);

        $tokenModel = new VerificationToken();
        $otpCode = $tokenModel->createWhatsAppOtp($userId);
        WhatsAppService::sendOtp($user['phone'], $otpCode, $user['name']);

        $this->flashAndRedirect('success', 'Kode OTP WhatsApp baru telah dikirim ke nomor ' . htmlspecialchars($user['phone']) . '.', '/verifikasi/otp');
    }

    /**
     * Verify Email with secure token
     */
    public function verifyEmail(string $token): void
    {
        $tokenModel = new VerificationToken();
        $record = $tokenModel->verifyEmailToken($token);

        if ($record) {
            $userModel = new User();
            $userModel->markEmailVerified((int)$record['user_id']);
            \App\Services\NotificationService::notifyEmailVerified((int)$record['user_id']);

            Session::setFlash('success', 'Alamat email Anda telah berhasil diverifikasi!');
            if (Auth::check()) {
                $this->redirect('/dashboard');
            } else {
                $this->redirect('/login');
            }
        } else {
            Session::setFlash('danger', 'Tautan verifikasi email tidak valid atau telah kedaluwarsa. Silakan login dan minta verifikasi baru.');
            $this->redirect('/login');
        }
    }

    /**
     * Resend Email Verification Link
     */
    public function resendEmail(): void
    {
        Auth::requireAuth();

        if (!$this->validateCsrf()) {
            return;
        }

        $user = Auth::user();
        if (Auth::isEmailVerified()) {
            $this->flashAndRedirect('info', 'Alamat email Anda sudah terverifikasi.', '/dashboard');
            return;
        }

        $tokenModel = new VerificationToken();
        $emailToken = $tokenModel->createEmailToken((int)$user['id']);
        MailService::sendVerificationEmail($user['email'], $user['name'], $emailToken);

        $this->flashAndRedirect('success', 'Tautan verifikasi email baru telah dikirim ke ' . htmlspecialchars($user['email']) . '.', '/verifikasi');
    }

    /**
     * Display Login Form
     */
    public function showLogin(): void
    {
        Auth::requireGuest();

        $this->render('auth/login', [
            'pageTitle' => 'Masuk ke Akun - SmartHealthy',
        ], 'layouts/auth');
    }

    /**
     * Process Login with Rate Limiting
     */
    public function login(): void
    {
        Auth::requireGuest();

        if (!$this->validateCsrf()) {
            return;
        }

        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $rateKey = "login_attempt_{$clientIp}";
        $maxAttempts = (int) Env::get('RATE_LIMIT_LOGIN_MAX', 5);
        $decaySeconds = (int) Env::get('RATE_LIMIT_LOGIN_DECAY', 60);

        if (RateLimiter::tooManyAttempts($rateKey, $maxAttempts)) {
            $waitSec = RateLimiter::availableIn($rateKey);
            $this->flashAndRedirect('danger', "Terlalu banyak percobaan masuk yang gagal. Akun sementara dikunci untuk keamanan. Silakan coba lagi dalam {$waitSec} detik.", '/login');
            return;
        }

        $identifier = trim((string)$this->input('email_or_nik'));
        $password = (string)$this->input('password');

        if (empty($identifier) || empty($password)) {
            $this->flashAndRedirect('danger', 'Silakan masukkan Email / No WhatsApp / NIK dan kata sandi Anda.', '/login');
            return;
        }

        $userModel = new User();

        // Check by Email
        $user = $userModel->findByEmail($identifier);
        // Check by Phone / WhatsApp
        if (!$user) {
            $user = $userModel->findByPhone($identifier);
        }
        // Check by NIK
        if (!$user) {
            $user = $userModel->findByNik($identifier);
        }

        if (!$user || !password_verify($password, $user['password'])) {
            RateLimiter::hit($rateKey, $decaySeconds);
            $currentHits = RateLimiter::attempts($rateKey);
            $remaining = max(0, $maxAttempts - $currentHits);

            $this->flashAndRedirect('danger', "Identitas atau kata sandi tidak cocok. Sisa kesempatan: {$remaining} kali.", '/login');
            return;
        }

        // Check if account is active
        if (isset($user['is_active']) && (int)$user['is_active'] === 0) {
            $this->flashAndRedirect('danger', 'Akun Anda dinonaktifkan oleh administrator. Silakan hubungi loket Puskesmas.', '/login');
            return;
        }

        // Login success: Clear rate limiter
        RateLimiter::clear($rateKey);
        Auth::login($user);

        Session::setFlash('success', "Selamat datang kembali, <strong>" . htmlspecialchars($user['name']) . "</strong>!");

        if ($user['role'] === 'admin') {
            $this->redirect('/admin');
        } else {
            $this->redirect('/dashboard');
        }
    }

    /**
     * Process Logout
     */
    public function logout(): void
    {
        Auth::logout();
        Session::setFlash('info', 'Anda telah berhasil keluar dari akun.');
        $this->redirect('/login');
    }

    /**
     * Display Forgot Password Form
     */
    public function showForgotPassword(): void
    {
        Auth::requireGuest();

        $this->render('auth/forgot_password', [
            'pageTitle' => 'Lupa Kata Sandi - SmartHealthy',
        ], 'layouts/auth');
    }

    /**
     * Process Forgot Password Request
     */
    public function sendResetLink(): void
    {
        Auth::requireGuest();

        if (!$this->validateCsrf()) {
            return;
        }

        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $rateKey = "forgot_pwd_{$clientIp}";
        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            $wait = RateLimiter::availableIn($rateKey);
            $this->flashAndRedirect('warning', "Terlalu banyak permintaan reset. Silakan tunggu {$wait} detik.", '/lupa-password');
            return;
        }
        RateLimiter::hit($rateKey, 120);

        $email = strtolower(trim((string)$this->input('email')));
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flashAndRedirect('danger', 'Silakan masukkan alamat email yang valid.', '/lupa-password');
            return;
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user) {
            $resetModel = new PasswordReset();
            $token = $resetModel->createResetToken((int)$user['id'], $user['email']);
            MailService::sendPasswordResetEmail($user['email'], $user['name'], $token);
        }

        // Always show positive message to prevent user enumeration
        $this->flashAndRedirect('success', 'Jika alamat email terdaftar, instruksi dan tautan pemulihan kata sandi telah dikirim ke email Anda.', '/lupa-password');
    }

    /**
     * Display Reset Password Form
     */
    public function showResetPassword(string $token): void
    {
        Auth::requireGuest();

        $resetModel = new PasswordReset();
        $resetRecord = $resetModel->validateToken($token);

        if (!$resetRecord) {
            Session::setFlash('danger', 'Tautan pemulihan kata sandi tidak valid atau telah kedaluwarsa (berlaku 60 menit). Silakan minta tautan baru.');
            $this->redirect('/lupa-password');
            return;
        }

        $this->render('auth/reset_password', [
            'pageTitle'   => 'Buat Kata Sandi Baru - SmartHealthy',
            'token'       => $token,
            'resetRecord' => $resetRecord,
        ], 'layouts/auth');
    }

    /**
     * Process Reset Password
     */
    public function resetPassword(): void
    {
        Auth::requireGuest();

        if (!$this->validateCsrf()) {
            return;
        }

        $token = (string)$this->input('token');
        $password = (string)$this->input('password');
        $passwordConfirm = (string)$this->input('password_confirm');

        $resetModel = new PasswordReset();
        $resetRecord = $resetModel->validateToken($token);

        if (!$resetRecord) {
            $this->flashAndRedirect('danger', 'Tautan reset sudah tidak berlaku.', '/lupa-password');
            return;
        }

        // Validate password
        $validator = Validator::make([
            'password'         => $password,
            'password_confirm' => $passwordConfirm,
        ], [
            'password'         => 'required|min:6',
            'password_confirm' => 'required|matches:password',
        ]);

        if ($validator->fails()) {
            $this->flashAndRedirect('danger', $validator->firstError(), "/reset-password/{$token}");
            return;
        }

        // Update password
        $userModel = new User();
        $newHash = password_hash($password, PASSWORD_BCRYPT);
        $userModel->updatePassword((int)$resetRecord['user_id'], $newHash);

        // Mark token used
        $resetModel->markAsUsed((int)$resetRecord['id']);

        // Send security notification
        \App\Services\NotificationService::notifyPasswordChanged((int)$resetRecord['user_id']);

        Session::setFlash('success', 'Kata sandi Anda telah berhasil diperbarui! Silakan masuk dengan kata sandi baru.');
        $this->redirect('/login');
    }
}
