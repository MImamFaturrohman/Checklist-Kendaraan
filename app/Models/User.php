<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['username', 'name', 'email', 'password', 'role', 'last_seen_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ONLINE_THRESHOLD_MINUTES = 3;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_seen_at'      => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function isOnline(?int $minutes = null): bool
    {
        if (! $this->last_seen_at) {
            return false;
        }

        $threshold = $minutes ?? self::ONLINE_THRESHOLD_MINUTES;

        return $this->last_seen_at->gte(now()->subMinutes($threshold));
    }

    public function markOnline(): void
    {
        static::whereKey($this->id)->update(['last_seen_at' => now()]);
    }

    public static function markOfflineById(?int $userId): void
    {
        if (! $userId) {
            return;
        }

        static::whereKey($userId)->update(['last_seen_at' => null]);
    }

    public function sppds(): HasMany
    {
        return $this->hasMany(Sppd::class);
    }

    public function bbmReports(): HasMany
    {
        return $this->hasMany(BbmReport::class);
    }

    public function vehicleUsageLogs(): HasMany
    {
        return $this->hasMany(VehicleUsageLog::class);
    }
}
