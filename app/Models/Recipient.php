<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipient extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = ['school_id', 'name', 'identifier', 'grade', 'class_name', 'gender', 'allergy_notes', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
