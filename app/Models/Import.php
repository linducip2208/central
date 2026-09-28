<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    protected $fillable = ['organization_id', 'entity', 'filename', 'status', 'total_rows', 'imported_rows', 'failed_rows', 'errors', 'created_by'];

    protected function casts(): array
    {
        return ['errors' => 'array'];
    }
}
