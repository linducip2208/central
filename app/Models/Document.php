<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use Auditable;

    protected $fillable = ['organization_id', 'category', 'code', 'title', 'content', 'version', 'effective_from', 'expires_at', 'status', 'attachment_path', 'created_by', 'approved_by', 'approved_at'];

    protected function casts(): array
    {
        return ['effective_from' => 'date', 'expires_at' => 'date', 'approved_at' => 'datetime'];
    }

    public function acknowledgements()
    {
        return $this->hasMany(DocumentAcknowledgement::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'PUBLISHED'
            && (! $this->effective_from || $this->effective_from <= now())
            && (! $this->expires_at || $this->expires_at >= now());
    }
}
