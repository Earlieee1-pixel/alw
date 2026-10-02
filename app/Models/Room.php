<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Room extends Model
{
    use HasFactory;

    /**
     * Mga field nga pwede mag-mass assign.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'room_code',
        'jitsi_room',
        'description',
        'status',
        'scheduled_at',
        'created_by',
    ];

    /**
     * I-cast ang fields sa tamang type.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    // =========================================================
    // BOOT — auto-generate room code ug jitsi room name
    // =========================================================

    protected static function booted(): void
    {
        static::creating(function (Room $room): void {
            // I-generate ang unique 8-char room code para i-share sa members
            if (empty($room->room_code)) {
                $room->room_code = static::generateUniqueRoomCode();
            }

            // I-generate ang Jitsi room name — ALW prefix + random string
            if (empty($room->jitsi_room)) {
                $room->jitsi_room = 'ALW-' . strtoupper(Str::random(10));
            }
        });
    }

    /**
     * I-generate ang unique 8-character room code.
     */
    public static function generateUniqueRoomCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::where('room_code', $code)->exists());

        return $code;
    }

    // =========================================================
    // RELATIONSHIPS
    // =========================================================

    /**
     * Kinsa ang nag-create sa room.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // =========================================================
    // HELPERS
    // =========================================================

    /**
     * I-build ang Jitsi Meet URL para sa room.
     */
    public function getJitsiUrl(): string
    {
        return "https://meet.jit.si/{$this->jitsi_room}";
    }

    /**
     * Check kung active pa ang room.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
