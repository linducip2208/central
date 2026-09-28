<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentAcknowledgement extends Model
{
    protected $fillable = ['document_id', 'user_id', 'acknowledged_at'];

    protected function casts(): array
    {
        return ['acknowledged_at' => 'datetime'];
    }
}
