<?php

namespace App\Http\Controllers\Api\Users;

use App\Exceptions\CognitoApiException;
use App\Http\Controllers\Api\Users\UsersRepositories\UserRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\DisabledUserRequest;
use App\Http\Requests\Users\EnabledUserRequest;
use App\Http\Services\CognitoService;
use App\Http\Services\EbangoService;
use App\Services\AuthCognito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller{
    protected $ebango;
    protected $cognito;
    protected $UserRepository;
    public function __construct(
            CognitoService $cognito,
            EbangoService $ebango,
            UserRepository $repository
            )
    {
        $this->cognito = $cognito;
        $this->ebango = $ebango;
        $this->UserRepository = $repository;
    }

    public function disabledUser(DisabledUserRequest $request){
        DB::beginTransaction();
        try{
            $user = $this->UserRepository->findUserByEmail($request->email);
            $tokenEbango = $user->ebangoToken->where('revoked',false)->first();
            $responseEbango  = $this->ebango->disabledUser($tokenEbango);
            $responseCognito = $this->cognito->disabledUser($request->ip(),$request->email);
            $this->UserRepository->disabledUser($request->email);
            DB::commit();

        } catch (CognitoApiException $e) {
            return custom_response_exception($e, 'Cognito Error');
        } catch (\Exception $e) {
            DB::rollBack();
            return custom_response_exception($e, __('messages.users.register.register.internal_error'));
        }
        $message = [__('messages.users.admin.disabled_user')];
        return custom_response_sucessfull($message);
    }

    public function enabledUser(EnabledUserRequest $request){
        DB::beginTransaction();
        try{

            $responseCognito = $this->cognito->enabledUser($request->ip(),$request->email);
            $this->UserRepository->enabledUser($request->email);
            DB::commit();

        } catch (CognitoApiException $e) {
            return custom_response_exception($e, 'Cognito Error');
        } catch (\Exception $e) {
            DB::rollBack();
            return custom_response_exception($e, __('messages.users.register.register.internal_error'));
        }
        $message = [__('messages.users.admin.enabled_user')];
        return custom_response_sucessfull($message);
    }

}
