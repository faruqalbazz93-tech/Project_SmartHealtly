<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\User;
use App\Core\Database;

class UserRegistrationTest extends TestCase
{
    private User $userModel;
    private array $createdUserIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();
    }

    protected function tearDown(): void
    {
        // Clean up any test users created during testing
        if (!empty($this->createdUserIds)) {
            $db = Database::getConnection();
            $in = implode(',', array_map('intval', $this->createdUserIds));
            $db->exec("DELETE FROM users WHERE id IN ({$in})");
        }
        parent::tearDown();
    }

    /**
     * Test password hashing with BCRYPT
     */
    public function testPasswordIsSecurelyHashed(): void
    {
        $plainPassword = 'TestPassword@2026!';
        $hashed = password_hash($plainPassword, PASSWORD_BCRYPT);

        $this->assertNotEquals($plainPassword, $hashed);
        $this->assertTrue(password_verify($plainPassword, $hashed));
        $this->assertFalse(password_verify('WrongPassword', $hashed));
    }

    /**
     * Test patient registration creation and role assignment
     */
    public function testPatientUserCreation(): void
    {
        $uniqueSuffix = bin2hex(random_bytes(4));
        $email = "test.pasien.{$uniqueSuffix}@smarthealthy.test";
        $phone = "0899" . random_int(10000000, 99999999);
        $nik = "3209" . random_int(100000000000, 999999999999);

        $userData = [
            'name'       => 'Pasien Pengujian Unit',
            'email'      => $email,
            'phone'      => $phone,
            'nik'        => (string)$nik,
            'village_id' => 1,
            'role'       => 'user',
            'password'   => password_hash('SandiPengujian#123', PASSWORD_BCRYPT),
            'is_active'  => 1,
        ];

        $userId = $this->userModel->create($userData);
        $this->assertIsNumeric($userId);
        $this->assertGreaterThan(0, $userId);
        $this->createdUserIds[] = (int)$userId;

        // Verify patient record in database
        $fetched = $this->userModel->find((int)$userId);
        $this->assertNotNull($fetched);
        $this->assertEquals('user', $fetched['role'], 'Pasien baru harus memiliki role user.');
        $this->assertEquals($email, $fetched['email']);
        $this->assertTrue(password_verify('SandiPengujian#123', $fetched['password']));
    }

    /**
     * Test find user by Email, Phone, and NIK
     */
    public function testFindUserByIdentifiers(): void
    {
        $uniqueSuffix = bin2hex(random_bytes(4));
        $email = "cari.pasien.{$uniqueSuffix}@smarthealthy.test";
        $phone = "0888" . random_int(10000000, 99999999);
        $nik = "3209" . random_int(100000000000, 999999999999);

        $userId = $this->userModel->create([
            'name'       => 'Pasien Pencarian Test',
            'email'      => $email,
            'phone'      => $phone,
            'nik'        => (string)$nik,
            'village_id' => 2,
            'role'       => 'user',
            'password'   => password_hash('Testing#2026', PASSWORD_BCRYPT),
            'is_active'  => 1,
        ]);
        $this->createdUserIds[] = (int)$userId;

        $byEmail = $this->userModel->findByEmail($email);
        $this->assertNotNull($byEmail);
        $this->assertEquals($userId, $byEmail['id']);

        $byPhone = $this->userModel->findByPhone($phone);
        $this->assertNotNull($byPhone);
        $this->assertEquals($userId, $byPhone['id']);

        $byNik = $this->userModel->findByNik((string)$nik);
        $this->assertNotNull($byNik);
        $this->assertEquals($userId, $byNik['id']);
    }
}
