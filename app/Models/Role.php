<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    const ADMIN = 1;
    const USER = 2;

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
