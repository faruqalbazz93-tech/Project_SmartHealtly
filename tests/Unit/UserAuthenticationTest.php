<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\User;
use App\Core\Auth;
use App\Core\Session;

class UserAuthenticationTest extends TestCase
{
    private User $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();
        Session::start();
    }

    /**
     * Test Admin demo credential authentication
     */
    public function testAdminAuthenticationSuccessful(): void
    {
        $admin = $this->userModel->findByEmail('admin@smarthealthy.id');
        $this->assertNotNull($admin, 'Akun admin harus terdaftar di database.');
        $this->assertEquals('admin', $admin['role'], 'Role akun harus admin.');

        $isPasswordValid = password_verify('Astanajapura@2026!', $admin['password']);
        $this->assertTrue($isPasswordValid, 'Password akun admin harus cocok.');
    }

    /**
     * Test Patient demo credential authentication
     */
    public function testPatientAuthenticationSuccessful(): void
    {
        $patient = $this->userModel->findByEmail('pasien@smarthealthy.id');
        $this->assertNotNull($patient, 'Akun pasien demo harus terdaftar di database.');
        $this->assertEquals('user', $patient['role'], 'Role akun harus user/pasien.');

        $isPasswordValid = password_verify('SehatAstanajapura#2026', $patient['password']);
        $this->assertTrue($isPasswordValid, 'Password akun pasien harus cocok.');
    }

    /**
     * Test wrong password fails verification
     */
    public function testInvalidPasswordFails(): void
    {
        $patient = $this->userModel->findByEmail('pasien@smarthealthy.id');
        $this->assertNotNull($patient);

        $isPasswordValid = password_verify('SandiSalahTotal#999', $patient['password']);
        $this->assertFalse($isPasswordValid, 'Password salah tidak boleh lolos verifikasi.');
    }

    /**
     * Test Auth class session state management
     */
    public function testAuthSessionState(): void
    {
        $admin = $this->userModel->findByEmail('admin@smarthealthy.id');
        $this->assertNotEmpty($admin, 'Akun admin harus tersedia di database.');

        Auth::login($admin);

        $this->assertTrue(Auth::check(), 'Auth::check() harus true setelah login.');
        $this->assertEquals((int)$admin['id'], Auth::id(), 'Auth::id() harus mengembalikan ID pengguna yang masuk.');
        $this->assertEquals('admin', Auth::role(), 'Auth::role() harus mengembalikan role yang tepat.');
        $this->assertTrue(Auth::isAdmin(), 'Auth::isAdmin() harus true untuk role admin.');
        $this->assertTrue(Auth::isFullyVerified(), 'Auth::isFullyVerified() harus true.');

        Auth::logout();
        $this->assertFalse(Auth::check(), 'Auth::check() harus false setelah logout.');
    }
}
