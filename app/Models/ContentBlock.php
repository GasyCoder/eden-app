<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ContentBlock extends Model
{
    protected $fillable = [
        'key',
        'type',
        'value',
        'meta',
        'group',
        'position',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'is_active' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderByRaw('position IS NULL')
            ->orderBy('position')
            ->orderBy('key');
    }

    public function scopeByGroup(Builder $query, ?string $group): Builder
    {
        return $group ? $query->where('group', $group) : $query;
    }
}
