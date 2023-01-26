<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;
    use SoftDeletes;

    protected $guard_name = 'api';
        /**
     * Holds the methods' names of Eloquent Relations
     * to fall on delete cascade or on restoring
     *
     * @var array
     */
    protected static $relations_to_cascade = ['userDetail'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model){
            $model->code_user=(string) Str::uuid();
        });

        static::deleting(function($resource) {
            foreach (static::$relations_to_cascade as $relation) {
                foreach ($resource->{$relation}()->get() as $item) {
                    $item->delete();
                }
            }
        });

        static::restoring(function($resource) {
            foreach (static::$relations_to_cascade as $relation) {
                foreach ($resource->{$relation}()->get() as $item) {
                    $item->withTrashed()->restore();
                }
            }
        });
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'lastname',
        'email',
        'password',
        'address',
        'brith',
        'cognitoId',
        'ebango_register',
        
        'code_user',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function userDetail()
    {
        return $this->hasOne(UserDetail::class);
    }

    public function code()
    {
        return $this->hasOne(Code::class, 'user_id', 'id');
    }

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    public function financing()
    {
        return $this->hasOne(Financing::class, 'user_id', 'id');
    }

    public function ebangoToken()
    {
        return $this->hasMany(EbangoToken::class);
    }

    public function cognitoTokens(){
        return $this->hasMany(CognitoToken::class);
    }


    public function scopeFiltro($query, $request)
    {
        return $query
            ->when($request->rol_id, function ($query, $rol) {
                return $query->whereHas(
                    "roles", function ($q) use ($rol) {
                        $q->where('roles.id',$rol);
                    }
                );
            });
    }

}
