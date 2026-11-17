<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\User;
use App\Models\Village;
use App\Core\Database;

class PatientManagementTest extends TestCase
{
    private User $userModel;
    private array $cleanupUserIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();
    }

    protected function tearDown(): void
    {
        if (!empty($this->cleanupUserIds)) {
            $db = Database::getConnection();
            $in = implode(',', array_map('intval', $this->cleanupUserIds));
            $db->exec("DELETE FROM users WHERE id IN ({$in})");
        }
        parent::tearDown();
    }

    /**
     * Test find patient with related village details
     */
    public function testFindWithVillage(): void
    {
        $patient = $this->userModel->findWithVillage(2); // Ahmad Fauzi
        $this->assertNotNull($patient);
        $this->assertArrayHasKey('village_name', $patient);
        $this->assertNotEmpty($patient['village_name'], 'Nama desa domisili pasien harus terisi.');
    }

    /**
     * Test updating patient profile fields
     */
    public function testUpdatePatientProfile(): void
    {
        $uniqueSuffix = bin2hex(random_bytes(4));
        $userId = $this->userModel->create([
            'name'       => 'Pasien Update Test',
            'email'      => "update.test.{$uniqueSuffix}@smarthealthy.test",
            'phone'      => "0877" . random_int(10000000, 99999999),
            'nik'        => "3209" . random_int(100000000000, 999999999999),
            'village_id' => 1,
            'role'       => 'user',
            'password'   => password_hash('Pass#123', PASSWORD_BCRYPT),
            'is_active'  => 1,
        ]);
        $this->cleanupUserIds[] = (int)$userId;

        // Update profile
        $updated = $this->userModel->update((int)$userId, [
            'name'       => 'Nama Pasien Baru',
            'address'    => 'Blok Baru RT 05 RW 02',
            'gender'     => 'P',
            'birth_date' => '1992-04-20',
        ]);
        $this->assertTrue($updated);

        $refreshed = $this->userModel->find((int)$userId);
        $this->assertEquals('Nama Pasien Baru', $refreshed['name']);
        $this->assertEquals('Blok Baru RT 05 RW 02', $refreshed['address']);
        $this->assertEquals('P', $refreshed['gender']);
        $this->assertEquals('1992-04-20', $refreshed['birth_date']);
    }

    /**
     * Test updating patient password
     */
    public function testUpdatePatientPassword(): void
    {
        $uniqueSuffix = bin2hex(random_bytes(4));
        $userId = $this->userModel->create([
            'name'       => 'Pasien Sandi Test',
            'email'      => "sandi.test.{$uniqueSuffix}@smarthealthy.test",
            'phone'      => "0855" . random_int(10000000, 99999999),
            'nik'        => "3209" . random_int(100000000000, 999999999999),
            'village_id' => 3,
            'role'       => 'user',
            'password'   => password_hash('SandiLama#123', PASSWORD_BCRYPT),
            'is_active'  => 1,
        ]);
        $this->cleanupUserIds[] = (int)$userId;

        // Update password with new bcrypt hash
        $newPlainPassword = 'SandiBaruOtomatis@2026!';
        $this->userModel->update((int)$userId, [
            'password' => password_hash($newPlainPassword, PASSWORD_BCRYPT),
        ]);

        $refreshed = $this->userModel->find((int)$userId);
        $this->assertFalse(password_verify('SandiLama#123', $refreshed['password']));
        $this->assertTrue(password_verify($newPlainPassword, $refreshed['password']));
    }

    /**
     * Test demographic calculation by village
     */
    public function testDemographicsByVillage(): void
    {
        $demographics = $this->userModel->getDemographicsByVillage();
        $this->assertIsArray($demographics);
        $this->assertNotEmpty($demographics, 'Data demografi desa tidak boleh kosong.');
        $this->assertArrayHasKey('village_name', $demographics[0]);
        $this->assertArrayHasKey('total_users', $demographics[0]);
    }
}
