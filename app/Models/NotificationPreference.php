<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    protected $fillable = ['user_id', 'type', 'in_app', 'mail'];

    protected function casts(): array
    {
        return ['in_app' => 'boolean', 'mail' => 'boolean'];
    }

    public static function for(User $user, string $type): self
    {
        return static::firstOrCreate(['user_id' => $user->id, 'type' => $type], ['in_app' => true, 'mail' => false]);
    }
}
