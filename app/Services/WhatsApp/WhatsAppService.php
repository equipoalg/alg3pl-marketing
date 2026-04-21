<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $token;
    private string $phoneNumberId;
    private string $apiBase = 'https://graph.facebook.com/v18.0';

    public function __construct()
    {
        $this->token         = config('services.whatsapp.api_token', env('WHATSAPP_API_TOKEN', ''));
        $this->phoneNumberId = config('services.whatsapp.phone_number_id', env('WHATSAPP_PHONE_NUMBER_ID', ''));
    }

    /**
     * Send a free-form text message via WhatsApp Cloud API.
     *
     * @param  string $to      Recipient phone in E.164 format (e.g. 50312345678)
     * @param  string $message Plain text body
     * @return array{success: bool, message_id: string|null, error: string|null}
     */
    public function sendMessage(string $to, string $message): array
    {
        if (empty($this->token) || empty($this->phoneNumberId)) {
            return [
                'success'    => false,
                'message_id' => null,
                'error'      => 'WhatsApp API credentials not configured (WHATSAPP_API_TOKEN / WHATSAPP_PHONE_NUMBER_ID).',
            ];
        }

        try {
            $response = Http::withToken($this->token)
                ->timeout(15)
                ->post("{$this->apiBase}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to'                => $to,
                    'type'              => 'text',
                    'text'              => ['body' => $message],
                ]);

            if ($response->successful()) {
                $messageId = $response->json('messages.0.id');
                return [
                    'success'    => true,
                    'message_id' => $messageId,
                    'error'      => null,
                ];
            }

            $errorDetail = $response->json('error.message') ?? $response->body();
            Log::warning('WhatsApp sendMessage failed', [
                'status' => $response->status(),
                'error'  => $errorDetail,
                'to'     => $to,
            ]);

            return [
                'success'    => false,
                'message_id' => null,
                'error'      => $errorDetail,
            ];

        } catch (\Throwable $e) {
            Log::error('WhatsApp sendMessage exception', ['message' => $e->getMessage(), 'to' => $to]);

            return [
                'success'    => false,
                'message_id' => null,
                'error'      => $e->getMessage(),
            ];
        }
    }

    /**
     * Send a template message (e.g. lead_welcome) via WhatsApp Cloud API.
     *
     * @param  string $to           Recipient phone in E.164 format
     * @param  string $templateName Approved template name (e.g. 'lead_welcome')
     * @param  array  $params       Ordered list of body parameter values
     * @return array{success: bool, message_id: string|null, error: string|null}
     */
    public function sendTemplate(string $to, string $templateName, array $params = []): array
    {
        if (empty($this->token) || empty($this->phoneNumberId)) {
            return [
                'success'    => false,
                'message_id' => null,
                'error'      => 'WhatsApp API credentials not configured.',
            ];
        }

        $components = [];
        if (!empty($params)) {
            $components[] = [
                'type'       => 'body',
                'parameters' => array_map(
                    fn ($value) => ['type' => 'text', 'text' => (string) $value],
                    $params
                ),
            ];
        }

        try {
            $response = Http::withToken($this->token)
                ->timeout(15)
                ->post("{$this->apiBase}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to'                => $to,
                    'type'              => 'template',
                    'template'          => [
                        'name'       => $templateName,
                        'language'   => ['code' => 'es'],
                        'components' => $components,
                    ],
                ]);

            if ($response->successful()) {
                return [
                    'success'    => true,
                    'message_id' => $response->json('messages.0.id'),
                    'error'      => null,
                ];
            }

            $errorDetail = $response->json('error.message') ?? $response->body();
            Log::warning('WhatsApp sendTemplate failed', [
                'status'   => $response->status(),
                'error'    => $errorDetail,
                'template' => $templateName,
                'to'       => $to,
            ]);

            return [
                'success'    => false,
                'message_id' => null,
                'error'      => $errorDetail,
            ];

        } catch (\Throwable $e) {
            Log::error('WhatsApp sendTemplate exception', ['message' => $e->getMessage()]);

            return [
                'success'    => false,
                'message_id' => null,
                'error'      => $e->getMessage(),
            ];
        }
    }
}
