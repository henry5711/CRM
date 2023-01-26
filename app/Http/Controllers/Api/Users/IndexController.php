<?php

namespace App\Http\Controllers\Api\Users;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomRequest;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Users\IndexRequest;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Users\GetUserRequest;
use App\Http\Requests\Users\GetRolesRequest;
use App\Http\Requests\Users\IndexRolUserRequest;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends Controller
{
    public function getIdentityVerificationRequests(CustomRequest $request)
    {

        try {
            DB::beginTransaction();

            $users = User::withTrashed()->with(
                'userDetail.identityVerification.requestStatuFrontal',
                'userDetail.identityVerification.requestStatuBack',
                'userDetail.identityVerification.requestStatuSelfie',
                'userDetail.identityVerification.requestStatuAddress',
                'userDetail.identityVerification.requestStatuGeneral',
                'userDetail.broker'
            )
                ->whereHas('userDetail', function ($query) {
                    $query->whereHas('identityVerification', function ($query) {
                        $query->where('id', '>', 0);
                    });
                });

            if ($request->email) {
                $email = $request->email;
                $users = $users->where('email', 'LIKE', '%' . $email . '%');
            }

            if ($request->name) {
                $name = $request->name;
                $users = $users->where('name', 'LIKE', '%' . $name . '%');
            }

            if (!empty($request->status)) {
                $status = $request->status;
                if ($status == 'check') {
                    $user = $this->addStatusFilter($users, 1);
                }

                if ($status == 'passed') {
                    $user = $this->addStatusFilter($users, 2);
                }

                if ($status == 'refused') {
                    $user = $this->addStatusFilter($users, 3);
                }
            }

            $users = $users->orderBy('created_at', 'DESC')->get();


            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code' => $ex->getCode(),
                    'title' => "error interno",
                    'errors' => $ex->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => "Lista de solicitudes de verificacion de documento (adm)",
            "response" => UserResource::collection($users)->paginate($request->pag),
        ]);
    }

    public function index(IndexRequest $request)
    {
        try {
            DB::beginTransaction();

            $usersQuery = User::query();
            $usersQuery->withTrashed();
            $role = $request->role_id;

            $usersQuery->whereHas('userDetail', function (Builder $query) use ($role) {
                $query->withTrashed();
            });
            if ($role) {
                $usersQuery->whereHas('roles', function (Builder $query) use ($role) {
                    $query->where('role_id', $role);
                });
            } else {
                $usersQuery->whereHas('roles', function (Builder $query) use ($role) {
                });
            }

            if ($request->email) {
                $email = $request->email;
                $usersQuery->where('email', 'LIKE', '%' . $email . '%');
            }

            if ($request->name) {
                $name = $request->name;
                $usersQuery->where('name', 'LIKE', '%' . $name . '%');
            }

            if ($request->notRole) {
                $usersQuery->whereHas('roles', function (Builder $query) use ($request) {
                    $query->whereNotIn('role_id',$request->notRole);
                });
            }

            if (!empty($request->status)) {
                $status = $request->status;
                if ($status == 'INACTIVE') {
                    $usersQuery->where('deleted_at', '!=', null);
                }

                if ($status == 'ACTIVE') {
                    $usersQuery->where('deleted_at', '=', null);
                }
            }

            $response = $usersQuery->with(
                'userDetail.gender',
                'userDetail.tpDocument',
                'userDetail.identityVerification.requestStatuFrontal',
                'userDetail.identityVerification.requestStatuBack',
                'userDetail.identityVerification.requestStatuSelfie',
                'userDetail.identityVerification.requestStatuAddress',
                'userDetail.identityVerification.requestStatuGeneral',
                'userDetail.country',
                'userDetail.broker.getCountry',
                'roles'
            )->orderBy("id", "DESC")->get();


            // deletes show : withTrashed();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code' => $e->getCode(),
                    'title' => [__('messages.users.index.index.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (isset($request->pag)) {
            return response()->json([
                "message" => "lista de todos los usuarios",
                "response" => UserResource::collection($response)->paginate($request->pag),
            ]);
        } else {
            return response()->json([
                "message" => "lista de todos los usuarios",
                "response" => UserResource::collection($response),
            ]);
        }
    }

    public function getRoles(GetRolesRequest $request)
    {

        try {
            DB::beginTransaction();

            $roles = Role::get();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code' => $e->getCode(),
                    'title' => [__('messages.users.index.index.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message" => "lista de todos los usuarios",
            "response" => RoleResource::collection($roles),
        ]);
    }


    public function getUser(GetUserRequest $request)
    {

        try {
            DB::beginTransaction();


            $usersClient = User::whereHas('roles', function (Builder $query) {
                $query->where('name', 'Cliente');
            })->get();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code' => $e->getCode(),
                    'title' => [__('messages.users.index.index.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json([
            "message" => "lista de todos los clientes SELECT",
            "response" => UserResource::collection($usersClient),
        ]);
    }

    public function addStatusFilter($users, $status_id)
    {
        $users = $users->whereHas('userDetail', function ($query) use ($status_id) {
            $query->whereHas('identityVerification', function ($query) use ($status_id) {
                $query->whereHas('requestStatuGeneral', function ($query) use ($status_id) {
                    $query->where('id', '=', $status_id);
                });
            });
        });

        return $users;
    }

    public function IndexRolUser(IndexRolUserRequest $request)
    {
        try {
            DB::beginTransaction();

            $usersQuery = User::query();
            $usersQuery->withTrashed();
            $role = $request->role_id;

            $usersQuery->whereHas('userDetail', function (Builder $query) use ($role) {
                $query->withTrashed();
            });
            $usersQuery->whereHas('roles', function (Builder $query) use ($role) {
                $query->where('role_id', $role);
            });



            $response = $usersQuery->with(
                'userDetail.gender',
                'userDetail.tpDocument',
                'userDetail.identityVerification.requestStatuFrontal',
                'userDetail.identityVerification.requestStatuBack',
                'userDetail.identityVerification.requestStatuSelfie',
                'userDetail.identityVerification.requestStatuAddress',
                'userDetail.identityVerification.requestStatuGeneral',
                'userDetail.country',
                'userDetail.broker.getCountry',
                'roles'
            )->orderBy("id", "DESC")->get();


            // deletes show : withTrashed();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code' => $e->getCode(),
                    'title' => [__('messages.users.index.index.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json([
            "message" => "lista de todos los usuarios",
            "response" => UserResource::collection($response)->paginate($request->pag),
        ]);
    }
}
