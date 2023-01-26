<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Typification extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'type_typification_id'
    ];

    public function type()
    {
        return $this->hasOne(typeTypification::class,'id','type_typification_id');
    }

    public function scopeFiltro($query, $request)
    {
        return $query
            ->when($request->name, function ($query, $name) {
                return $query->where('name','Like',"%$name%");
            })
            ->when($request->type_typification_id, function ($query, $type_typification_id) {
                return $query->where('type_typification_id',$type_typification_id);
            });
    }

}
