<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TakComment extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'content',
        'tak_id'
    ];

    public function getUser()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function geTak()
    {
        return $this->hasOne(Tak::class, 'id', 'tak_id');
    }

    public function scopeFiltro($query, $request)
    {
        return $query
            ->when($request->tak_id, function ($query, $tak_id) {
                return $query->where('tak_id',$tak_id);
            })
            ->when($request->user_id, function ($query, $user_id) {
                return $query->where('user_id',$user_id);
            });
    }
}
