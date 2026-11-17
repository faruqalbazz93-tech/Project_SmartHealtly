<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Core\Validator;
use App\Models\User;
use App\Models\Registration;
use App\Models\HealthService;
use App\Models\Village;
use App\Models\HealthArticle;
use App\Models\QuizAttempt;

class DashboardController extends Controller
{
    /**
     * Patient / Masyarakat Dashboard
     */
    public function index(): void
    {
        Auth::requireAuth();

        if (Auth::isAdmin()) {
            $this->redirect('/admin');
            return;
        }

        $userId = Auth::id();
        $userModel = new User();
        $regModel = new Registration();
        $serviceModel = new HealthService();
        $articleModel = new HealthArticle();
        $quizModel = new QuizAttempt();

        $user = $userModel->findWithVillage($userId);
        $activeRegistration = $regModel->getActiveByUser($userId);
        $recentRegistrations = array_slice($regModel->getByUserFiltered($userId), 0, 5);
        $services = $serviceModel->getActiveServices();
        $articles = $articleModel->getPublished(3);
        $userAttempts = $quizModel->getByUser($userId);

        $isFullyVerified = Auth::isVerified() && Auth::isEmailVerified() && Auth::isPhoneVerified();

        $this->render('user/dashboard', [
            'pageTitle'           => 'Dashboard Pasien - SmartHealthy',
            'user'                => $user,
            'activeRegistration'  => $activeRegistration,
            'recentRegistrations' => $recentRegistrations,
            'services'            => $services,
            'articles'            => $articles,
            'userAttempts'        => $userAttempts,
            'isVerified'          => Auth::isVerified(),
            'isEmailVerified'     => Auth::isEmailVerified(),
            'isPhoneVerified'     => Auth::isPhoneVerified(),
            'isFullyVerified'     => $isFullyVerified,
        ], 'layouts/user');
    }

    /**
     * User Profile Page
     */
    public function profile(): void
    {
        Auth::requireAuth();

        $userId = Auth::id();
        $userModel = new User();
        $villageModel = new Village();

        $user = $userModel->findWithVillage($userId);
        $villages = $villageModel->allOrdered();

        $this->render('user/profile', [
            'pageTitle'       => 'Profil Saya - SmartHealthy',
            'user'            => $user,
            'villages'        => $villages,
            'isEmailVerified' => Auth::isEmailVerified(),
            'isPhoneVerified' => Auth::isPhoneVerified(),
        ], Auth::isAdmin() ? 'layouts/admin' : 'layouts/user');
    }

    /**
     * Process Profile Update with Validation
     */
    public function updateProfile(): void
    {
        Auth::requireAuth();

        if (!$this->validateCsrf()) {
            return;
        }

        $userId = Auth::id();
        $name = trim((string)$this->input('name'));
        $phone = trim((string)$this->input('phone'));
        $villageId = $this->input('village_id');
        $address = trim((string)$this->input('address'));
        $birthDate = $this->input('birth_date');
        $gender = $this->input('gender');
        $nik = trim((string)$this->input('nik'));
        $password = (string)$this->input('password');
        $passwordConfirm = (string)$this->input('password_confirm');

        // Validation
        $rules = [
            'name'       => 'required|min:3|max:150',
            'phone'      => "required|whatsapp|unique:users,phone,{$userId}",
            'village_id' => 'required|exists:villages,id',
        ];

        if (!empty($nik)) {
            $rules['nik'] = "numeric|min:16|max:16|unique:users,nik,{$userId}";
        }

        if (!empty($password)) {
            $rules['password'] = 'min:6|confirmed';
            $rules['password_confirm'] = 'required';
        }

        $validator = Validator::make([
            'name'             => $name,
            'phone'            => $phone,
            'village_id'       => $villageId,
            'nik'              => $nik,
            'password'         => $password,
            'password_confirm' => $passwordConfirm,
        ], $rules);

        if ($validator->fails()) {
            $this->flashAndRedirect('danger', $validator->firstError(), '/profil');
            return;
        }

        $userModel = new User();
        $currentUser = $userModel->find($userId);

        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        $phoneChanged = ($cleanPhone !== $currentUser['phone']);

        $updateData = [
            'name'       => $name,
            'phone'      => $cleanPhone,
            'village_id' => !empty($villageId) ? (int) $villageId : null,
            'address'    => $address,
            'birth_date' => !empty($birthDate) ? $birthDate : null,
            'gender'     => in_array($gender, ['L', 'P']) ? $gender : 'L',
        ];

        if (!empty($nik)) {
            $updateData['nik'] = $nik;
        }

        // If phone number changed, reset phone verification status
        if ($phoneChanged) {
            $updateData['phone_verified_at'] = null;
        }

        if (!empty($password)) {
            $updateData['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $userModel->update($userId, $updateData);
        Session::set('user_name', $name);
        Session::set('user_phone', $cleanPhone);
        if (!empty($nik)) {
            Session::set('user_nik', $nik);
        }

        if ($phoneChanged) {
            $this->flashAndRedirect('warning', 'Profil diperbarui. Karena nomor WhatsApp diubah, silakan lakukan verifikasi ulang.', '/verifikasi/otp');
        } elseif (!empty($password)) {
            $this->flashAndRedirect('success', 'Profil dan kata sandi Anda berhasil diperbarui.', '/profil');
        } else {
            $this->flashAndRedirect('success', 'Profil Anda berhasil diperbarui.', '/profil');
        }
    }
}
