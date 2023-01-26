<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'country_id',
        'tp_document_id',
        'ip',
        'gender_id',
        'document',
        'address',
        'birth',
        'code_phone',
        'profile_background_image_id',
        'phone',
        'phone_verified_at',
        'confirmation_code_phone',
        'broker_id',
        'call_center_id',
        'name_card',
        'code_phone_card',
        'phone_card',
        'email_card',
    ];



    public function images()
    {
        return $this->morphMany(Image::class , 'imageable');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getCallCenter()
    {
        return $this->hasOne(User::class, 'id', 'call_center_id');
    }

    public function country()
    {
      return $this->hasOne(Country::class, 'id' , 'country_id');
    }
    public function tpDocument()
    {
      return $this->hasOne(TpDocument::class, 'id' , 'tp_document_id');
    }

    public function gender()
    {
      return $this->hasOne(Gender::class, 'id' , 'gender_id');
    }

    public function identityVerification()
    {
        return $this->hasOne(IdentityVerification::class,'user_detail_id', 'id');
    }

    public function broker()
    {
      return $this->belongsTo(broker_country::class,'broker_id');
    }


}
