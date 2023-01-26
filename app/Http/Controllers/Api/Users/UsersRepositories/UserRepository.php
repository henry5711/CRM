<?php

namespace App\Http\Controllers\Api\Users\UsersRepositories;

use App\Models\User;

class UserRepository {
    protected $model;
    protected $object;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function Create(array $data, $role){
        return $this->model::create($data)->assignRole($role);
    }

    public function findUserByEmail($email){
        return $this->model::where('email',$email)->first();
    }

    public function disabledUser($email){
        $user = self::findUserByEmail($email);
        if($user){
            return $user->delete();
        }
        return true;
    }

    public function enabledUser($email){
        $user = $this->model::withTrashed()->where('email',$email)->first();
        if($user){
            return $user->restore();
        }
        return true;
    }

    public function VerifiedIdentity($user_id): bool
    {
        $user = $this->model::find($user_id);
        $bool = $user->userDetail->identityVerification;
        if(!$bool || $bool->status_general === 3){
            return false;
        }
        return  true;
    }

}
