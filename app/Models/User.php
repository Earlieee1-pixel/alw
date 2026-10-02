<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Mga field nga pwede mag-mass assign.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'invite_code',
        'referred_by',
        'status',
    ];

    /**
     * Mga field nga dili ibalik sa JSON response.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * I-cast ang mga column sa tamang type.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // =========================================================
    // BOOT — auto-generate invite code kung bag-ong user
    // =========================================================

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            // I-generate ang unique 8-character invite code kung wala pa
            if (empty($user->invite_code)) {
                $user->invite_code = static::generateUniqueInviteCode();
            }
        });
    }

    /**
     * I-generate ang unique invite code — 8 uppercase alphanumeric characters.
     */
    public static function generateUniqueInviteCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::where('invite_code', $code)->exists());

        return $code;
    }

    // =========================================================
    // RELATIONSHIPS
    // =========================================================

    /**
     * Kinsa ang nag-invite niini nga user (upline).
     */
    public function upline(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    /**
     * Mga tawo nga gi-invite niini nga user (direct downline).
     */
    public function downlines(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    /**
     * Tanan nga downline recursively — para sa tree view.
     */
    public function allDownlines(): HasMany
    {
        return $this->downlines()->with('allDownlines');
    }

    // =========================================================
    // HELPERS
    // =========================================================

    /**
     * Check kung active ang account sa user.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Ibalik ang invite link para ma-share sa uban.
     */
    public function inviteLink(): string
    {
        return url('/register?ref=' . $this->invite_code);
    }
}
