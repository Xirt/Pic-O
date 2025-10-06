<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareToken extends Model
{
    protected $fillable = [
        'album_id', 'token', 'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    // Generate token automatically
    public static function generateForAlbum(int $albumId, ?\DateTime $expiresAt = null): self
    {
        return self::create([
            'album_id'   => $albumId,
            'token'      => Str::random(40),
            'expires_at' => $expiresAt,
        ]);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
