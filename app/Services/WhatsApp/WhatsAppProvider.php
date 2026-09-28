<?php

namespace App\Services\WhatsApp;

/**
 * Kontrak provider WhatsApp. Kredensial HANYA via config/services,
 * tidak pernah di-hardcode. Lihat config/services.php (whatsapp.*).
 */
interface WhatsAppProvider
{
    /** @return string message id provider */
    public function send(string $to, string $message): string;

    public function name(): string;
}
