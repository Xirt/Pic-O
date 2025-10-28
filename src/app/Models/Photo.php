<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Traits\RecordsStats;

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
     * Get the folder for this photo.
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get the albums for this photo.
     */
	public function albums(): BelongsToMany
	{
		return $this->belongsToMany(Album::class);
	}
}
