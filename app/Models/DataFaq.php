<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataFaq extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
    'title',
    'body',
    'category_faq_id',
    ];

    public function images()
    {
        return $this->morphMany(Image::class , 'imageable');
    }
}
