<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Registration;
use App\Models\HealthService;
use App\Core\Database;

class QueueRegistrationTest extends TestCase
{
    private Registration $regModel;
    private array $cleanupRegistrationIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->regModel = new Registration();
    }

    protected function tearDown(): void
    {
        if (!empty($this->cleanupRegistrationIds)) {
            $db = Database::getConnection();
            $in = implode(',', array_map('intval', $this->cleanupRegistrationIds));
            $db->exec("DELETE FROM registrations WHERE id IN ({$in})");
        }
        parent::tearDown();
    }

    /**
     * Test creating a registration transaction
     */
    public function testCreateRegistrationTransaction(): void
    {
        $testDate = date('Y-m-d', strtotime('+7 days'));
        $testUserId = 2; // Ahmad Fauzi
        $testServiceId = 1; // Poli Umum

        // Clean any existing booking for this test date
        $db = Database::getConnection();
        $db->exec("DELETE FROM registrations WHERE user_id = {$testUserId} AND health_service_id = {$testServiceId} AND registration_date = '{$testDate}'");

        $result = $this->regModel->createRegistrationTransaction([
            'user_id'           => $testUserId,
            'health_service_id' => $testServiceId,
            'schedule_id'       => null,
            'registration_date' => $testDate,
            'complaint'         => 'Pemeriksaan kesehatan rutin untuk pengujian unit sistem.',
        ]);

        $this->assertTrue($result['status'], 'Pendaftaran transaksi antrean harus berhasil: ' . ($result['message'] ?? ''));
        $this->assertNotEmpty($result['registration_code'], 'Kode registrasi unik harus diterbitkan.');
        $this->assertStringStartsWith('REG-', $result['registration_code'], 'Format kode registrasi harus diawali REG-.');
        $this->assertGreaterThan(0, $result['queue_number'], 'Nomor antrean harus lebih dari 0.');

        $regId = (int)$result['registration_id'];
        $this->cleanupRegistrationIds[] = $regId;

        // Verify status is default Pending
        $fetched = $this->regModel->find($regId);
        $this->assertEquals('Pending', $fetched['status'], 'Status pendaftaran awal harus Pending.');
    }

    /**
     * Test queue status transition lifecycle
     */
    public function testQueueStatusTransitions(): void
    {
        $testDate = date('Y-m-d', strtotime('+8 days'));
        $result = $this->regModel->createRegistrationTransaction([
            'user_id'           => 2,
            'health_service_id' => 2, // Poli Gigi
            'schedule_id'       => null,
            'registration_date' => $testDate,
            'complaint'         => 'Pembersihan karang gigi pengujian lifecycle.',
        ]);

        $regId = (int)$result['registration_id'];
        $this->cleanupRegistrationIds[] = $regId;

        // 1. Pending -> Confirmed
        $updated1 = $this->regModel->updateStatus($regId, 'Confirmed', 'Pasien dipanggil loket');
        $this->assertTrue($updated1);
        $this->assertEquals('Confirmed', $this->regModel->find($regId)['status']);

        // 2. Confirmed -> Completed
        $updated2 = $this->regModel->updateStatus($regId, 'Completed', 'Selesai pemeriksaan medis');
        $this->assertTrue($updated2);
        $this->assertEquals('Completed', $this->regModel->find($regId)['status']);

        // 3. Completed -> Cancelled
        $updated3 = $this->regModel->updateStatus($regId, 'Cancelled', 'Dibatalkan');
        $this->assertTrue($updated3);
        $this->assertEquals('Cancelled', $this->regModel->find($regId)['status']);
    }

    /**
     * Test prevention of duplicate active bookings on the same date & service
     */
    public function testDuplicateBookingPrevention(): void
    {
        $testDate = date('Y-m-d', strtotime('+9 days'));
        $testUserId = 3; // Siti Rahmawati
        $testServiceId = 3; // Poli KIA

        $res1 = $this->regModel->createRegistrationTransaction([
            'user_id'           => $testUserId,
            'health_service_id' => $testServiceId,
            'schedule_id'       => null,
            'registration_date' => $testDate,
            'complaint'         => 'Booking pertama.',
        ]);
        $this->cleanupRegistrationIds[] = (int)$res1['registration_id'];

        // Second booking on same date & same service must fail
        $res2 = $this->regModel->createRegistrationTransaction([
            'user_id'           => $testUserId,
            'health_service_id' => $testServiceId,
            'schedule_id'       => null,
            'registration_date' => $testDate,
            'complaint'         => 'Booking kedua duplikat.',
        ]);

        $this->assertFalse($res2['status'], 'Booking duplikat pada tanggal dan layanan yang sama harus ditolak.');
    }
}
