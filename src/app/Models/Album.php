<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Enums\DatePrecision;

class Album extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'photo_id',
        'start_date',
        'end_date',
        'date_precision',
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
            'date_precision' => DatePrecision::class
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
