<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a token that grants (temporary) access to a shared resource,
 * such as a photo, album, or folder.
 *
 * Can include an optional expiration date and related to one specific album.
 */
class ShareToken extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'album_id', 'token', 'expires_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Get the Album associated with this ShareToken.
     *
     * @return BelongsTo<Album, ShareToken>
     */
    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    /**
     * Generate and persist a new share token for the given Album.
     *
     * @param  int           $albumId   The ID of the album to share
     * @param  DateTime|null $expiresAt Optional expiration date/time
     * @return static
     */
    public static function generateForAlbum(int $albumId, ?\DateTime $expiresAt = null): self
    {
        return self::create([
            'album_id'   => $albumId,
            'token'      => Str::random(40),
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Determine whether this share token has expired.
     *
     * @return bool True if the token has an expiration date and it is in the past
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
