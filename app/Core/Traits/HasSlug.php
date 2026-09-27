<?php

namespace App\Core\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            $source = $model->slugSourceColumn();
            if (empty($model->slug) && ! empty($model->{$source})) {
                $model->slug = $model->generateSlug($source);
            }
        });

        static::updating(function ($model) {
            $source = $model->slugSourceColumn();
            if ($model->isDirty($source) && ! $model->isDirty('slug')) {
                $model->slug = $model->generateSlug($source);
            }
        });
    }

    protected function slugSourceColumn(): string
    {
        return 'name';
    }

    public function generateSlug(string $attribute = 'name'): string
    {
        $base = Str::slug((string) $this->{$attribute});
        $base = $base !== '' ? $base : 'item';
        $slug = $base;
        $counter = 1;

        $query = static::where('slug', $slug);
        if ($this->exists) {
            $query->where($this->getKeyName(), '!=', $this->getKey());
        }
        while ((clone $query)->exists()) {
            $slug = $base.'-'.($counter++);
            $query = static::where('slug', $slug);
            if ($this->exists) {
                $query->where($this->getKeyName(), '!=', $this->getKey());
            }
        }

        return $slug;
    }
}
