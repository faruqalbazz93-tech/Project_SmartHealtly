<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use PDO;

class DatabaseConnectionTest extends TestCase
{
    /**
     * Test that Database connection returns a valid PDO instance
     */
    public function testDatabaseConnectionReturnsPdo(): void
    {
        $pdo = Database::getConnection();
        $this->assertInstanceOf(PDO::class, $pdo, 'Koneksi database harus berupa instance dari PDO.');
    }

    /**
     * Test that Database connection implements Singleton pattern (same instance returned)
     */
    public function testDatabaseSingletonInstance(): void
    {
        $pdo1 = Database::getConnection();
        $pdo2 = Database::getConnection();
        $this->assertSame($pdo1, $pdo2, 'Koneksi database harus menggunakan pola Singleton.');
    }

    /**
     * Test PDO Error Mode is set to ERRMODE_EXCEPTION
     */
    public function testPdoErrorModeIsException(): void
    {
        $pdo = Database::getConnection();
        $errorMode = $pdo->getAttribute(PDO::ATTR_ERRMODE);
        $this->assertEquals(PDO::ERRMODE_EXCEPTION, $errorMode, 'PDO Error Mode harus ERRMODE_EXCEPTION.');
    }

    /**
     * Test executing a basic test query
     */
    public function testBasicQueryExecution(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('SELECT 1 as test_col');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertIsArray($result);
        $this->assertEquals(1, $result['test_col']);
    }
}
