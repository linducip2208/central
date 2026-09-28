<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = ['type', 'subject', 'body', 'mail_enabled'];

    protected function casts(): array
    {
        return ['mail_enabled' => 'boolean'];
    }

    public function render(array $vars): array
    {
        $out = ['subject' => $this->subject, 'body' => $this->body];
        foreach ($vars as $k => $v) {
            $out['subject'] = str_replace('{{'.$k.'}}', (string) $v, $out['subject']);
            $out['body'] = str_replace('{{'.$k.'}}', (string) $v, $out['body']);
        }

        return $out;
    }
}
