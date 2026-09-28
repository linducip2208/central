<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Log;

/**
 * Adapter default: mencatat ke log (aman untuk dev/test).
 * Ganti dengan provider API (mis. gateway agregator) via binding
 * di MbgCoreProvider + config services.whatsapp.
 */
class LogWhatsAppProvider implements WhatsAppProvider
{
    public function send(string $to, string $message): string
    {
        $id = 'wa-log-'.uniqid();
        Log::info('WhatsApp (log adapter)', ['id' => $id, 'to' => $to, 'message' => mb_substr($message, 0, 500)]);

        return $id;
    }

    public function name(): string
    {
        return 'log';
    }
}
