<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Represents a key-value setting in the application.
 *
 * Used to store application configuration options in the database.
 */
class Setting extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value'
    ];
}
