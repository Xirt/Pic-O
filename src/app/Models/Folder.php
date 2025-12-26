<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a folder in the media library.
 *
 * Stores folder path, name, and parent-child relationships.
 */
class Folder extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'path',
        'parent_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime:Y-m-d\TH:i:sP',
            'updated_at' => 'datetime:Y-m-d\TH:i:sP',
        ];
    }

    /**
     * Get the parent Folder.
     *
     * @return BelongsTo<Folder, Folder>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    /**
     * Get the child Folders.
     *
     * @return HasMany<Folder>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    /**
     * Get the Photos contained in this Folder.
     *
     * @return HasMany<Photo>
     */
	public function photos(): HasMany
	{
		return $this->hasMany(Photo::class);
	}
}
