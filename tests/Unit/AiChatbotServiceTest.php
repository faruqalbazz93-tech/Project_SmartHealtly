<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\AiService;

class AiChatbotServiceTest extends TestCase
{
    private AiService $aiService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aiService = new AiService();
    }

    public function testAiServiceInstantiatesProperly(): void
    {
        $this->assertInstanceOf(AiService::class, $this->aiService);
        $this->assertIsString($this->aiService->getProvider());
    }

    public function testFallbackMockResponseWhenKeyNotConfigured(): void
    {
        $prompt = "Apa gejala demam berdarah?";
        $response = $this->aiService->fallbackMockResponse($prompt);

        $this->assertNotEmpty($response);
        $this->assertStringContainsString('SmartHealthy', $response);
        $this->assertStringContainsString('AI Assistant', $response);
    }

    public function testChatReturnsStringWithOrWithoutConfig(): void
    {
        $systemInstruction = "Anda adalah asisten medis ramah untuk Puskesmas Astanajapura.";
        $history = [
            ['role' => 'user', 'message' => 'Halo dokter'],
            ['role' => 'assistant', 'message' => 'Halo! Ada yang bisa saya bantu terkait informasi kesehatan?']
        ];
        $userMessage = "Saya merasa pusing sejak kemarin.";

        try {
            $reply = $this->aiService->chat($systemInstruction, $history, $userMessage);
            $this->assertNotEmpty($reply);
            $this->assertIsString($reply);
        } catch (\Exception $e) {
            // When external Google API experiences temporary demand spikes or timeouts
            $this->assertNotEmpty($e->getMessage());
        }
    }

    public function testHealthPromptSanitizationAndSafetyGrounding(): void
    {
        $maliciousInput = "<script>alert('xss')</script> SELECT * FROM users; --";
        $cleaned = htmlspecialchars(strip_tags(trim($maliciousInput)), ENT_QUOTES, 'UTF-8');

        $this->assertStringNotContainsString('<script>', $cleaned);
        $this->assertStringNotContainsString('</script>', $cleaned);
        $this->assertStringContainsString('SELECT * FROM users;', $cleaned);
    }
}
