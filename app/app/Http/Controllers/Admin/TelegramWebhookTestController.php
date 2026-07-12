<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;

final class TelegramWebhookTestController extends Controller
{
    public function index()
    {
        return view('dev.telegram');
    }

    public function send(Request $request)
    {
        $payload = json_decode($request->string('payload'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()
                ->withErrors([
                    'payload' => 'Некорректный JSON: ' . json_last_error_msg(),
                ])
                ->withInput();
        }

        $response = Http::withHeaders([
            'Host' => 'api.localhost',
        ])->post(
            'http://nginx/integrations/telegram/' . config('services.telegram.url_key'),
            $payload
        );

        return back()
            ->with([
                'status' => $response->status(),
                'response' => $response->body(),
            ])
            ->withInput();
    }
}
