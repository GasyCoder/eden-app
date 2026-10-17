<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class NewsletterSubscription extends Model
{
    use HasFactory;

    public const STATUSES = ['pending', 'pending_approval', 'subscribed', 'rejected', 'unsubscribed'];

    public const STATUS_LABELS = ['pending' => 'E-mail à confirmer', 'pending_approval' => 'À valider', 'subscribed' => 'Abonné', 'rejected' => 'Refusé', 'unsubscribed' => 'Désinscrit'];

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

    protected $hidden = ['token', 'verification_token'];

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'admin_confirmed_at' => 'datetime',
            'rejected_at' => 'datetime',
            'verification_sent_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $subscription): void {
            $subscription->token ??= Str::random(48);
            $subscription->status ??= 'pending';
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'subscribed')->whereNotNull('approved_at')
            ->where(fn (Builder $query) => $query->whereNotNull('email_verified_at')->orWhereNotNull('admin_confirmed_at'));
    }

    protected function email(): Attribute
    {
        return Attribute::make(set: fn (string $value) => mb_strtolower(trim($value)));
    }

    /** Une demande par adresse ; une nouvelle saisie ne réactive jamais un ancien abonnement. */
    public static function subscribe(string $email, array $attributes = []): self
    {
        $email = mb_strtolower(trim($email));

        // Reconnaît aussi une éventuelle ancienne adresse enregistrée avec des majuscules.
        $existing = self::query()->whereRaw('LOWER(TRIM(email)) = ?', [$email])->first();

        return $existing ?? self::query()->createOrFirst(['email' => $email],
            collect($attributes)->only(['name', 'locale', 'source'])->filter(fn ($value) => $value !== null && $value !== '')->all());
    }

    public function isActive(): bool
    {
        return $this->status === 'subscribed' && $this->approved_at !== null
            && ($this->email_verified_at !== null || $this->admin_confirmed_at !== null);
    }

    public function verifyEmail(string $token): void
    {
        self::query()->whereKey($this->id)->where('status', 'pending')->where('verification_token', $token)
            ->update(['email_verified_at' => now(), 'status' => 'pending_approval']);
        self::query()->whereKey($this->id)->where('status', 'subscribed')->whereNull('email_verified_at')->where('verification_token', $token)
            ->update(['email_verified_at' => now()]);
        $this->refresh();
    }

    public function approve(User $administrator): void
    {
        abort_unless($administrator->is_admin, 404);

        $updated = self::query()->whereKey($this->id)->whereIn('status', ['pending', 'pending_approval'])
            ->update(['status' => 'subscribed', 'approved_at' => now(), 'admin_confirmed_at' => now(), 'approved_by' => $administrator->id, 'subscribed_at' => now(), 'unsubscribed_at' => null, 'rejected_at' => null]);

        if (! $updated) {
            throw ValidationException::withMessages(['status' => 'Seule une demande en attente peut être validée. Une adresse refusée ou désinscrite ne peut pas être réactivée automatiquement.']);
        }

        $this->refresh();
    }

    public function reject(): void
    {
        $updated = self::query()->whereKey($this->id)->whereIn('status', ['pending', 'pending_approval', 'subscribed'])
            ->update(['status' => 'rejected', 'rejected_at' => now(), 'approved_at' => null, 'verification_token' => null]);

        if (! $updated) {
            throw ValidationException::withMessages(['status' => 'Seule une demande en attente ou un abonnement actif peut être refusé.']);
        }

        $this->refresh();
    }

    public function unsubscribe(): void
    {
        $this->forceFill(['status' => 'unsubscribed', 'unsubscribed_at' => now(), 'approved_at' => null, 'verification_token' => null])->save();
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
            'created_at' => $this->created_at?->toIso8601String(),
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'admin_confirmed_at' => $this->admin_confirmed_at?->toIso8601String(),
            'approved_by' => $this->approved_by,
            'rejected_at' => $this->rejected_at?->toIso8601String(),
            'verification_sent_at' => $this->verification_sent_at?->toIso8601String(),
            'subscribed_at' => $this->subscribed_at?->toIso8601String(),
            'unsubscribed_at' => $this->unsubscribed_at?->toIso8601String(),
        ];
    }
}
