<?php

namespace App\Models;

use App\Traits\HandleStorage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    use HandleStorage;

    protected $fillable = ['filename','url','imageable_type','imageable_id', 'tag', 'category'
,'name','description'];
   protected $appends = ['from_image',];

    public function imageable()
    {
        return $this->morphTo();
    }

    public function getImageAttribute($key)
    {
       return $this->getS3FileUrl($key);
    }
    public function scopeFiltro($query, $request)
    {
        return $query
            ->when($request->name, function ($query, $name) {
                return $query->where('name','LIKE', "%$name%");
            })
            ->when($request->imageable_id, function ($query, $imageable_id) {
                return $query->where('imageable_id', $imageable_id);
            })
            ->when($request->category, function ($query, $category) {
                return $query->where('category', $category);
            });
    }

    public function getFromImageAttribute()
    {
        if(isset($this->attributes['url'])) {
            $url = $this->attributes['url'];
            $data = explode(".com/", $url);
            if(count($data)>1){
                return $this->getS3FileUrl($data[1]);
            }
            else{
                return $this->getS3FileUrl($this->attributes['url']);
            }
        } else {
            return null;
        }
    }



    /*public function getnameAttribute($key)
    {
        if(isset($this->attributes['url'])) {
            $url = $this->attributes['url'];
            $data = explode(".com/", $url);
            return $this->getS3FileUrl($data[1]);
        } else {
            return null;
        }
    }*/

}
