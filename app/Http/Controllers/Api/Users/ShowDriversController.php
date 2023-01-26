<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Users\ShowDriversRequest;
use App\Models\RegisterDriver;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\DriversResource;

class ShowDriversController extends Controller
{
    public function show(ShowDriversRequest $request, int $id)
    {
        DB::beginTransaction();
        try {
            $driver = RegisterDriver::with(
                'country',
                'model',
                'brand',
                'images'
            )
            ->where('country_id', $request->nat)
            ->where('id', $id)
            ->first();

            if(!empty($driver)){
                foreach ($driver->images as $img) {
                    $img->pathS3 =  'img/ewex-car/driver';
                }
            }else{
                return response()->json([
                    "message"       => 'Registro de conductor no valido',
                  ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => "AUSDC-Error interno",
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json([
            "message"       => "Detalle de conductor (Adm)",
            "response"      => DriversResource::make($driver),
        ]);
    }
}
