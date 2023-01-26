<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function getStatus()
    {
        return $this->belongsTo(Status::class,'status_id','id');
    }

    public function scopeFiltro($query, $request)
    {
        return $query
            ->when($request->name, function ($query, $name) {
                return $query->where('name','Like',"%$name%");
            })
            ->when($request->description, function ($query, $description) {
                return $query->where('description','LIKE',"%$description%");
            });
    }
}
