<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

use App\Enums\AlbumType;
use App\Enums\DatePrecision;
use App\Enums\UserRole;
use App\Traits\RecordsStats;

/**
 * Represents a photo album in the application.
 *
 * Stores metadata such as name, display name, type, date range, and associated photos.
 * Can have a cover photo and keeps track of photo counts.
 */
class Album extends Model
{
    use RecordsStats;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'type',
        'photo_id',
        'start_date',
        'end_date',
        'date_precision',
        'impressions',
    ];

    /**
     * The attributes that are dynamically appended.
     *
     * @var list<string>
     */
    protected $appends = [
        'display_name'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at'     => 'datetime:Y-m-d\TH:i:sP',
            'updated_at'     => 'datetime:Y-m-d\TH:i:sP',
            'start_date'     => 'date:Y-m-d',
            'end_date'       => 'date:Y-m-d',
            'date_precision' => DatePrecision::class,
            'type'           => AlbumType::class,
            'impressions'    => 'integer',
        ];
    }

    /**
     * Accessor for the Album display name.
     *
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        return strtr(config('settings.album_name_tpl', '{name}'), [
            '{id}'    => $this->id,
            '{name}'  => $this->name,
            '{year}'  => $this->start_date?->year,
            '{month}' => $this->start_date?->format('m'),
            '{day}'   => $this->start_date?->format('d'),
            '{type}'  => $this->type?->value ?? '',
        ]);
    }

    /**
     * Get the Photos associated with this Album.
     *
     * @return BelongsToMany<Photo>
     */
	public function photos(): BelongsToMany
	{
		return $this->belongsToMany(Photo::class);
	}

    /**
     * Get the cover for this Album.
     *
     * @return BelongsTo<Photo, Album>
     */
    public function coverPhoto(): BelongsTo
    {
        return $this->belongsTo(Photo::class, 'photo_id');
    }
}
