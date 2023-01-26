<?php

namespace App\Http\Controllers\Api\Images;

use App\Models\Image;
use Illuminate\Http\Request;
use App\Models\SystemImageType;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\ImageResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\SystemImageTypeResource;
use App\Http\Requests\Images\IndexSystemImageTypeRequest;

class IndexController extends Controller
{

    public function selectSystemImageType(){

        try{
            DB::beginTransaction();

            $systemImageType = SystemImageType::get();

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
              "message"       => "Select tipos de imagen del sistema (adm)",
              "response"      => SystemImageTypeResource::collection($systemImageType),
            ]);

    }

    public function indexSystemImageType(IndexSystemImageTypeRequest $request){

        try{
            DB::beginTransaction();

            if($request->idTypeImage == null){
                $images = Image::where('imageable_type' , 'App\Models\SystemImageType')
                ->get();
            }else{
                $images = Image::where('imageable_type' , 'App\Models\SystemImageType')
                ->where('imageable_id',$request->idTypeImage)
                ->get();
            }

            foreach($images as $image )
            {
                $image->pathS3 =  'img/ewex-car/imagenes-del-sistema/';
            }

            if($request->idTypeImage == null){
                $type = "TODAS";
            }else{
                $systemImageType = SystemImageType::where('id',$request->idTypeImage)->first();
                $type = $systemImageType->name;
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
              "message"       => "Imagenes del sistema [".$type."] (adm)",
              "response"      => ImageResource::collection($images)->paginate($request->pag),
            ]);
    }


    public function getSystemImageProfile(){
        try{
            DB::beginTransaction();


            $images = Image::where('imageable_type' , 'App\Models\SystemImageType')
            ->where('imageable_id' , 1)
            ->get();

            foreach($images as $image )
            {
                    $image->pathS3 =  'img/ewex-car/imagenes-del-sistema/';
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
              "response"      => ImageResource::collection($images),
            ]);
    }
    public function filterImagen(Request $request){
        try{
            DB::beginTransaction();


            $images = Image::where('imageable_type' , 'App\Models\SystemImageType')->filtro($request)->get();

            foreach($images as $image )
            {
                    $image->pathS3 =  'img/ewex-car/imagenes-del-sistema/';
            }

            DB::commit();
            }catch(\Exception $e){
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.imagen.index.filterImagen')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json([
              "message"       => "Archivos",
              "response"      => ImageResource::collection($images),
            ]);
    }
}
