<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class typeTypification extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description'
    ];

    public function scopeFiltro($query, $request)
    {
        return $query
            ->when($request->name, function ($query, $name) {
                return $query->where('name','Like',"%$name%");
            });
    }
}
