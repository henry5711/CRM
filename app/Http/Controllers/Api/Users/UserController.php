<?php

namespace App\Http\Controllers\Api\Users;

use App\Exceptions\CognitoApiException;
use App\Exceptions\EbangoApiException;
use Carbon\Carbon;
use App\Models\Code;
use App\Models\User;
use \SendGrid\Mail\From;
use \SendGrid\Mail\To;
use \SendGrid\Mail\Subject;
use \SendGrid\Mail\PlainTextContent;
use \SendGrid\Mail\HtmlContent;
use \SendGrid\Mail\Mail;
use \SendGrid\Mail\Substitution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\ChangePasswordRequest;
use App\Http\Requests\Users\LoginRequest;
use App\Http\Requests\Users\SendCodeResetPasswordRequest;
use App\Http\Requests\Users\SendCodeVerifyEmailRequest;
use App\Http\Requests\Users\VerifyCodeAndChangePasswordRequest;
use App\Http\Requests\Users\VerifyCodeEmailRequest;
use App\Http\Resources\UserResource;
use App\Http\Services\CognitoService;
use App\Http\Services\EbangoService;
// use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Mail\SendCodeEmailVerifiedMailable;
use App\Mail\SendCodeResetPasswordMailable;
use App\Models\EbangoToken;
use App\Services\CognitoApi;
use App\Traits\Cognito\AuthenticatesUsers;
use App\Traits\Cognito\ResetsPasswords;
use App\Traits\Cognito\VerifiesEmails;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;
use Ellaisys\Cognito\Auth\AuthenticatesUsers as CognitoAuthenticatesUsers;

