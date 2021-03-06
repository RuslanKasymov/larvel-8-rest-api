<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Facades\JWTAuth;

class Media extends Model
{
    protected $fillable = [
        'link',
        'name',
        'filepath',
        'owner_id',
        'is_public',
        'thumbnail'
    ];

    protected $casts = [
        'is_public' => 'boolean'
    ];

    protected $hidden = ['pivot'];

    public function scopeApplyMediaPermissionRestrictions($query)
    {
        if (!JWTAuth::getToken()) {
            $query->where('is_public', true);

            return;
        }

        $user = JWTAuth::toUser();

        if ($user->role_id !== Role::ADMIN) {
            $query->where(function ($subQuery) use ($user) {
                $subQuery
                    ->where('is_public', true)
                    ->orWhere('owner_id', $user->id);
            });
        }
    }
}
