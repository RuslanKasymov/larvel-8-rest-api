<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    public $timestamps = false;
    public $incrementing = false;

    protected $primaryKey = 'email';
    protected $keyType = 'string';

    protected $fillable = [
        'email', 'token'
    ];
}
