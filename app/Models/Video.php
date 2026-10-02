<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Video extends Model
{
    use HasFactory;

    /**
     * Mga field nga pwede mag-mass assign.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'embed_url',
        'video_id',
        'source',
        'category',
        'is_published',
        'posted_by',
    ];

    /**
     * I-cast ang fields sa tamang type.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    // =========================================================
    // RELATIONSHIPS
    // =========================================================

    /**
     * Kinsa ang nag-post niini nga video.
     */
    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    // =========================================================
    // HELPERS
    // =========================================================

    /**
     * I-extract ang video ID gikan sa YouTube o Vimeo URL.
     */
    public static function extractVideoId(string $url, string $source): ?string
    {
        if ($source === 'youtube') {
            // I-support ang youtube.com/watch?v= ug youtu.be/ formats
            preg_match('/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches);

            return $matches[1] ?? null;
        }

        if ($source === 'vimeo') {
            preg_match('/vimeo\.com\/(\d+)/', $url, $matches);

            return $matches[1] ?? null;
        }

        return null;
    }

    /**
     * I-build ang embed URL gikan sa video ID.
     */
    public function getEmbedUrl(): string
    {
        if ($this->source === 'youtube' && $this->video_id) {
            return "https://www.youtube.com/embed/{$this->video_id}?rel=0&modestbranding=1";
        }

        if ($this->source === 'vimeo' && $this->video_id) {
            return "https://player.vimeo.com/video/{$this->video_id}";
        }

        return $this->embed_url;
    }

    /**
     * I-build ang thumbnail URL.
     */
    public function getThumbnailUrl(): string
    {
        if ($this->source === 'youtube' && $this->video_id) {
            return "https://img.youtube.com/vi/{$this->video_id}/mqdefault.jpg";
        }

        return '';
    }
}
