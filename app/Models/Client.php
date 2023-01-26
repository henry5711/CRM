<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'document',
        'name',
        'email',
        'code_phone',
        'phone',
        'origin_id',
        'segmento',//'personal','corporativo','otro'
        'calender',
        'status_id',
        'user_id',
        'type_typification_id',
        'typification_id',
        'country_id',
        //'phone_ax'
    ];

    public function getCountry()
    {
        return $this->hasOne(Country::class, 'id', 'country_id');
    }

    public function getStatus()
    {
        return $this->hasOne(Status::class,'id','status_id');
    }

    public function getOrigin()
    {
        return $this->hasOne(Origin::class,'id','origin_id');
    }

    public function getUser()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function type()
    {
        return $this->hasOne(typeTypification::class,'id','type_typification_id');
    }


    public function typification()
    {
        return $this->hasOne(Typification::class,'id','typification_id');
    }

    public function getObserve(){
        return $this->hasMany(CrmObserve::class,'client_id','id');
    }

    public function scopeFiltro($query, $request)
    {
        return $query
            ->when($request->name, function ($query, $name) {
                return $query->where('name','Like',"%$name%");
            })
            ->when($request->document, function ($query, $document) {
                return $query->where('document','Like',"%$document%");
            })
            ->when($request->email, function ($query, $email) {
                return $query->where('email','Like',"%$email%");
            })
            ->when($request->status_id, function ($query, $status_id) {
                return $query->where('status_id',$status_id);
            })
            ->when($request->code_phone, function ($query, $code_phone) {
                return $query->where('code_phone',$code_phone);
            })
            ->when($request->phone, function ($query, $phone) {
                return $query->where('phone','LIKE',"%$phone%");
            })
            ->when($request->type_typification_id, function ($query, $type_typification_id) {
                return $query->where('type_typification_id',$type_typification_id);
            })
            ->when($request->typification_id, function ($query, $typification_id) {
                return $query->where('typification_id',$typification_id);
            })
            ->when($request->segmento, function ($query, $segmento) {
                return $query->where('segmento','LIKE',"%$segmento%");
            })
            ->when($request->user_id, function ($query, $user_id) {
                return $query->where('user_id',$user_id);
            });
    }
}
