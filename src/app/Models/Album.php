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
     * Get the photos in this album.
     */
	public function photos(): BelongsToMany
	{
		return $this->belongsToMany(Photo::class);
	}

    /**
     * Get the cover of this album.
     */
    public function coverPhoto(): BelongsTo
    {
        return $this->belongsTo(Photo::class, 'photo_id');
    }
}
