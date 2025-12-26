<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Traits\RecordsStats;

/**
 * Represents a photo in the media library.
 *
 * Stores metadata such as filename, dimensions, camera info, exposure settings,
 * and statistics like impressions and downloads.
 * Belongs to a folder and can belong to multiple albums.
 */
class Photo extends Model
{
    use RecordsStats;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'folder_id',
        'blurhash',
        'filename',
        'width',
        'height',
        'camera',
        'make',
        'orientation',
        'aperture',
        'iso',
        'exposure_time',
        'focal_length',
        'taken_at',
        'size',
        'impressions',
        'downloads',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'width'    => 'integer',
        'height'   => 'integer',
        'size'     => 'integer',
        'taken_at' => 'datetime',
    ];

    /**
     * Get the Folder this Photo belongs to.
     *
     * @return BelongsTo<Folder, Photo>
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     *  Get the Albums this Photo is associated with.
     *
     * @return BelongsToMany<Album>
     */
	public function albums(): BelongsToMany
	{
		return $this->belongsToMany(Album::class);
	}
}
