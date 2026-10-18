<?php

namespace App\Models;

use Database\Factories\UserFactory;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'email', 'password', 'is_admin', 'role', 'profile_photo_path', 'onboarding_completed_at', 'invitation_token', 'invitation_expires_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    use SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'onboarding_completed_at' => 'datetime',
            'invitation_expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (self $user): void {
            if (! $user->isForceDeleting()) {
                $user->forceFill(['remember_token' => null])->saveQuietly();
            }

            if (config('session.driver') === 'database') {
                DB::table(config('session.table'))->where('user_id', $user->id)->delete();
            }

            DB::table(config('auth.passwords.'.config('auth.defaults.passwords').'.table'))
                ->where('email', $user->email)->delete();
        });
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isEditor(): bool
    {
        return $this->role === 'editor';
    }

    /**
     * Un editor ne peut pas modifier les blocs réservés au superadmin.
     */
    public function canEditBlock(string $key): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return ! in_array($key, config('eden.superadmin_only_blocks', []), true);
    }
}
