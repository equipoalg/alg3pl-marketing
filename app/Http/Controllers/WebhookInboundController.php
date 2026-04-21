<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use App\Services\Webhook\WebhookDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class WebhookInboundController extends Controller
{
    public function handle(Request $request, int $webhookId)
    {
        $rateLimiterKey = 'webhook:' . $request->ip();

        // Allow 30 attempts per minute per IP
        if (RateLimiter::tooManyAttempts($rateLimiterKey, 30)) {
            $retryAfter = RateLimiter::availableIn($rateLimiterKey);

            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please try again in ' . $retryAfter . ' seconds.',
            ], 429, ['Retry-After' => $retryAfter]);
        }

        RateLimiter::hit($rateLimiterKey, 60); // decay: 60 seconds

        $webhook = Webhook::where('id', $webhookId)
            ->where('direction', 'inbound')
            ->where('is_active', true)
            ->firstOrFail();

        $dispatcher = app(WebhookDispatcher::class);

        $event     = $request->header('X-Webhook-Event', 'unknown');
        $signature = $request->header('X-Webhook-Signature');
        $payload   = $request->all();

        $log = $dispatcher->processInbound($webhook, $event, $payload, $signature);

        return response()->json([
            'success' => $log->success,
            'message' => $log->success ? 'Webhook received' : $log->error_message,
        ], $log->success ? 200 : 401);
    }
}
