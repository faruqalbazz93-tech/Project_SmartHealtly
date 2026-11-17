<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\WhatsAppService;

class WebhookController extends Controller
{
    /**
     * Handle Meta WhatsApp Webhook Verification (GET /webhook/whatsapp)
     */
    public function verify(): void
    {
        $challenge = WhatsAppService::verifyWebhookChallenge($_GET);

        if ($challenge !== null) {
            http_response_code(200);
            header('Content-Type: text/plain');
            echo $challenge;
            exit;
        }

        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'status'  => 'error',
            'message' => 'Forbidden: Invalid verification token or mode',
        ]);
        exit;
    }

    /**
     * Handle Meta WhatsApp Incoming Status Updates (POST /webhook/whatsapp)
     */
    public function handle(): void
    {
        $rawPayload = file_get_contents('php://input');
        $signatureHeader = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? null;

        // 1. Signature Verification (HMAC-SHA256)
        if (!WhatsAppService::verifySignature($rawPayload, $signatureHeader)) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'status'  => 'error',
                'message' => 'Unauthorized: Invalid X-Hub-Signature-256',
            ]);
            exit;
        }

        // 2. Validate JSON Payload
        $data = json_decode($rawPayload, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'status'  => 'error',
                'message' => 'Bad Request: Invalid JSON payload',
            ]);
            exit;
        }

        // 3. Process Status Updates
        $result = WhatsAppService::processWebhookPayload($data);

        // Always return 200 OK to Meta to avoid webhook retries
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode([
            'status'    => 'EVENT_RECEIVED',
            'processed' => $result['count'] ?? 0,
        ]);
        exit;
    }
}

