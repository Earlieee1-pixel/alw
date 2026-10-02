<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_one',
        'user_two',
        'last_message_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    // =========================================================
    // RELATIONSHIPS
    // =========================================================

    /**
     * First participant sa conversation.
     */
    public function participantOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one');
    }

    /**
     * Second participant sa conversation.
     */
    public function participantTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two');
    }

    /**
     * Tanan nga messages sa conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Pinaka-bag-o nga message.
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    // =========================================================
    // HELPERS
    // =========================================================

    /**
     * I-get ang other participant base sa current user ID.
     */
    public function otherParticipant(int $currentUserId): User
    {
        return $this->user_one === $currentUserId
            ? $this->participantTwo
            : $this->participantOne;
    }

    /**
     * I-find o create ang conversation tali sa duha ka users.
     * Always sort by ID para avoid duplicates.
     */
    public static function findOrCreateBetween(int $userA, int $userB): self
    {
        // I-sort para consistent ang order
        [$one, $two] = $userA < $userB ? [$userA, $userB] : [$userB, $userA];

        return static::firstOrCreate([
            'user_one' => $one,
            'user_two' => $two,
        ]);
    }

    /**
     * I-count ang unread messages para sa given user.
     */
    public function unreadCountFor(int $userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();
    }
}
