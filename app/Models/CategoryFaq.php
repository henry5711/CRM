<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryFaq extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
    'title',
    'body'
    ];

    public function faqs()
    {
        return $this->hasMany(DataFaq::class);
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

}
