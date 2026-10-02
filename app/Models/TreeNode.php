<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreeNode extends Model
{
    use HasFactory;

    /**
     * Mga field nga pwede mag-mass assign.
     *
     * @var list<string>
     */
    protected $fillable = [
        'position_key',
        'parent_key',
        'display_number',
        'depth',
        'side',
        'name',
        'filled_by',
        'filled_at',
    ];

    /**
     * I-cast ang fields.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'filled_at' => 'datetime',
        ];
    }

    // =========================================================
    // RELATIONSHIPS
    // =========================================================

    /**
     * Kinsa ang nag-fill niini nga slot.
     */
    public function filler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filled_by');
    }

    // =========================================================
    // STATIC HELPERS
    // =========================================================

    /**
     * I-ensure nga ang 31 nodes (5 levels) exist para sa given root key.
     * I-create kung wala pa — idempotent, safe to call multiple times.
     */
    public static function ensureTreeExists(string $rootKey): void
    {
        // I-generate ang 31 nodes para sa 5-level binary tree
        $nodes = static::generateNodeKeys($rootKey, 5);

        foreach ($nodes as $node) {
            static::firstOrCreate(
                ['position_key' => $node['position_key']],
                [
                    'parent_key' => $node['parent_key'],
                    'display_number' => $node['display_number'],
                    'depth' => $node['depth'],
                    'side' => $node['side'],
                ]
            );
        }
    }

    /**
     * I-generate ang tanan nga node definitions para sa 5 levels.
     * Returns flat array of node data.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function generateNodeKeys(string $rootKey, int $levels = 5): array
    {
        $nodes = [];
        $queue = [['key' => $rootKey, 'parent' => null, 'depth' => 0, 'side' => null]];
        $number = 1;

        while (! empty($queue)) {
            $current = array_shift($queue);

            if ($current['depth'] >= $levels) {
                continue;
            }

            $nodes[] = [
                'position_key' => $current['key'],
                'parent_key' => $current['parent'],
                'display_number' => $number++,
                'depth' => $current['depth'],
                'side' => $current['side'],
            ];

            // I-add ang left ug right children kung dili pa sa last level
            if ($levels > $current['depth'] + 1) {
                $queue[] = [
                    'key' => $current['key'].'-L',
                    'parent' => $current['key'],
                    'depth' => $current['depth'] + 1,
                    'side' => 'L',
                ];
                $queue[] = [
                    'key' => $current['key'].'-R',
                    'parent' => $current['key'],
                    'depth' => $current['depth'] + 1,
                    'side' => 'R',
                ];
            }
        }

        return $nodes;
    }

    /**
     * Check kung filled na ang slot.
     */
    public function isFilled(): bool
    {
        return ! empty($this->name);
    }
}
