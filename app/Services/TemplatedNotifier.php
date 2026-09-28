<?php

namespace App\Services;

use App\Core\Services\NotificationService;
use App\Models\NotificationPreference;
use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Notification center: template + preferensi + in-app/mail + log.
 */
class TemplatedNotifier
{
    public function __construct(protected NotificationService $base) {}

    public function send(iterable $users, string $type, array $vars = []): void
    {
        $users = $users instanceof Collection ? $users : collect($users);
        $template = NotificationTemplate::where('type', $type)->first();
        $rendered = $template ? $template->render($vars) : ['subject' => $type, 'body' => json_encode($vars)];

        $inApp = [];
        foreach ($users as $user) {
            $pref = NotificationPreference::for($user, $type);
            if ($pref->in_app) {
                $inApp[] = $user;
            }
            if ($pref->mail && $template?->mail_enabled && $user->email) {
                $this->sendMail($user, $rendered);
            }
        }
        if ($inApp) {
            $this->base->send($inApp, $type, ['subject' => $rendered['subject'], 'body' => $rendered['body']] + $vars);
        }
    }

    /** Kirim mentah tanpa template (untuk automation engine). */
    public function sendRaw(iterable $users, string $type, string $message): void
    {
        $this->base->send($users, $type, ['message' => $message]);
    }

    protected function sendMail(User $user, array $rendered): void
    {
        try {
            if (empty(config('mail.host')) && config('mail.mailer') === 'smtp') {
                throw new \RuntimeException('MAIL_HOST belum dikonfigurasi.');
            }
            Mail::raw($rendered['body'], fn ($m) => $m->to($user->email)->subject($rendered['subject']));
        } catch (\Throwable $e) {
            Log::warning('Mail notifikasi gagal', ['user' => $user->id, 'error' => $e->getMessage()]);
        }
    }
}
