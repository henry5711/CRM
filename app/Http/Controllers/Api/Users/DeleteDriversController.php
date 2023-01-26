<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\RegisterDriver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Users\DeleteDriversRequest;

class DeleteDriversController extends Controller
{
    public function delete(DeleteDriversRequest $request , int $id){
        DB::beginTransaction();
        try{
            $driver = RegisterDriver::find($id);
            if(empty($driver)){
                return response()->json([
                    "message" => "Registro no existe",
                ]);
            }
            $driver->delete();
            DB::commit();
        }catch(\Exception $e){
        DB::rollBack();
        return response()->json([
            'data' => [
                'code'   => $e->getCode(),
                'title'  => [__('messages.users.delete.delete.internal_error')],
                'errors' => $e->getMessage(),
            ]
          ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json([
            "message" => "Registro eliminado",
        ]);
    }
}
