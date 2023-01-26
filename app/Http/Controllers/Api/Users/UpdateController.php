<?php

namespace App\Http\Controllers\Api\Users;

use App\Models\Code;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Image;
use App\Models\Vehicle;
use Twilio\Rest\Client;
use \SendGrid\Mail\Mail;
use App\Models\Automotive;
use App\Models\UserDetail;
use App\Models\PasswordCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\ChangePasswordCognitoRequest;
use App\Http\Resources\UserResource;
use App\Models\IdentityVerification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Users\RestoreRequest;
use App\Mail\SendCodeEmailVerifiedMailable;
use App\Http\Requests\Users\UpdateAdmRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Users\UpdateIdentityVerificationImagesAdmRequest;
use App\Http\Requests\Users\UpdateRequest;
use App\Http\Requests\Users\IdinteficationAllRequest;
use App\Http\Resources\UserDetailResource;
use App\Models\Client as ModelsClient;
use App\Models\Setting;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;
use Ellaisys\Cognito\Auth\ChangePasswords;

class UpdateController extends Controller
{
    use ChangePasswords;

    /**
     * @OA\Post(
     *     path="/api/users,
     *     tags={"Users"},
     *     summary="Actualizar Usuario (adm).",
     *     security={
     *       {"api_key": {}},
     *     },
     *     description="Actualizar cualquier usuario (adm).",
     *     operationId="update",
     *     @OA\Parameter(
     *         name="locale",
     *         in="path",
     *         description="locale , lenguaje del EP",
     *         required=false,
     *         example="es",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="path",
     *         description="name , nombre del usuario",
     *         required=true,
     *         example="victor",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="lastname",
     *         in="path",
     *         description="lastname , apellido del usuario",
     *         required=true,
     *         example="perez",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="path",
     *         description="email , correo electronico del usuario",
     *         required=true,
     *         example="victor@gmail.com",
     *         @OA\Schema(
     *             type="string",
     *             format="email",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="new_password",
     *         in="path",
     *         description="new_password , nueva contrasena del usuario",
     *         required=false,
     *         example="1as@sdf*2",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="new_password_confirmation",
     *         in="path",
     *         description="new_password_confirmation , confirmacion de la nueva contrasena ",
     *         required=false,
     *         example="1as@sdf*2",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="country_id",
     *         in="path",
     *         description="country_id , id del pais del usuario ",
     *         required=false,
     *         example="1",
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="gender_id",
     *         in="path",
     *         description="gender_id , id de genero",
     *         required=false,
     *         example="1",
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="tp_document_id",
     *         in="path",
     *         description="tp_document_id , id de tipo de documento",
     *         required=false,
     *         example="1",
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="role_id",
     *         in="path",
     *         description="role_id , id de rol para usuario",
     *         required=false,
     *         example="1",
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="document",
     *         in="path",
     *         description="document , documento de identidad usuario",
     *         required=false,
     *         example="19529391a",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="birth",
     *         in="path",
     *         description="birth , fecha de nacimiento formato Y/m/d",
     *         required=false,
     *         example="1991/04/28",
     *         @OA\Schema(
     *             type="date",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="phone",
     *         in="path",
     *         description="phone , numero de telefono",
     *         required=false,
     *         example="555555555",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="code_phone",
     *         in="path",
     *         description="code_phone , numero de telefono",
     *         required=false,
     *         example="+57",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="address",
     *         in="path",
     *         description="address , dirección",
     *         required=false,
     *         example="Cra 85b",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Actualizar cualquier usuario (Adm)"
     *     ),
     * )
     */
    public function update(UpdateRequest $request)
    {
        $id = $request->id;
        DB::beginTransaction();
        try {
            if ($request->profile_background_image_id) {
                $img = Image::where('id', $request->profile_background_image_id)->first();
                if ($img->imageable_type <> 'App\Models\SystemImageType') {
                    return response()->json([
                        "errors" => [
                            "profile_background_image_id" => ["El id de la imagen de fondo de perfil, no es correcto"],
                        ]
                    ], 422);
                }
            }

            $user = User::findOrFail($id);
            if($user->hasRole('Admin')){
                if($request->email){
                    request()->request->remove('email');
                }
            }
            /***************************** */
            //Hasta tener el codigo en cognito
            request()->request->remove('email');
            /***************************** */
            $currentEmail = $user->email;

            //validacion del code en caso que el email sea distinto del ya existente.

            if ($request->has('email')) {
                if ($user->email != $request->email) {
                    if (!$request->has('code_email')) {
                        return response()->json([
                            'data' => [
                                'title' => [__('messages.users.update.update.internal_error')],
                                'errors' => "Campo code_email es requerido",
                            ]
                        ], Response::HTTP_BAD_REQUEST);
                    } else {
                        $code = Code::where('code', '=', $request->code_email)
                            ->where('user_id', '=', $user->id)->first();

                        if (is_null($code)) {
                            return response()->json([
                                'data' => [
                                    'title' => [__('messages.users.update.update.internal_error')],
                                    'errors' => "Codigo Invalido",
                                ]
                            ], Response::HTTP_BAD_REQUEST);
                        }

                        $code->delete();
                    }
                }
            }
/*Por remplazar
            $updateCognito = $this->updateCognito($user, $request);

            if ($updateCognito <> 1 && $updateCognito <> 0) {
                if ($updateCognito === 'AliasExistsException') {
                    return response()->json([
                        'errors' => [
                            'email' => ['El correo electrónico ya ha sido registrado.'],
                        ]
                    ]);
                } //End if
                return $updateCognito;
            }
*/
            if($request->phone_country_code != null){
                $request['code_phone'] = $request->phone_country_code;
            }
            $this->updateUserAdm($user, $request);

            $userDetail = UserDetail::where('user_id', $id)->first();
            $currentNumber = $userDetail->phone;

            if (($request->phone != null) && ($currentNumber == null))
            {
                $this->mailNewPhoneAdded($userDetail, $user);
            }

            $this->updateUserDetailAdm($userDetail, $request);

            $userDetail = UserDetail::where('user_id', $id)->first();
            /*Validar sino manda nada guardar algo ya q KYC lo pide OJO por cambiar */
            if(!empty($userDetail)){
                if(!$request->has('birth')) {
                    $request['birth'] = '2000-01-01';
                }
                $userDetail->birth = $request->birth;
                $userDetail->save();
            }


            if ($request->phone && ($request->phone != null) && ($request->phone != $currentNumber))
            {
                $this->mailNewPhoneAddedUpdate($userDetail, $user);
            }

            // return $currentEmail.' '.$request->email.' '.$user->email;

            if ($request->email && ($request->email != null) && ($request->email != $currentEmail)) {
                $this->sendEmailChanged($user, $currentEmail);
            }

            // return"NOO entroooo";
            if ($request->role_id <> null) {
                if ($user->roles[0]['id'] <> $request->role_id) {
                    $user->removeRole($user->roles[0]['id']);
                    $user->assignRole($request->role_id);
                }
            }

            $response = User::where('id', $id)->with('userDetail', 'roles')->first();

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'data' => [
                    'code' => $e->getCode(),
                    'title' => [__('messages.users.update.update.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => [__('messages.users.update.update.success')],
            "response" => UserResource::make($response->load([
                'userDetail.tpDocument',
                'userDetail.gender',
                'userDetail.country',
            ])),
        ]);
    }


    /**
     * @OA\Post(
     *     path="/api/users/valid/CodePhone",
     *     tags={"Users"},
     *     summary="Validar teléfono del usuario",
     *     security={
     *       {"api_key": {}},
     *     },
     *     description="Validar teléfono del usuario",
     *     operationId="validPhone",
     *     @OA\Parameter(
     *         name="locale",
     *         in="path",
     *         description="locale , lenguaje del EP",
     *         required=false,
     *         example="es",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="code , codigo recibido en el telefono del usuario",
     *         required=true,
     *         example="1234-1234",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Validar teléfono del usuario"
     *     ),
     * )
     */
    public function validPhone(Request $request)
    {
        $id = \App\Services\AuthCognito::user()->id;
        $userDetail = UserDetail::where('user_id', $id)->first();

        if ($userDetail->confirmation_code_phone != $request->code) {
            return response()->json([
                "errors" => [
                    "message" => [__('messages.users.update.validPhone.invalid_code')],
                    "response" => [__('messages.users.update.validPhone.invalid_code_resp')]
                ]
            ], 422);
        }

        $response;
        try {
            DB::beginTransaction();

            $userDetail = UserDetail::where('user_id', $id)->first();
            $user = User::where('id', $id)->first();
            $this->updateConfirmationCodePhone($userDetail, $request);

            $this->sendPhoneVerified($userDetail, $user);

            $response = User::where('id', $id)
                ->with('userDetail')
                ->first();


            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code' => $e->getCode(),
                    'title' => [__('messages.users.update.validPhone.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => [__('messages.users.update.validPhone.success')],
            "response" => UserResource::make($response->load([
                'userDetail.tpDocument',
                'userDetail.gender',
                'userDetail.country',
            ])),
        ]);
    }

    /**
     * @OA\get(
     *     path="/api/users/send/CodePhone",
     *     tags={"Users"},
     *     summary="Enviar al teléfono del usuario código de validación",
     *     security={
     *       {"api_key": {}},
     *     },
     *     description="Enviar al teléfono del usuario código de validación",
     *     operationId="sendCodePhone",
     *     @OA\Parameter(
     *         name="locale",
     *         in="path",
     *         description="locale , lenguaje del EP",
     *         required=false,
     *         example="es",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Enviar codigo al telefono de usuario"
     *     ),
     * )
     */
    public function sendCodePhone(Request $request)
    {
        $id = \App\Services\AuthCognito::user()->id;
        $details = UserDetail::where('user_id', $id)->first();
        $phone = $details->phone;
        if (!$details->phone) {
            return response()->json([
                "errors" => [
                    "phone" => [__('messages.users.update.sendCodePhone.not_phone')],
                    "message" => [__('messages.users.update.sendCodePhone.resp_phone')]
                ]
            ], 422);
        }


        $response;
        try {
            DB::beginTransaction();

            if (!$details->phone_verified_at) {
                $validPhone = $this->sendSms($id);
            } else {
                return response()->json([
                    "errors" => [
                        "message" => [__('messages.users.update.sendCodePhone.valid_exist_msg')],
                        "response" => null
                    ]
                ], 422);
            }
            $response = User::where('id', $id)
                ->with('userDetail')
                ->first();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code' => $e->getCode(),
                    'title' => [__('messages.users.update.sendCodePhone.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => [__('messages.users.update.sendCodePhone.success')],
            "response" => UserResource::make($response->load([
                'userDetail.tpDocument',
                'userDetail.gender',
                'userDetail.country',
            ])),
        ]);
    }


    /**
     * @OA\Post(
     *     path="/api/users/update/updateIdentityVerification",
     *     tags={"Users"},
     *     summary="Actualizar imagen verificacion de identidad usuario autenticado",
     *     security={
     *      {"passport": {}},
     *     },
     *     description="Actualizar imagen verificacion de identidad usuario autenticado",
     *     operationId="updateIdentityVerification",
     *     @OA\Parameter(
     *         name="locale",
     *         in="path",
     *         description="locale , lenguaje del EP",
     *         required=false,
     *         example="es",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="file_frontal",
     *         in="path",
     *         description="file_frontal , imagen frontal de documento",
     *         required=false,
     *         example="frontal.png",
     *         @OA\Schema(
     *             type="file",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="file_back",
     *         in="path",
     *         description="file_back , imagen trasera de documento",
     *         required=false,
     *         example="imagentrasera.jpg",
     *         @OA\Schema(
     *             type="file",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="file_selfie",
     *         in="path",
     *         description="file_selfie , imagen selfie del usuario",
     *         required=false,
     *         example="selfie.jpg",
     *         @OA\Schema(
     *             type="file",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="file_address",
     *         in="path",
     *         description="file_address , imagen de documento de domicilio",
     *         required=false,
     *         example="recibodeluz.jpg",
     *         @OA\Schema(
     *             type="file",
     *         ),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Actualizada imagen verificacion de identidad"
     *     ),
     * )
     */
    public function updateIdentityVerification(Request $request)
    {
        $user = \App\Services\AuthCognito::user();
        $userDetail = UserDetail::where('user_id', $user->id)->first();
        $identityVerification = IdentityVerification::where('user_detail_id', $userDetail->id)->first();
        $setting_value = ((Setting::where('setting_name', 'file_address')->first()->setting_value) == 1) ? true : false;

        if (!$identityVerification) {
            return response()->json([
                "errors" => [
                    "message" => __('messages.users.update.updateIdentityVerification.not_request'),
                ]
            ], 422);
        }
        if ($identityVerification->status_general == 2) {
            return response()->json([
                "errors" => [
                    "message" => __('messages.users.update.updateIdentityVerification.error_verify'),
                ]
            ], 422);
        }

        if ($setting_value) {
            if ($identityVerification->status_frontal != 3 && $identityVerification->status_back != 3 && $identityVerification->status_selfie != 3 && $identityVerification->status_address != 3) {
                return response()->json([
                    "errors" => [
                        "message" => __('messages.users.update.updateIdentityVerification.error_wait'),
                    ]
                ], 422);
            }
        } else {
            if ($identityVerification->status_frontal != 3 && $identityVerification->status_back != 3 && $identityVerification->status_selfie != 3) {
                return response()->json([
                    "errors" => [
                        "message" => __('messages.users.update.updateIdentityVerification.error_wait'),
                    ]
                ], 422);
            }
        }


        $validator = Validator::make($request->all(), [
            'file_frontal' => 'nullable|image|mimes:jpeg,png,jpg|max:8192',
            'file_back' => 'nullable|image|mimes:jpeg,png,jpg|max:8192',
            'file_selfie' => 'nullable|image|mimes:jpeg,png,jpg|max:8192',
            'file_address' => 'nullable|image|mimes:jpeg,png,jpg|max:8192',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $statusFrontal = $identityVerification->status_frontal;
        $statusBack = $identityVerification->status_back;
        $statusSelfie = $identityVerification->status_selfie;
        $statusAddress = $identityVerification->status_address;

        // $arrayImagesId = $identityVerification;

        try {
            DB::beginTransaction();

            foreach ($userDetail->images as $image) {

                $busqueda1 = 'front-';
                $busqueda2 = 'back-';
                $busqueda3 = 'selfie-';
                $busqueda4 = 'address-';

                if ((strpos($image->filename, $busqueda1)) === 0) {
                    // $front = array('front_id' => $image->id);
                    // array_push( $arrayImagesId,['front_id' => $image->id] );
                    // $arrayImagesId->front_id = $image->id ;

                    if ($request->file_frontal && $statusFrontal == 3) {
                        $frontal = $request->file_frontal;
                        $frontal->storeAs('img/ewex-car/identity-verification', $image->filename, 's3');

                        $image->updated_at = Carbon::now();
                        $image->update();

                        $identityVerification->status_frontal = 1;
                        $identityVerification->updated_at = Carbon::now();
                        $identityVerification->update();
                    }
                }
                if ((strpos($image->filename, $busqueda2)) === 0) {
                    // $back = array();
                    // array_push( $arrayImagesId, ['back_id' => $image->id]);
                    // $arrayImagesId->back_id = $image->id ;

                    if ($request->file_back && $statusBack == 3) {
                        $back = $request->file_back;
                        $back->storeAs('img/ewex-car/identity-verification', $image->filename, 's3');

                        $image->updated_at = Carbon::now();
                        $image->update();

                        $identityVerification->status_back = 1;
                        $identityVerification->updated_at = Carbon::now();
                        $identityVerification->update();
                    }
                }
                if ((strpos($image->filename, $busqueda3)) === 0) {
                    // $selfie = array('selfie_id' => $image->id);
                    // array_push( $arrayImagesId,['selfie_id' => $image->id] );
                    // $arrayImagesId->selfie_id = $image->id ;
                    if ($request->file_selfie && $statusSelfie == 3) {
                        $selfie = $request->file_selfie;
                        $selfie->storeAs('img/ewex-car/identity-verification', $image->filename, 's3');

                        $image->updated_at = Carbon::now();
                        $image->update();

                        $identityVerification->status_selfie = 1;
                        $identityVerification->updated_at = Carbon::now();
                        $identityVerification->update();
                    }
                }

                if ((strpos($image->filename, $busqueda4)) === 0) {
                    // $address = array('address_id' => $image->id);
                    // array_push( $arrayImagesId, ['address_id' => $image->id] );
                    // $arrayImagesId->address_id = $image->id ;

                    if ($request->file_address && $statusAddress == 3 && $setting_value) {
                        $address = $request->file_address;
                        $address->storeAs('img/ewex-car/identity-verification', $image->filename, 's3');

                        $image->updated_at = Carbon::now();
                        $image->update();

                        $identityVerification->status_address = 1;
                        $identityVerification->updated_at = Carbon::now();
                        $identityVerification->update();
                    }
                }
            }
            $statusVerify = IdentityVerification::where('user_detail_id', $userDetail->id)->first();
            if ($setting_value) {
                if ($statusVerify->status_frontal <> 3 && $statusVerify->status_back <> 3 && $statusVerify->status_selfie <> 3 && $statusVerify->status_address <> 3) {
                    $statusVerify->status_general = 1;
                    $statusVerify->updated_at = Carbon::now();
                    $statusVerify->update();
                }
            } else {
                if ($statusVerify->status_frontal <> 3 && $statusVerify->status_back <> 3 && $statusVerify->status_selfie <> 3) {
                    $statusVerify->status_general = 1;
                    $statusVerify->updated_at = Carbon::now();
                    $statusVerify->update();
                }
            }


            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code' => $e->getCode(),
                    'title' => [__('messages.users.update.updateIdentityVerification.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => [__('messages.users.update.updateIdentityVerification.success')],
        ], 200);
    }


    /**
     * @OA\Post(
     *     path="/api/users/update/updateIdentityVerificationImagesAdm/{identity_verification_id}",
     *     tags={"Users"},
     *     summary="Actualizar estatus de verificacion de identidad (Adm)",
     *     security={
     *      {"passport": {}},
     *     },
     *     description="Actualizar estatus de verificacion de identidad (Adm).",
     *     operationId="updateIdentityVerificationImagesAdm",
     *     @OA\Parameter(
     *         name="locale",
     *         in="path",
     *         description="locale , lenguaje del EP",
     *         required=false,
     *         example="es",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id , id de registro de verificacion de identidad",
     *         required=true,
     *         example="2",
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="frontal_status_id",
     *         in="path",
     *         description="frontal_status_id , id del estatus imagen frontal",
     *         required=false,
     *         example="2",
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="back_status_id",
     *         in="path",
     *         description="back_status_id , id del estatus imagen trasero",
     *         required=false,
     *         example="3",
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="selfie_status_id",
     *         in="path",
     *         description="selfie_status_id , id del estatus imagen selfie",
     *         required=false,
     *         example="1",
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="address_status_id",
     *         in="path",
     *         description="address_status_id , id del estatus imagen documento de domicilio",
     *         required=false,
     *         example="2",
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Actualizar estatus de verificacion de identidad (Adm)"
     *     ),
     * )
     */
    public function updateIdentityVerificationImagesAdm(UpdateIdentityVerificationImagesAdmRequest $request, int $id)
    {

        try {
            DB::beginTransaction();

            $identityVerification = IdentityVerification::where('id', $id)->first();
            $identityVerification->status_frontal = $request->filled('frontal_status_id') ? $request->frontal_status_id : $identityVerification->status_frontal;
            $identityVerification->status_back = $request->filled('back_status_id') ? $request->back_status_id : $identityVerification->status_back;
            $identityVerification->status_selfie = $request->filled('selfie_status_id') ? $request->selfie_status_id : $identityVerification->status_selfie;
            $identityVerification->status_address = $request->filled('address_status_id') ? $request->address_status_id : $identityVerification->status_address;
            $identityVerification->updated_at = Carbon::now();
            $identityVerification->update();

            $changeStatus = IdentityVerification::where('id', $id)->first();

            $userDetail = UserDetail::where('id', $identityVerification->user_detail_id)->first();
            $user = User::where('id', $userDetail->user_id)->first();

            $setting_value = ((Setting::where('setting_name', 'file_address')->first()->setting_value) == 1) ? true : false;
            if ($setting_value) {
                if ($changeStatus->status_frontal == 2 && $changeStatus->status_back == 2 && $changeStatus->status_selfie == 2 && $changeStatus->status_address == 2) {
                    $changeStatus->status_general = 2;
                    $changeStatus->updated_at = Carbon::now();
                    $changeStatus->update();

                    $this->sendEmailApprovedDocument($user);

                } else if ($changeStatus->status_frontal == 3 || $changeStatus->status_back == 3 || $changeStatus->status_selfie == 3 || $changeStatus->status_address == 3) {
                    $changeStatus->status_general = 3;
                    $changeStatus->updated_at = Carbon::now();
                    $changeStatus->update();

                    $this->sendEmailRejectedDocument($user);
                }
            } else {
                if ($changeStatus->status_frontal == 2 && $changeStatus->status_back == 2 && $changeStatus->status_selfie == 2) {
                    $changeStatus->status_general = 2;
                    $changeStatus->updated_at = Carbon::now();
                    $changeStatus->update();

                    $this->sendEmailApprovedDocument($user);

                } else if ($changeStatus->status_frontal == 3 || $changeStatus->status_back == 3 || $changeStatus->status_selfie == 3) {
                    $changeStatus->status_general = 3;
                    $changeStatus->updated_at = Carbon::now();
                    $changeStatus->update();

                    $this->sendEmailRejectedDocument($user);

                }
            }


            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code' => $e->getCode(),
                    'title' => [__('messages.users.update.updateIdentityVerificationImagesAdm.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => [__('messages.users.update.updateIdentityVerificationImagesAdm.success')],
        ], 200);
    }


    /**
     * @OA\Post(
     *     path="/api/changePassword",
     *     tags={"Users"},
     *     summary="Cambiar contrasena",
     *     security={
     *      {"passport": {}},
     *     },
     *     description="Cambiar contrasena.",
     *     operationId="changePassword",
     *     @OA\Parameter(
     *         name="locale",
     *         in="path",
     *         description="locale , lenguaje del EP",
     *         required=false,
     *         example="es",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="current_password",
     *         in="path",
     *         description="current_password , contrasena actual del usuario",
     *         required=true,
     *         example="1235as2#",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="new_password",
     *         in="path",
     *         description="new_password , nueva contrasena",
     *         required=true,
     *         example="hhhAsd2",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="new_password_confirmation",
     *         in="path",
     *         description="new_password_confirmation , confirmacion de nueva contrasena",
     *         required=true,
     *         example="hhhAsd2",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="cambiar contrasena desde perfil de usuario"
     *     ),
     * )
     */
    public function changePasswordCognito(ChangePasswordCognitoRequest $request)
    {

        $response;

        try {

            $this->reset($request, 'email', 'password', 'new_password');
            // // $this->sendCodeChangePassword($user);


            // $response = $this->updatePassword($user,$newPassword,$currentPassword);

            // if($response == 200){
            //     $this->sendEmailPasswordUpdate($user);
            // }

        } catch (CognitoIdentityProviderException $e) {
            if ($e->getAwsErrorCode() === 'NotAuthorizedException') {
                return response()->json([
                    'errors' => [
                        'email'   => [__('messages.users.update.changePasswordCognito.not_authorized')],
                    ]
                ],400);
            }
            throw $e;
        }

        return response()->json([
            "message" => [__('messages.users.userSession.changePassword.success')],
        ]);
        // if($response == 200){
        //     return response()->json([
        //         'data' => [
        //             'code'   => 200,
        //             'title'  => [__('messages.users.userSession.changePassword.success_response')],
        //             'success'=> [__('messages.users.userSession.changePassword.success')]
        //         ]
        //     ], 200);
        // }else if ($response == 401){
        //     return response()->json([
        //         'data' => [
        //             'code'   => 401,
        //             'title'  => [__('messages.users.userSession.changePassword.error_response')],
        //             'errors'=> [__('messages.users.userSession.changePassword.error')]
        //         ]
        //     ], 401);
        // }

    }

    public function restore(RestoreRequest $request, int $id)
    {

        try {
            DB::beginTransaction();

            $user = User::with([
                'userDetail' => function ($q) {
                    $q->withTrashed();
                }
            ])->withTrashed()->where('id', $id)->first();

            if ($user && $user->deleted_at) {
                $user->restore();
                $user->userDetail->restore();
            } else if ($user && ($user->deleted_at == null)) {
                return response()->json([
                    "message" => "Este usuario se encuentra activo",
                ], 400);
            } else {
                return response()->json([
                    "message" => "No es pobible realizar la transaccion",
                ], 400);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code' => $e->getCode(),
                    'title' => [__('messages.users.update.restore.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => [__('messages.users.update.restore.success')],
        ]);
    }

    public function updateAdm(UpdateAdmRequest $request, $id)
    {

        try {
            DB::beginTransaction();


            $user = User::findOrFail($id);
            if ($user->hasRole('Admin')) {
                if ($request->email) {
                    request()->request->remove('email');
                }
            }
            // return $request;
            $request['code_phone'] = $request->phone_country_code;
            $updateCognito = $this->updateCognito($user, $request);

            if ($updateCognito <> 1 && $updateCognito <> 0) {
                if ($updateCognito === 'AliasExistsException') {
                    return response()->json([
                        'errors' => [
                            'email' => ['El correo electrónico ya ha sido registrado.'],
                        ]
                    ]);
                } //End if
                return $updateCognito;
            }
            $this->updateUserAdm($user, $request);

            $userDetail = UserDetail::where('user_id','=',$id)->first();
            $this->updateUserDetailAdm($userDetail, $request);

            if ($request->role_id <> null) {
                if ($user->roles[0]['id'] <> $request->role_id) {
                    if ($user->roles[0]['id'] <> 1) {
                        $user->removeRole($user->roles[0]['id']);
                        $user->assignRole($request->role_id);
                    } else {
                        return response()->json([
                            "errors" => [
                                "message" => ["El usuario Administrador NO debe cambiar de rol"],
                            ]
                        ], 401);
                    }
                }
            }

            $response = User::where('id', $id)
                ->with(
                    'userDetail',
                    'roles'
                )
                ->first();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code' => $e->getCode(),
                    'title' => [__('messages.users.update.update.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => [__('messages.users.update.update.success')],
            "response" => UserResource::make($response),
        ]);
    }

    protected function sendCodeChangePassword($user)
    {
        $correo = new SendCodeChangePasswordMailable($user);
        Mail::to($user->email)->send($correo);
    }

    protected function updatePassword($user, $newPassword, $currentPassword)
    {

        if (Hash::check($currentPassword, $user->password)) {

            $user->password = $newPassword ? bcrypt($newPassword) : $user->password;
            $user->updated_at = Carbon::now();
            $user->update();
            return $response = 200;
        } else {
            return $response = 401;
        }
    }

    protected function updateConfirmationCodePhone($userDetail)
    {
        $userDetail->phone_verified_at = Carbon::now();
        $userDetail->confirmation_code_phone = null;
        $userDetail->update();
    }

    protected function sendSms($id)
    {
        $message = [__('messages.users.sendSms.sendSms')][0];

        $user = UserDetail::where('user_id', $id)->first();
        do {
            $token_acount = $this->generateRandomCode();
        } while ($this->verifyCode($token_acount) <> 1);

        $user->update([
            'confirmation_code_phone' => $token_acount,
        ]);

        $accountSid = config('services.twilio')['TWILIO_ACCOUNT_SID'];
        $authToken = config('services.twilio')['TWILIO_AUTH_TOKEN'];
        $appSid = config('services.twilio')['TWILIO_APP_SID'];
        $client = new Client($accountSid, $authToken);

        // Use the client to do fun stuff like send text messages!
        $client->messages->create(
        // the number you'd like to send the message to
            "+" . $user->code_phone . $user->phone,
            array(
                // A Twilio phone number you purchased at twilio.com/console
                'from' => $appSid,
                // the body of the text message you'd like to send
                'body' => $message . ' ' . $token_acount
            )
        );
    }

    protected function generateRandomCode()
    {
        $caracteres_permitidos = '123456789';
        $longitud = 6;
        return $code = substr(str_shuffle($caracteres_permitidos), 0, $longitud);
    }

    protected function verifyCode($code)
    {
        $verifyCode = UserDetail::where('confirmation_code_phone', $code)->exists();
        if ($verifyCode) {
            return 2;
        } else {
            return 1;
        }
    }

    protected function updateUserAdm($user, $request)
    {
        $user->name       = $request->filled('name')         ? $request->name                 : $user->name;
        $user->lastname   = $request->filled('lastname')     ? $request->lastname             : $user->lastname;
        $user->email      = $request->filled('email')        ? $request->email                : $user->email;
        $user->password   = $request->filled('new_password') ? bcrypt($request->new_password) : $user->password;
        $user->updated_at = Carbon::now();
        $user->update();
    }

    protected function updateUserDetailAdm($userDetail, $request)
    {
        $userDetail->country_id                  = $request->filled('country_id')                  ? $request->country_id                  : $userDetail->country_id;
        $userDetail->profile_background_image_id = $request->filled('profile_background_image_id') ? $request->profile_background_image_id : $userDetail->profile_background_image_id;
        $userDetail->tp_document_id              = $request->filled('tp_document_id')              ? $request->tp_document_id              : $userDetail->tp_document_id;
        $userDetail->gender_id                   = $request->filled('gender_id')                   ? $request->gender_id                   : $userDetail->gender_id;
        $userDetail->document                    = $request->filled('document')                    ? $request->document                    : $userDetail->document;
        $userDetail->address                     = $request->filled('address')                     ? $request->address                     : $userDetail->address;
        $userDetail->birth                       = $request->filled('birth')                       ? $request->birth                       : $userDetail->birth;
        $userDetail->code_phone                  = $request->filled('code_phone')          ? $request->code_phone          : $userDetail->code_phone;
        if ($request->phone) {
            $userDetail->phone_verified_at       = ($request->phone == $userDetail->phone) ? $userDetail->phone_verified_at       : null;
            $userDetail->confirmation_code_phone = ($request->phone == $userDetail->phone) ? $userDetail->confirmation_code_phone : null;
        }
        $userDetail->phone      = $request->filled('phone') ? $request->phone : $userDetail->phone;
        $userDetail->updated_at = Carbon::now();
        $userDetail->update();
    }


    protected function mailNewPhoneAdded($userDetail, $user)
    {
        $email = new Mail();
        $email->setFrom(config('mail.from.address'), config('mail.from.name'));

        $email->addTo(
            $user->email,
            $user->name,
            [
                "subject" => "Nuevo teléfono agregado",
                "phone" => $userDetail->phone,
                // "body"    => "aqui escribe cualquier cosa",
            ],
            0
        );

        $email->setTemplateId("d-e432d9a8164d473b95338165faa177bf");
        $sendgrid = new \SendGrid(config('mail.sendgrid.sendgrid_api_key'));

        try {
            $response = $sendgrid->send($email);
        } catch (\Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }
        // $correo = new SendCodeEmailVerifiedMailable($user);
        // Mail::to($user->email)->send($correo);

    }


    protected function sendPhoneVerified($userDetail, $user)
    {
        $email = new Mail();
        $email->setFrom(config('mail.from.address'), config('mail.from.name'));

        $email->addTo(
            $user->email,
            $user->name,
            [
                "subject" => "Teléfono verificado",
                "phone" => $userDetail->phone,
                // "body"    => "aqui escribe cualquier cosa",
            ],
            0
        );

        $email->setTemplateId("d-7931eeac7e7f4a4c9b96e28e3feafe3d");
        $sendgrid = new \SendGrid(config('mail.sendgrid.sendgrid_api_key'));

        try {
            $response = $sendgrid->send($email);
        } catch (\Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }

    }


    protected function sendEmailApprovedDocument($user)
    {
        $email = new Mail();
        $email->setFrom(config('mail.from.address'), config('mail.from.name'));

        $email->addTo(
            $user->email,
            $user->name,
            [
                "subject" => "Documento de identidad han sido aprobado",
                "status" => "Aprobado",
                // "body"  => "aqui escribe cualquier cosa",
            ],
            0
        );

        $email->setTemplateId("d-49a6092ed13d482296f31056d9bfa5e7");
        $sendgrid = new \SendGrid(config('mail.sendgrid.sendgrid_api_key'));

        try {
            $response = $sendgrid->send($email);
        } catch (\Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }

    }

    protected function sendEmailRejectedDocument($user)
    {
        $email = new Mail();
        $email->setFrom(config('mail.from.address'), config('mail.from.name'));

        $email->addTo(
            $user->email,
            $user->name,
            [
                "subject" => "Documento de identidad han sido rechazado",
                "status" => "Rechazado",
                // "body"  => "aqui escribe cualquier cosa",
            ],
            0
        );

        $email->setTemplateId("d-86824247ead04b3cadfb0fdcf7665c1e");
        $sendgrid = new \SendGrid(config('mail.sendgrid.sendgrid_api_key'));

        try {
            $response = $sendgrid->send($email);
        } catch (\Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }

    }

    protected function sendEmailChanged($user, $currentEmail)
    {
        $email = new Mail();
        $email->setFrom(config('mail.from.address'), config('mail.from.name'));

        $email->addTo(
            $currentEmail,
            $user->name,
            [
                "subject" => "Cambio de correo electrónico",
                "email" => $user->email,
                // "body"  => "aqui escribe cualquier cosa",
            ],
            0
        );

        $email->setTemplateId("d-3fdefd412aa1445b87590820ca97eefd");
        $sendgrid = new \SendGrid(config('mail.sendgrid.sendgrid_api_key'));

        try {
            $response = $sendgrid->send($email);
        } catch (\Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }

    }

    protected function mailNewPhoneAddedUpdate($userDetail, $user)
    {
        $email = new Mail();
        $email->setFrom(config('mail.from.address'), config('mail.from.name'));

        $email->addTo(
            $user->email,
            $user->name,
            [
                "subject" => "Actualizado numero de teléfono",
                "phone" => $userDetail->phone,
                // "body"    => "aqui escribe cualquier cosa",
            ],
            0
        );

        $email->setTemplateId("d-e296051632df415b8503aebd1f2a83ec");
        $sendgrid = new \SendGrid(config('mail.sendgrid.sendgrid_api_key'));

        try {
            $response = $sendgrid->send($email);
        } catch (\Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }
        // $correo = new SendCodeEmailVerifiedMailable($user);
        // Mail::to($user->email)->send($correo);

    }


    protected function sendEmailPasswordUpdate($user)
    {
        $email = new Mail();
        $email->setFrom(config('mail.from.address'), config('mail.from.name'));

        $email->addTo(
            $user->email,
            $user->name,
            [
                "subject" => "Actualizada la contraseña exitosamente!",
                // "body"    => "aqui escribe cualquier cosa",
            ],
            0
        );

        $email->setTemplateId("d-fb8fcea65b4d48a1982107263f30b968");
        $sendgrid = new \SendGrid(config('mail.sendgrid.sendgrid_api_key'));

        try {
            $response = $sendgrid->send($email);
        } catch (\Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }

    }

    protected function updateCognito($user, $request)
    {
        try {
            $array = [];

            if ($request->filled('name') && ($request->name <> $user->name)) {
                $array = array_merge($array, ['given_name' => $request->name]);
            }

            if ($request->filled('lastname') && ($request->lastname <> $user->lastname)) {
                $array = array_merge($array, ['family_name' => $request->lastname]);
            }

            if ($request->filled('email') && ($request->email <> $user->email)) {
                $array = array_merge($array, ['email' => $request->email]);
                $array = array_merge($array, ['email_verified' => 'true']);
            }

            if (!empty($array)) {
               /*
                //No funcional se cambiaron los ENDPOINT

                $client = app('cognito');

                $client->AdminUpdateUserAttributes([
                    'Username' => $user->email,
                    'UserPoolId' => config('cognito.user_pool_id'),
                    'UserAttributes' => $this->formatAttributes($array),
                ]);
                */
                return 1;

            } else {

                return 0;
            }

        } catch (CognitoIdentityProviderException $e) {

            if ($e->getAwsErrorCode() === 'InvalidParameterException') {
                return 'InvalidParameterException';
            } //End if

            if ($e->getAwsErrorCode() === 'AliasExistsException') {
                return 'AliasExistsException';
            } //End if

            throw $e;
        } //Try-catch ends

    }

    protected function formatAttributes(array $attributes)
    {
        $userAttributes = [];

        foreach ($attributes as $key => $value) {
            $userAttributes[] = [
                'Name' => $key,
                'Value' => $value,
            ];
        } //Loop ends

        return $userAttributes;
    } //Function ends

    public function sendCodeUpdateEmail(Request $request)
    {
        $user = User::where('email', '=', $request->email)->first();

        if (is_null($user)) {

            $existCode = Code::where('user_id', '=', $request->user_id)->first();

            if ( $existCode ) {
                $existCode->delete();
            }

            $code = new Code();
            $code->user_id = $request->user_id;
            $code->code = $this->generateRandomCode();
            $code->save();

            $email = new Mail();
            $email->setFrom(config('mail.from.address'), config('mail.from.name'));

            $email->addTo(
                $request->email,
                "nombre de prueba",
                [
                    "subject" => "Actualizacion de Correo",
                    "code" => $code->code,
                ],
                0
            );

            $email->setTemplateId("d-2b57186e5a8a41a4b5d2041afe7c2835");
            $sendgrid = new \SendGrid(config('mail.sendgrid.sendgrid_api_key'));

            try {
                $response = $sendgrid->send($email);
            } catch (\Exception $e) {
                echo 'Caught exception: ' . $e->getMessage() . "\n";
            }

            return response()->json([
                "message" => [__('Codigo enviado con exito.!')],
            ]);
        }
        else {
            return response()->json([
                "message" => [__('Este correo es el mismo que ya tienes registrado.!')],
            ]);
        }
    }

    /*
     Funicion que permite llamar desde el test las funciones de envio de correo
    */
    public function test_email(Request $request){
        if (isLocalOrTesting()) {
            $user = \App\Services\AuthCognito::userTesting($request->header('Authorization'));
        } else {
            $user = \App\Services\AuthCognito::user();
        }
        $userDetail = UserDetail::where('user_id', $user->id)->first();
        switch ($request->value) {
            case 6:
                return custom_response_sucessfull($this->mailNewPhoneAddedUpdate($userDetail, $user));
            case 7:
                return custom_response_sucessfull($this->sendPhoneVerified($userDetail, $user));
            case 9:
                return custom_response_sucessfull($this->sendEmailApprovedDocument($user));
            case 10:
                return custom_response_sucessfull($this->sendEmailRejectedDocument($user));
            case 11:
                return custom_response_sucessfull($this->sendEmailChanged($user, $user->email));
            case 12:
                return custom_response_sucessfull($this->mailNewPhoneAdded($userDetail, $user));
            case 13:
                return custom_response_sucessfull($this->sendEmailPasswordUpdate($user));

            default:
                # code...
                break;
        }
    }
    /**
     * Controlador que permite aceptar o rechazar los KYC registrados por lote
     * @package users
     * @param IdinteficationAllRequest $request
     * @return Response
     * @author foskert@gmail.com
     * @version 1.0
     * @method POST
     */
    public function updateIdentityVerificationAll(IdinteficationAllRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = User::find($request->user_id);
            $userDetail = UserDetail::where('user_id', $request->user_id)->first();
            $identityVerifications = IdentityVerification::where('user_detail_id', $userDetail->id)->get();
            if(count($identityVerifications) == 0){
                return response()->json([
                    "message" => [__('messages.users.update.updateIdentityVerificationImagesAll.empty')],
                ], 404);
            }
            foreach($identityVerifications AS $identityVerification){
                $identityVerification->status_frontal  = $request->request_status_id;
                $identityVerification->status_back     = $request->request_status_id;
                $identityVerification->status_selfie   = $request->request_status_id;
                $identityVerification->status_address  = $request->request_status_id;
                $identityVerification->status_general  = $request->request_status_id;
                $identityVerification->update();
            }
            if($request->request_status_id == 2){
                $this->sendEmailApprovedDocument($user);
                $updateClient=ModelsClient::where('email',$user->email)->first();
                if($updateClient){
                     $updateClient->status_id=27;
                     $updateClient->save();
                }
            }else{
                $this->sendEmailRejectedDocument($user);
                $updateClient=ModelsClient::where('email',$user->email)->first();
                if($updateClient){
                     $updateClient->status_id=18;
                     $updateClient->save();
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code' => $e->getCode(),
                    'title' => [__('messages.users.update.updateIdentityVerificationImagesAll.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => [__('messages.users.update.updateIdentityVerificationImagesAll.success')],
        ], 200);
    }


    public function cardData($id,Request $request)
    {
        $userDetail = UserDetail::where('user_id', $id)->first();
        if (!$userDetail) {
            return response()->json([
                "errors" => [
                    "message"       => "No es posible editar este usuario",
                ]
            ], 422);
        }
        try {
            DB::beginTransaction();

            $card = UserDetail::where('user_id', $id)->first();
            $card->name_card=$request->name_card  ? $request->name_card  :  $card->name_card;
            $card->code_phone_card=$request->code_phone_card ? $request->code_phone_card  :  $card->code_phone_card;
            $card->phone_card=$request->phone_card  ? $request->phone_card  :  $card->phone_card;
            $card->email_card=$request->email_card ? $request->email_card :  $card->email_card;
            $card->updated_at  = Carbon::now();
            $card->save();

            $response = UserDetail::where('user_id', $id)->first();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.card.update.update.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "usuario actualizado",
            "response"      => UserDetailResource::make($response),
        ]);
    }


}
