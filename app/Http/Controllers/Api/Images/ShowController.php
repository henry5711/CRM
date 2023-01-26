<?php

namespace App\Http\Controllers\Api\Images;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\ImageResource;
use Symfony\Component\HttpFoundation\Response;

class ShowController extends Controller
{
    public function showSystemImageProfile(int $id){
        try{
            DB::beginTransaction();
    
            $image = Image::where('id', $id)->where('imageable_type' , 'App\Models\SystemImageType')
            ->first();
            
            if($image){
                $image->pathS3 =  'img/ewex-car/imagenes-del-sistema/';
            }else{
                return response()->json([
                    "errors" => [
                        "message"       =>  ["no es posible realizar la transaccion"],
                    ]
                ], 422);
            }
            
            DB::commit();
            }catch(\Exception $e){
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.currency.register.registerNewBrands.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
    
            return response()->json([
              "message"       => "Imagenes de fondo de perfil de usuario",
              "response"      => ImageResource::make($image),
            ]);
    }
}
