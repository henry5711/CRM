<?php

namespace App\Http\Controllers\Api\Users;

use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Users\ShowRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Users\ShowIdentityVerificationRequest;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ShowController extends Controller
{
    public function profile(Request $request)
    {


        try {
            DB::beginTransaction();
            $user = User::with(
                'userDetail',
                'userDetail.country',
                'userDetail.gender',
                'userDetail.tpDocument',
                'roles.permissions',
                'userDetail.gender',
                'userDetail.tpDocument',
                'bankAccounts',
                'financing',
                'userDetail.images',
                'userDetail.identityVerification.requestStatuFrontal',
                'userDetail.identityVerification.requestStatuBack',
                'userDetail.identityVerification.requestStatuSelfie',
                'userDetail.identityVerification.requestStatuAddress',
                'userDetail.identityVerification.requestStatuGeneral',
                'userDetail.broker.getCountry'
            )
                ->findOrFail(\App\Services\AuthCognito::user()->id);
            // return $user;
            if ($user) {
                foreach ($user->userDetail->images as $img) {
                    $img->pathS3 =  'img/ewex-car/identity-verification/';
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.users.show.profile.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ]);
        }

        return response()->json([
            "message"       => "Datos de usuario!",
            "response"      => UserResource::make($user),
        ]);
    }

    public function showIdentityVerificationRequests(ShowIdentityVerificationRequest $request, int $id)
    {

        $userDetail = UserDetail::where('user_id', $id)->first();
        try {
            DB::beginTransaction();
            if ($userDetail) {
                $users = User::withTrashed()->with(
                    'bankAccounts',
                    'userDetail.images',
                    'userDetail.identityVerification.requestStatuFrontal',
                    'userDetail.identityVerification.requestStatuBack',
                    'userDetail.identityVerification.requestStatuSelfie',
                    'userDetail.identityVerification.requestStatuAddress',
                    'userDetail.identityVerification.requestStatuGeneral',
                    'userDetail.broker.getCountry'
                )
                    ->whereHas('userDetail', function ($query) use ($userDetail) {
                        $query->whereHas('identityVerification', function ($query) use ($userDetail) {
                            $query->where('user_detail_id', '=', $userDetail->id);
                        });
                    })->orderBy("id", "ASC")->first();

                if ($users) {
                    foreach ($users->userDetail->images as $img) {
                        $img->pathS3 =  'img/ewex-car/identity-verification/';
                    }
                } else {
                    return response()->json([
                        "errors" => [
                            "message"  => "no es posible realizar la transaccion",
                        ]
                    ], 422);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.users.show.showIdentityVerificationRequests.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ]);
        }

        return response()->json([
            "message"       => "Mostrar detalles de datos de verificacion de documentos (Adm)",
            "response"      => $users ? UserResource::make($users) : " ",
        ]);
    }

    public function show(ShowRequest $request, int $id)
    {

        try {
            DB::beginTransaction();

            $user = User::with(
                'userDetail.gender',
                'userDetail.country',
                'roles',
                'userDetail.tpDocument',
                'userDetail.identityVerification.requestStatuFrontal',
                'userDetail.identityVerification.requestStatuBack',
                'userDetail.identityVerification.requestStatuSelfie',
                'userDetail.identityVerification.requestStatuAddress',
                'userDetail.identityVerification.requestStatuGeneral',
                'userDetail.broker.getCountry'
            )
                ->findOrFail($id);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.users.show.show.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "Detalle de usuario",
            "response"      => UserResource::make($user),
        ]);
    }


    public function showUserCode(Request $request)
    {

        try {
            DB::beginTransaction();

            $user = User::with(
                'userDetail.gender',
                'userDetail.country',
                'roles',
                'userDetail.tpDocument',
                'userDetail.identityVerification.requestStatuFrontal',
                'userDetail.identityVerification.requestStatuBack',
                'userDetail.identityVerification.requestStatuSelfie',
                'userDetail.identityVerification.requestStatuAddress',
                'userDetail.identityVerification.requestStatuGeneral',
                'userDetail.broker.getCountry'
            )
            ->where('code_user',$request->code_user)
                ->first();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.users.show.show.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "Detalle de usuario",
            "response"      => UserResource::make($user),
        ]);
    }

    public function generatorQrClient(Request $request,$id){

        $front=$request->server->get('WEB_APP_URL');
        $user = User::find($id);
        if($user){
            $qrCode = QrCode::size(200)
            ->backgroundColor(0,0,0)
            ->color(255,255,255)
            ->margin(1)
            ->generate($front.'/agente/'.$user->code_user);

            return $qrCode;
        }

        else{
            return response()->json([
                'data' => [
                    'title'  => [__('messages.users.show.generatorQrClient.internal_error')],
                    'errors' => 'no existe este usuario'
                ]
            ]);
        }



    }
}
