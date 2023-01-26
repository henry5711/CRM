<?php

namespace App\Http\Controllers\Api\Users;

use App\Enums\TypeDocumentEnum;
use App\Exceptions\CognitoApiException;
use App\Exceptions\EbangoApiException;
use App\Http\Controllers\Api\Users\UsersRepositories\UserRepository;
use Carbon\Carbon;
use App\Models\Code;
use App\Models\User;
use App\Models\Image;
use MongoDB\Driver\Exception\ExecutionTimeoutException;
use Prophecy\Exception\Exception;
use \SendGrid\Mail\Mail;
use App\Models\UserDetail;
use App\Models\IdentityVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Services\CognitoService;
use App\Http\Services\EbangoService;
use App\Http\Requests\Users\RegisterAdmRequest;
use App\Http\Requests\Users\RegisterIdentifyVerificationRequest;
use App\Http\Requests\Users\RegisterRequest;
use App\Http\Resources\UserResource;
// use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Mail\SendCodeEmailVerifiedMailable;
use App\Models\Setting;
use App\Services\AuthCognito;
use App\Services\KycApi;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;
use Ellaisys\Cognito\Auth\RegistersUsers;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\RequiredIf;
use App\Exceptions\KycApiException;
use App\Models\broker_country;
use App\Http\Controllers\Api\Users\UserController;
use App\Models\Client;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    //Cognito Ellaisys
    use RegistersUsers;

    protected $registerCognito;
    protected $ebango;
    protected $userRepository;
    public function __construct(CognitoService $registerCognito, EbangoService $ebango)
    {
        $this->registerCognito = $registerCognito;
        $this->ebango          = $ebango;
        $this->userRepository  = new UserRepository(new User);
    }
    //Register cognito TEST
    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();
        try {

            // $ebangoRegister    = $this->ebango->registerUser($request);
            $cognitoRegistered = $this->registerCognito->registerUser($request);
            $request['cognitoID'] =$cognitoRegistered['message']['UserSub'];
            //$ebangoRegister    = $this->ebango->registerUserNew($request);


            $user = User::create([
                'name'       => $request->name,
                'lastname'   => $request->lastname,
                'email'      => $request->email,
                'cognitoId'  => $cognitoRegistered['message']['UserSub'],
                // 'ebangoId'   => $ebangoRegister['message']['data']['id'],
            ])->assignRole('Cliente');

            $data_register = $request->only([
                'country_id',
                'phone',
                'code_phone',
            ]);
            if($request->phone_country_code){
                $data_register['code_phone'] = $request->phone_country_code;
            }

            $data_register['broker_id'] = broker_country::where('country_id',$request->nat)->value('id');
            $data_register['ip'] = $request->ip();
            $data_register['birth'] = '2000-01-01'; //POR CAMBIAR OJO!!!!!!!!!!!!!!!!!!!!!!!!!!

            $user->userDetail()->create($data_register);

            $updateClient=Client::where('email',$request->email)->first();
            if($updateClient){
                 $updateClient->status_id=16;
                 $updateClient->save();
            }

            DB::commit();
        } /*catch (EbangoApiException $e) {
            return custom_response_exception($e, 'Ebango Error', $e->getCode());
        }*/ catch (CognitoApiException $e) {
            return custom_response_exception($e, 'Cognito Error');
        } catch (\Exception $e) {
            DB::rollBack();
            $message = [__('messages.users.register.register.internal_error')];
            return custom_response_exception($e, $message, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $message = [__('messages.users.register.register.success')];
        return custom_response_sucessfull($message);
    }

    /**
     * @OA\Post(
     *     path="/api/users/register/registerIdentityVerification",
     *     tags={"Users"},
     *     summary="Registro de verificacion de identidad",
     *     security={
     *      {"passport": {}},
     *     },
     *     description="Registro de verificacion de identidad .",
     *     operationId="registerIdentityVerification",
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
     *         name="country_id",
     *         in="path",
     *         description="country_id , id de pais",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="tp_document_id",
     *         in="path",
     *         description="tp_document_id , Tipo de documento de identidad",
     *         required=true,
     *         example="2",
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="document",
     *         in="path",
     *         description="document , numero de documento de identidad",
     *         required=false,
     *         example="123123456a",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="city",
     *         in="path",
     *         description="city , nombre de la ciudad del usuario",
     *         required=true,
     *         example="Caracas",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="address",
     *         in="path",
     *         description="address , direccion del usuario",
     *         required=true,
     *         example="Av. francisco fajardo 2da pasarlela frente a sevicio postal",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="file_frontal",
     *         in="path",
     *         description="file_frontal , archivo de imagen frontal",
     *         required=true,
     *         example="image.jpg",
     *         @OA\Schema(
     *             type="file",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="file_back",
     *         in="path",
     *         description="file_back , archivo de imagen trasera",
     *         required=true,
     *         example="imagetrasera.png",
     *         @OA\Schema(
     *             type="file",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="file_selfie",
     *         in="path",
     *         description="file_selfie , archivo de imagen selfie",
     *         required=true,
     *         example="mifoto.png",
     *         @OA\Schema(
     *             type="file",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="file_address",
     *         in="path",
     *         description="file_address , archivo de la imagen de documento de domicilio",
     *         required=true,
     *         example="reciboluz.jpg",
     *         @OA\Schema(
     *             type="file",
     *         ),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Registro de verificacion de identidad."
     *     ),
     * )
     */
    public function registerIdentityVerification(RegisterIdentifyVerificationRequest $request)
    {
        if (isLocalOrTesting()) {
            $user       = \App\Services\AuthCognito::userTesting($request->header('Authorization'));
        } else {
            $user       = \App\Services\AuthCognito::user();
        }


        if ($this->userRepository->VerifiedIdentity($user->id)) {
            return response()->json([
                "error" => [
                    "message"  => [__('messages.users.register.registerIdentityVerification.verify_identity')],
                ]
            ], 401);
        }


        try {
            DB::beginTransaction();
            $data = [
                'country_id'     => $request->country_id,
                'tp_document_id' => $request->document_type_id,
                'document'       => $request->document_number,
                'address'        => $request->direction,
            ];
            if($data['address'] == '' || $data['address'] == null){
                unset($data['address']);
            }
            $user->userDetail()->update($data);


            $user->refresh();

            $name_inputs_images = [
                'front_identity_document'   => 'front',
                'reverse_identity_document' => 'back',
                'selfie_identity_document'  => 'selfie',
            ];

            $img_paths = [];
            $userDetail = UserDetail::where('user_id', $user->id)->first();
            foreach($name_inputs_images as $img => $min){
                $input_img = $request->$img;
                if($input_img != '' && $input_img != null){
                    $img_paths[$img] = self::saveImageV2($input_img,$userDetail,$min);
                }
            }

            //------------
            $type_document = $user->userDetail->tpDocument->tp_document_name;
            $KYC_type_document = TypeDocumentEnum::getTypeDocumentKYC($type_document);

            $data = [
                'city'          => $request->city,
                'email'         => $user->email,
                'last_name'     => $user->lastname,
                'first_name'    => $user->name,
                'address'       => $user->userDetail->address,
                'country'       => $user->userDetail->country->name,
                'nationality'   => $user->userDetail->country->name,
                'document_type' => $KYC_type_document,
                'selfie_url'    => $img_paths['selfie_identity_document'],
                'document_url'  => $img_paths['front_identity_document'],
            ];
            $kyc_api = new KycApi;
            $response_kyc = $kyc_api->registerIdentityVerification($data);

            if($response_kyc['statusCode'] != 200){
                if($request->attempt_number < 3){
                    throw new KycApiException($response_kyc['message'],$response_kyc['statusCode']);
                }else
                if($request->attempt_number >= 3){
                    if(
                        isset($user->userDetail->identityVerification) &&
                        !empty($user->userDetail->identityVerification)
                    ){
                        $user->userDetail->identityVerification->update([
                            'status_frontal' => 1,
                            'status_back'    => 1,
                            'status_selfie'  => 1,
                            'status_address' => 1,
                            'status_general' => 1,
                        ]);
                    }else{
                        $user->userDetail->identityVerification()->create([
                            'status_frontal' => 1,
                            'status_back'    => 1,
                            'status_selfie'  => 1,
                            'status_address' => 1,
                            'status_general' => 1,
                        ]);
                    }
                    $this->sendEmailRegisteredIdentityDocuments($user);
                    DB::commit();
                    $message = [__('messages.users.register.registerIdentityVerification.success')];
                    return response()->json(
                        [
                            'success'          => true,
                            'external_service' => $response_kyc['statusCode'],
                            'message'          => $message
                        ],
                        Response::HTTP_CREATED
                    );
                }
            }

            if(
                isset($user->userDetail->identityVerification) &&
                !empty($user->userDetail->identityVerification)
            ){
                $user->userDetail->identityVerification->update([
                    'status_frontal' => 2,
                    'status_back'    => 2,
                    'status_selfie'  => 2,
                    'status_address' => 2,
                    'status_general' => 2,
                ]);
            }else{
                $user->userDetail->identityVerification()->create([
                    'status_frontal' => 2,
                    'status_back'    => 2,
                    'status_selfie'  => 2,
                    'status_address' => 2,
                    'status_general' => 2,
                ]);
            }

            $this->sendEmailRegisteredIdentityDocuments($user);
            $updateClient=Client::where('email',$user->email)->first();
            if($updateClient){
                 $updateClient->status_id=17;
                 $updateClient->save();
            }
            DB::commit();

            //------------
        }catch(KycApiException $e){
            DB::rollBack();
            return custom_response_exception($e,'Kyc-error');
        } catch (\Exception $e) {
            DB::rollBack();
            return custom_response_exception($e,[__('messages.users.register.registerIdentityVerification.internal_error')]);
        }
        return custom_response_sucessfull([__('messages.users.register.registerIdentityVerification.success')]);
    }

    public function registerAdm(RegisterAdmRequest $request)
    {
        DB::beginTransaction();
        try {

            $cognitoRegistered = $this->registerCognito->registerUser($request);

            $user = User::create([
                'name' => $request->name,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'cognitoId' => $cognitoRegistered['message']['UserSub'],
            ])->assignRole($request->role_id);

            $data_register = $request->only([
                'country_id',
                'phone'
            ]);
            $data_register['broker_id'] =broker_country::where('country_id',$request->nat)->value('id');
            $data_register['ip'] = $request->ip();
            $data_register['code_phone'] = $request->phone_country_code;
            $user->userDetail()->create($data_register);

            $updateClient=Client::where('email',$request->email)->first();
            if($updateClient){
                 $updateClient->status_id=16;
                 $updateClient->save();
            }

            DB::commit();
        } catch (CognitoApiException $e) {
            return custom_response_exception($e, 'Cognito Error');
        } catch (\Exception $e) {
            DB::rollBack();
            $message = [__('messages.users.register.register.internal_error')];
            return custom_response_exception($e, $message, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $message = [__('messages.users.register.register.success')];
        return custom_response_sucessfull($message);
    }

    /**
     * Cambiando función de guardado de imágenes
     *
     * @param [type] $img
     * @param UserDetail $userDetail
     * @return void
     */
    private function saveImageV2($img,UserDetail $userDetail, $pre){
        if($img == '' || $img == null){
            return null;
        }

        $caracteres_permitidos = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $longitud = 40;
        $code = substr(str_shuffle($caracteres_permitidos), 0, $longitud);
        $path = $img->storeAs('img/ewex-car/identity-verification', $pre.'-' . $code . '.jpg', 's3');
        $fullPath = Storage::disk('s3')->url($path);

        $userDetail->images()->create([
            'filename' => basename($path),
            'url' => $fullPath
        ]);
        $url_temp = Storage::disk('s3')->temporaryUrl(
            $path, now()->addMinutes(10)
        );





        return $url_temp;

    }


    protected function generateRandomCode()
    {
        $caracteres_permitidos = '123456789';
        $longitud = 6;
        return $code = substr(str_shuffle($caracteres_permitidos), 0, $longitud);
    }

    protected function verifyCode($code)
    {
        $verifyCode = Code::where('code', $code)->exists();
        if ($verifyCode) {
            return 2;
        } else {
            return 1;
        }
    }

    protected function sendCodeEmailVerified($user)
    {
        $email = new Mail();
        $email->setFrom(config('mail.from.address'), config('mail.from.name'));

        $email->addTo(
            $user->email,
            $user->name,
            [
                "subject" => "Validación de correo electrónico",
                "name" => $user->name,
                "code" => $user->code,
                // "body"    => "aqui escribe cualquier cosa",
            ],
            0
        );

        $email->setTemplateId("d-2b57186e5a8a41a4b5d2041afe7c2835");
        $sendgrid = new \SendGrid(config('mail.sendgrid.sendgrid_api_key'));

        try {
            $response = $sendgrid->send($email);
            return $response;
        } catch (\Exception $e) {
            log::critical('Caught exception: ' . $e->getMessage() );
        }
        // Antigua forma de envio
        // $correo = new SendCodeEmailVerifiedMailable($user);
        // Mail::to($user->email)->send($correo);

    }

    protected function welcome($user)
    {
        $email = new Mail();
        $email->setFrom(config('mail.from.address'), config('mail.from.name'));

        $email->addTo(
            $user->email,
            $user->name,
            [
                "subject" => "Bienvenido a EwexCar!",
                // "email"    => $user->email,
                // "body"    => "aqui escribe cualquier cosa",
            ],
            0
        );

        $email->setTemplateId("d-c33cc3c8eaad49b9864b12902cab301b");
        $sendgrid = new \SendGrid(config('mail.sendgrid.sendgrid_api_key'));

        try {
            $response = $sendgrid->send($email);
        } catch (\Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }
        // $correo = new SendCodeEmailVerifiedMailable($user);
        // Mail::to($user->email)->send($correo);

    }


    protected function sendEmailRegisteredIdentityDocuments($user)
    {
        $email = new Mail();
        $email->setFrom(config('mail.from.address'), config('mail.from.name'));

        $email->addTo(
            $user->email,
            $user->name,
            [
                "subject" => "Envío de documento de identidad",
                "status" => "Verificación",
                // "body"    => "aqui escribe cualquier cosa",
            ],
            0
        );

        $email->setTemplateId("d-15e5e105a8c641c3b29a2770fc290304");
        $sendgrid = new \SendGrid(config('mail.sendgrid.sendgrid_api_key'));

        try {
            $response = $sendgrid->send($email);
        } catch (\Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }
    }

    // protected function createUserCognito($data)
    // {

    //     $client = app('cognito');
    //     $name = $data['name'];
    //     $given_name = $data['name'];
    //     $family_name = $data['name'];
    //     $website = $data['name'];
    //     $email = $data['email'];
    //     $password = $data['password'];

    //     $result = $client->signUp([
    //         'ClientId' => config('cognito.app_client_id'),
    //         'Username' => $email,
    //         'Password' => $password,
    //         'UserAttributes' => [
    //             [
    //                 'Name' => 'name',
    //                 'Value' => $name
    //             ],
    //             [
    //                 'Name' => 'given_name',
    //                 'Value' => $given_name
    //             ],
    //             [
    //                 'Name' => 'family_name',
    //                 'Value' => $family_name
    //             ],
    //             [
    //                 'Name' => 'website',
    //                 'Value' => config('app.url')
    //             ],
    //             [
    //                 'Name' => 'email',
    //                 'Value' => $email,
    //             ]
    //         ],
    //     ]);
    //     return $result;
    // }

    /*
     Funicion que permite llamar desde el test las funciones de envio de correo
    */
    public function test_email(Request $request){
        if (isLocalOrTesting()) {
            $user = \App\Services\AuthCognito::userTesting($request->header('Authorization'));
        } else {
            $user = \App\Services\AuthCognito::user();
        }
        //dd($user);
        switch ($request->value) {
            case 1:
                return custom_response_sucessfull($this->sendCodeEmailVerified($user));
                break;
            case 5:
                return custom_response_sucessfull($this->welcome($user));
                break;
            case 8:
                return custom_response_sucessfull($this->sendEmailRegisteredIdentityDocuments($user));
                break;
            default:
                # code...
                break;
        }
    }

}
