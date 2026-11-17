<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\FonnteWhatsAppService;
use App\Models\WhatsAppSetting;

class WhatsAppNotificationTest extends TestCase
{
    private WhatsAppSetting $settingModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->settingModel = new WhatsAppSetting();
    }

    public function testPhoneFormattingIndonesianStandard(): void
    {
        // 08xx becomes 628xx
        $this->assertEquals('6281234567890', FonnteWhatsAppService::formatPhone('081234567890'));

        // +628xx becomes 628xx
        $this->assertEquals('6281234567890', FonnteWhatsAppService::formatPhone('+6281234567890'));

        // Formatted with spaces and dashes
        $this->assertEquals('6281234567890', FonnteWhatsAppService::formatPhone('0812-3456-7890'));
        $this->assertEquals('6281234567890', FonnteWhatsAppService::formatPhone('0812 3456 7890'));

        // 8xx without leading zero or 62
        $this->assertEquals('6281234567890', FonnteWhatsAppService::formatPhone('81234567890'));
    }

    public function testTemplateRenderingWithPlaceholders(): void
    {
        $placeholders = [
            'nama'              => 'Budi Santoso',
            'nomor_pendaftaran' => 'REG-20261117-001',
            'layanan'           => 'Poli Umum',
            'tanggal'           => '18 November 2026',
            'jam'               => '08:30 WIB',
            'nama_puskesmas'    => 'Puskesmas Astanajapura',
            'nama_dokter'       => 'dr. Sarah Sp.PD'
        ];

        $rendered = $this->settingModel->renderTemplate('registration', $placeholders);

        $this->assertStringContainsString('Budi Santoso', $rendered);
        $this->assertStringContainsString('REG-20261117-001', $rendered);
        $this->assertStringContainsString('Poli Umum', $rendered);
        $this->assertStringContainsString('18 November 2026', $rendered);
        $this->assertStringNotContainsString('{nama}', $rendered);
        $this->assertStringNotContainsString('{nomor_pendaftaran}', $rendered);
    }

    public function testReminderTemplateRendering(): void
    {
        $placeholders = [
            'nama_pasien'       => 'Siti Aminah',
            'nomor_antrean'     => 'A-012',
            'nama_layanan'      => 'Poli Gigi & Mulut',
            'tanggal_kunjungan' => '19 November 2026',
            'jadwal_layanan'    => '10:00 WIB',
            'nama_dokter'       => 'drg. Hendra'
        ];

        $renderedH1 = $this->settingModel->renderTemplate('reminder_h1', $placeholders);
        $this->assertStringContainsString('Siti Aminah', $renderedH1);
        $this->assertStringContainsString('Poli Gigi & Mulut', $renderedH1);
        $this->assertStringContainsString('BESOK', $renderedH1);

        $renderedH2 = $this->settingModel->renderTemplate('reminder_h2', $placeholders);
        $this->assertStringContainsString('Siti Aminah', $renderedH2);
        $this->assertStringContainsString('2 JAM', $renderedH2);
    }

    public function testTokenMaskingSecurity(): void
    {
        $masked = FonnteWhatsAppService::getMaskedToken();
        $this->assertIsString($masked);
        
        // Never expose full token if configured
        if (FonnteWhatsAppService::isConfigured()) {
            $token = FonnteWhatsAppService::getToken();
            $this->assertNotEquals($token, $masked);
            $this->assertStringContainsString('****', $masked);
        }
    }
}
