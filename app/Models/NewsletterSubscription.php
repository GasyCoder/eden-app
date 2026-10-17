<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscription extends Model
{
    use HasFactory;

    public const STATUSES = ['subscribed', 'unsubscribed'];

    public const SOURCES = ['site', 'manual', 'import'];

    protected $fillable = [
        'email',
        'name',
        'status',
        'locale',
        'source',
        'token',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $subscription): void {
            $subscription->token ??= Str::random(48);
            $subscription->status ??= 'subscribed';
            $subscription->subscribed_at ??= now();
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'subscribed');
    }

    /** Inscription ou réinscription (une adresse désinscrite redevient active). */
    public static function subscribe(string $email, array $attributes = []): self
    {
        $subscription = self::query()->firstOrNew(['email' => mb_strtolower(trim($email))]);

        if (! $subscription->exists || $subscription->status !== 'subscribed') {
            $subscription->fill(['status' => 'subscribed', 'subscribed_at' => now(), 'unsubscribed_at' => null]);
        }

        $subscription->fill(array_filter($attributes, fn ($value) => $value !== null && $value !== ''));
        $subscription->save();

        return $subscription;
    }

    public function unsubscribe(): void
    {
        $this->forceFill(['status' => 'unsubscribed', 'unsubscribed_at' => now()])->save();
    }

    public function unsubscribeUrl(): string
    {
        return route('newsletter.unsubscribe', $this->token);
    }

    public function toAdminRow(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'status' => $this->status,
            'locale' => $this->locale,
            'source' => $this->source,
            'subscribed_at' => $this->subscribed_at?->toIso8601String(),
            'unsubscribed_at' => $this->unsubscribed_at?->toIso8601String(),
        ];
    }
}
