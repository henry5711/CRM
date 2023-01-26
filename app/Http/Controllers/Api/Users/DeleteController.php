<?php

namespace App\Http\Controllers\Api\Users;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Users\DeleteRequest;
use Symfony\Component\HttpFoundation\Response;

class DeleteController extends Controller
{
    public function delete(DeleteRequest $request , int $id){

            try{
            DB::beginTransaction();
            
            $user = User::findOrFail($id);
            if ($user->roles[0]->name <> 'Admin'){
               $user->delete();
            }
            
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
              "message"       => "eliminado usuario correctamente",
             ]);
          }
}
