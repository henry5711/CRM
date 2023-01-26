<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Users\IndexDriversRequest;
use Illuminate\Support\Facades\DB;
use App\Models\RegisterDriver;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\DriversResource;

class IndexDriversController extends Controller
{
    public function index(IndexDriversRequest $request)
    {
        DB::beginTransaction();
        try {

            $drivers = RegisterDriver::with(
                    'country',
                    'model',
                    'brand',
                    'images'
                )
                ->where('country_id', $request->nat)
                ->get();
                return response()->json([ 'data' =>$drivers]);
            if (empty($request->pag)) {
                $request->pag = 1;
            } else {
                $request->pag = (int)$request->pag;
            }
            foreach ($drivers AS $driver) {
                foreach ($driver->images as $img) {
                    $img->pathS3 =  'img/ewex-car/driver/';
                }
            }
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
            "message" => "Lista de conductores",
            "response" => DriversResource::collection($drivers)->paginate($request->pag),
        ]);
    }
}