use Ellaisys\Cognito\Auth\SendsPasswordResetEmails;
use Ellaisys\Cognito\AwsCognitoClaim;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail as FacadesMail;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Constant representing the user not found exception.
     *
     * @var string
     */
    const USER_NOT_FOUND = 'UserNotFoundException';

    /**
     * Constant representing the code mismatch exception.
     *
     * @var string
     */
    const CODE_MISMATCH = 'CodeMismatchException';


    /**
     * Constant representing the expired code exception.
     *
     * @var string
     */
    const EXPIRED_CODE = 'ExpiredCodeException';

    protected $ebango;
    protected $cognito;

    //Cognito traits
    use VerifiesEmails;
    use AuthenticatesUsers;
    use SendsPasswordResetEmails;
    use ResetsPasswords;

    public function __construct(CognitoService $cognito, EbangoService $ebango)
    {
        $this->cognito = $cognito;
        $this->ebango = $ebango;
    }


    public function login(LoginRequest $request)
    {
        DB::beginTransaction();
        try {
            $responseLoginCognito = $this->cognito->login($request->email, $request->password, $request->ip());

            $user = User::firstWhere('email', $request->email);


            $responseLoginCognito['NewDeviceMetadata'] = $this->setNewDeviceMetadata($responseLoginCognito);

            if (count($user->cognitoTokens) > 0) {
                foreach ($user->cognitoTokens as $token) {
                    $token->revoked = true;
                    $token->save();
                }
            }

            //$user->cognitoTokens()->create($responseLoginCognito['AuthenticationResult']);

            $user->cognitoTokens()->create($responseLoginCognito);
            $user->refresh();
            $tokens = $user->cognitoTokens->where('revoked', false)->first();
            $user->load([
                'roles.permissions',
                'userDetail.country',
                'userDetail.identityVerification',
                'userDetail.broker'
            ]);

            if ($user->ebango_register == false) {
                $request->merge([
                    'password_confirmation' => $request->password,
                    'name' => $user->name,
                    'lastname' => $user->lastname,
                    'code_phone' => $user->userDetail->code_phone,
                    'phone' => $user->userDetail->phone,
                    'country_id' => $user->userDetail->country->id,

                ]);
                $token = $tokens->IdToken;
                $ebangoRegister    = $this->ebango->registerUserNew($request, $token);
                $user->update(['ebango_register' => true]);
            }
            DB::commit();
        } catch (CognitoApiException $e) {
            return custom_response_exception($e, 'Cognito Error');
        } catch (\Exception $e) {
            DB::rollBack();
            return custom_response_exception($e, __('messages.users.register.register.internal_error'));
        }
        return response()->json([
            "message" => [__('messages.users.user.login.success')],
            "response" => UserResource::make($user),
            "token" => $tokens->IdToken,
        ], 200);
    }

    public function setNewDeviceMetadata($responseLoginCognito)
    {
        if (!array_key_exists('NewDeviceMetadata', $responseLoginCognito)) {
            $json = [
                "DeviceKey" => null,
                "DeviceGroupKey" => null
            ];
            $responseLoginCognito['NewDeviceMetadata'] = $json;
        }
        return  json_encode($responseLoginCognito['NewDeviceMetadata']);
    }
    public function logout()
    {
        // auth()->user()->tokens->each(function ($token, $key) {
        //     $token->delete();
        // });
        Auth::logout();
        Redis::del('token_ebango');

        return response()->json([
            "message" => [__('messages.users.user.logout.success')]
        ], 200);
    }


    //Verificar codigo de email por cognito
    public function verifyCodeEmail(VerifyCodeEmailRequest $request)
    {

        try {
            DB::beginTransaction();
            $email = $request->email;
            $code = $request->code;

            //$responseEbango = $this->ebango->verifyEmail($email,$code);
            $requestCognito = $this->cognito->verifyEmail($email, $code);


            $user = User::where('email', $request->email)->first();
            $user->email_verified_at = Carbon::now();
            $user->update();
            $user->refresh();

            //$this->emailVerified($user); // envio de emil

            DB::commit();
        } catch (EbangoApiException $e) {
            return custom_response_exception($e, 'Ebango Error', $e->getCode());
        } catch (CognitoApiException $e) {
            return custom_response_exception($e, 'Cognito Error');
        } catch (\Exception $e) {
            DB::rollBack();
            return custom_response_exception($e, __('messages.users.register.register.internal_error'));
        }

        return custom_response_sucessfull([__('messages.users.user.verifyCodeEmail.success')]);
    }

    // Reenviar codigo para validar email registrado por primera vez, Cognito.
    public function sendCodeVerifyEmail(SendCodeVerifyEmailRequest $request)
    {
        try {
            $email = $request->email;
            $responseCognito = $this->cognito->sendCodeVerifyEmail($email);
        } catch (CognitoApiException $e) {
            return custom_response_exception($e, 'Cognito Error');
        } catch (\Exception $e) {
            DB::rollBack();
            return custom_response_exception($e, __('messages.users.register.register.internal_error'));
        }

        return response()->json([
            "message" => [__('messages.users.user.sendCodeVerifyEmail.success')],
        ], 200);
    }


    public function sendCodeResetPassword(SendCodeResetPasswordRequest $request)
    {
        try {

            $responseCognito = $this->cognito->forgotPassword($request);
        } catch (EbangoApiException $e) {
            return custom_response_exception($e, 'Ebango Error', $e->getCode());
        } catch (CognitoApiException $e) {
            return custom_response_exception($e, 'Cognito Error');
        } catch (\Exception $e) {
            DB::rollBack();
            return custom_response_exception($e, __('messages.users.register.register.internal_error'));
        }
        $message = [__('messages.users.user.sendCodeResetPassword.success')];
        return custom_response_sucessfull($message);
    }


    public function verifyCodeAndChangePassword(VerifyCodeAndChangePasswordRequest $request)
    {
        try {
            // $responseEbango = $this->ebango->verifyCodeAndChangePassword($request);
            $responseCognito = $this->cognito->verifyCodeAndChangePassword($request);

            if ($responseCognito['statusCode'] !== 200) {
                $e = new \Exception($responseCognito['message'], 422);
                throw $e;
            }
        } catch (EbangoApiException $e) {
            return custom_response_exception($e, 'Ebango Error', $e->getCode());
        } catch (CognitoApiException $e) {
            return custom_response_exception($e, 'Cognito Error');
        } catch (\Exception $e) {
            // DB::rollBack();
            return custom_response_exception($e, __('messages.users.register.register.internal_error'));
        }

        $message = [__('messages.users.user.verifyCodeAndChangePassword.success')];
        return custom_response_sucessfull($message);
    }

    public function ChangePassword(ChangePasswordRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = \App\Services\AuthCognito::userTesting($request->header('authorization'));
            //$responseEbango   = $this->ebango->updateUser($request->new_password, $request->password);
            $responseCognito  = $this->cognito->changePassword($request->new_password, $request->password);
            $user->password = Hash::make($request->new_password);
            $user->save();
            DB::commit();
            $message = [__('messages.users.userSession.changePassword.success')];
            return custom_response_sucessfull($message);
        } catch (EbangoApiException $e) {
            return custom_response_exception($e, 'Ebango Error', $e->getCode());
            DB::rollBack();
        } catch (CognitoApiException $e) {
            return custom_response_exception($e, 'Cognito Error');
            DB::rollBack();
        } catch (\Exception $e) {
            DB::rollBack();
            return custom_response_exception($e, __('messages.users.register.register.internal_error'));
        }
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
        } catch (\Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }

        // $correo = new SendCodeEmailVerifiedMailable($user);
        // Mail::to($user->email)->send($correo);
    }


    protected function sendEmailCode($user)
    {

        $email = new Mail();
        $email->setFrom(config('mail.from.address'), config('mail.from.name'));

        $email->addTo(
            $user->email,
            $user->name,
            [
                "subject" => "Reestablecer Contraseña",
                "name" => $user->name,
                "code" => $user->code,
                // "body"    => "aqui escribe cualquier cosa",
            ],
            0
        );

        $email->setTemplateId("d-d4d2ef275b414b2697122bc3d00454c5");
        $sendgrid = new \SendGrid(config('mail.sendgrid.sendgrid_api_key'));

        try {
            $response = $sendgrid->send($email);
        } catch (\Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }

        // $correo = new SendCodeResetPasswordMailable($user);
        // FacadesMail::to($user->email)->send($correo);
    }


    protected function updatePasswordCode($user, $newPassword, $passwordCode)
    {
        $user->password = $newPassword ? bcrypt($newPassword) : $user->password;
        $user->updated_at = Carbon::now();
        $user->update();

        $passwordCode->delete();
    }

    protected function emailVerified($user)
    {
        $email = new Mail();
        $email->setFrom(config('mail.from.address'), config('mail.from.name'));

        $email->addTo(
            $user->email,
            $user->name,
            [
                "subject" => "Se verificó correo electrónico",
                "email" => $user->email,
                // "body"    => "aqui escribe cualquier cosa",
            ],
            0
        );

        $email->setTemplateId("d-69ef9a8b1d2d40d186467bd10b1dbebc");
        $sendgrid = new \SendGrid(config('mail.sendgrid.sendgrid_api_key'));

        try {
            $response = $sendgrid->send($email);
        } catch (\Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }
        // $correo = new SendCodeEmailVerifiedMailable($user);
        // Mail::to($user->email)->send($correo);

    }


    protected function successfulPasswordRecovery($user)
    {
        $email = new Mail();
        $email->setFrom(config('mail.from.address'), config('mail.from.name'));

        $email->addTo(
            $user->email,
            $user->name,
            [
                "subject" => "Recuperación de contraseña exitosa!",
                "email" => $user->email,
                // "body"    => "aqui escribe cualquier cosa",
            ],
            0
        );

        $email->setTemplateId("d-0a3f3cf3c79b414ca63d03ecafa4a5f2");
        $sendgrid = new \SendGrid(config('mail.sendgrid.sendgrid_api_key'));

        try {
            $response = $sendgrid->send($email);
        } catch (\Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }
        // $correo = new SendCodeEmailVerifiedMailable($user);
        // Mail::to($user->email)->send($correo);

    }

    protected function sendEmailLogin($user)
    {
        $email = new Mail();
        $email->setFrom(config('mail.from.address'), config('mail.from.name'));

        $email->addTo(
            $user->email,
            $user->name,
            [
                "subject" => "Inicio de sesión",
                // "body"    => "aqui escribe cualquier cosa",
            ],
            0
        );

        $email->setTemplateId("d-62ca559d0f2f493a8ef2d2076b604b61");
        $sendgrid = new \SendGrid(config('mail.sendgrid.sendgrid_api_key'));

        try {
            $response = $sendgrid->send($email);
        } catch (\Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }
    }

    protected function errorCognito($error)
    {

        switch ($error) {
            case "UserNotFoundException":
                return response()->json([
                    'errors' => [
                        'email' => [__('messages.users.user.errorCognito.user_not_found')],
                    ]
                ], 400);
                break;
            case "LimitExceededException":
                return response()->json([
                    'errors' => [
                        'email' => [__('messages.users.user.errorCognito.limit_exceeded')],
                    ]
                ], 400);
                break;
            case "InvalidParameterException":
                return response()->json([
                    'errors' => [
                        'email' => [__('messages.users.user.errorCognito.invalid_parameter')],
                    ]
                ], 400);
                break;
            default:
                return response()->json([
                    'errors' => [
                        'message' => [__('messages.users.user.errorCognito.internal_error')],
                    ]
                ], 400);
                break;
        }
    }



    protected function updateEbangoToken($userId, $tokenEbango)
    {
        // return $tokenEbango;
        $ebangoToken             = EbangoToken::where('user_id', $userId)->first();
        $ebangoToken->token      = $tokenEbango;
        $ebangoToken->updated_at = Carbon::now();
        $ebangoToken->update();
    }

    protected function registerEbangoToken($userId, $tokenEbango)
    {
        EbangoToken::create([
            'user_id' => $userId,
            'token'  => $tokenEbango,
        ]);
    }
    public function test_email(Request $request)
    {
        if (isLocalOrTesting()) {
            $user = \App\Services\AuthCognito::userTesting($request->header('Authorization'));
        } else {
            $user = \App\Services\AuthCognito::user();
        }
        switch ($request->value) {
            case 2:
                return custom_response_sucessfull($this->emailVerified($user));
                break;
            case 3:
                return custom_response_sucessfull($this->sendEmailCode($user));
                break;
            case 4:
                return custom_response_sucessfull($this->successfulPasswordRecovery($user));
                break;
            default:
                return custom_response_sucessfull("Not temple code");
                break;
        }
    }

    //Verificar codigo de email por cognito
    public function verifyCodeEmailAdmin(Request $request)
    {

        try {
            DB::beginTransaction();
            $email = $request->email;


            //$responseEbango = $this->ebango->verifyEmail($email,$code);
            $requestCognito = $this->cognito->VerifyEmailAdmin($email);


            $user = User::where('email', $request->email)->first();
            $user->email_verified_at = Carbon::now();
            $user->update();
            $user->refresh();

            $this->welcome($user); // envio de emil

            DB::commit();
        } catch (EbangoApiException $e) {
            return custom_response_exception($e, 'Ebango Error', $e->getCode());
        } catch (CognitoApiException $e) {
            return custom_response_exception($e, 'Cognito Error');
        } catch (\Exception $e) {
            DB::rollBack();
            return custom_response_exception($e, __('messages.users.register.register.internal_error'));
        }

        return custom_response_sucessfull([__('messages.users.user.verifyCodeEmail.success')]);
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


    public function massEmail(Request $request)
    {
        try {
        $users=User::filtro($request)->get();
        foreach($users as $user){
            $email = new Mail();
            $email->setFrom(config('mail.from.address'), config('mail.from.name'));

            $email->addTo(
                $user->email,
                $user->name,
                [
                    "subject" => $request->subject,
                ],
                0
            );
            //"d-62ca559d0f2f493a8ef2d2076b604b61"
            $email->setTemplateId($request->template);
            $sendgrid = new \SendGrid(config('mail.sendgrid.sendgrid_api_key'));


             $response = $sendgrid->send($email);
        }

        } catch (\Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }

        return response()->json([
            "message"       => "Emails enviados con exito",
            "response"      => 'success',
        ]);
    }
}
