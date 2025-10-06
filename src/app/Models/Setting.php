<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * The primary key definition.
     */
    protected $primaryKey = 'key';
    protected $keyType    = 'string';
    public $incrementing  = false;

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
