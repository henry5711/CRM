<?php

namespace App\Http\Controllers\Api\typification;

use App\Models\Typification;
use App\Http\Requests\Typification\StoreTypificationRequest;
use App\Http\Requests\Typification\UpdateTypificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\TypificationResource;
use Carbon\Carbon;
use Illuminate\Http\Response;

class TypificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            DB::beginTransaction();
        $type = Typification::with('type')->get();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.TypificationController.index.index.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if(isset($request->pag)){
            return response()->json([
                "message"       => "tipificacion",
                "response"      => TypificationResource::collection($type)->paginate($request->pag),
            ]);
        }
        else {
            return response()->json([
                "message"       => "tipificacion",
                "response"      => TypificationResource::collection($type),
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTypificationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTypificationRequest $request)
    {
        try {
            DB::beginTransaction();

            $id =  $this->createTypification($request);

            $response = Typification::where('id', $id)->first();

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.Tyfication.store.store.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "Se registro una nueva tipificacion",
            "response"      => TypificationResource::make($response),
        ]);
    }

    protected function createTypification($request)
    {

        $type = new Typification();
        $type->name = $request->name;
        $type->description = $request->description;
        $type->type_typification_id = $request->type_typification_id;
        $type->save();
        return  $type->id;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Typification  $typification
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            DB::beginTransaction();

            $type = Typification::with('type')->where('id', $id)->get();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.Tyfication.show.show.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "detalle de tipificacion",
            "response"      => TypificationResource::make($type),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTypificationRequest  $request
     * @param  \App\Models\Typification  $typification
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTypificationRequest $request,$id)
    {
        $typeValide = Typification::where('id', '=', $id)->first();
        if (!$typeValide) {
            return response()->json([
                "errors" => [
                    "message"       => "No es posible editar la tipificacion",
                ]
            ], 422);
        }
        try {
            DB::beginTransaction();

            $type = Typification::findOrFail($id);
            $this->updateType($type, $request);

            $response = Typification::where('id', $id)->first();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.Typification.update.update.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "tipificacion actulizada",
            "response"      => TypificationResource::make($response),
        ]);
    }

    protected function updateType($type, $request)
    {

        $type->name  = $request->name  ? $request->name  :  $type->name;
        $type->description  = $request->description  ? $request->description  : $type->description;
        $type->type_typification_id  = $request->type_typification_id  ? $request->type_typification_id  :  $type->type_typification_id;
        $type->updated_at  = Carbon::now();
        $type->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Typification  $typification
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $type = Typification::where('id', $id)->first();
            if ($type) {
                $type->delete();
            } else {
                return response()->json([
                    "errors" => [
                        "message"       =>  ["no es posible realizar eliminar esta tipificacion"],
                    ]
                ], 422);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.Typification.delete.delete.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json([
            "message"       => "tipificacion Eliminada",
        ]);
    }


    public function filter(Request $request)
    {
        try {
            DB::beginTransaction();
            $type = Typification::filtro($request)->get();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.Typification.filter.filter.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if(isset($request->pag)){
            return response()->json([
                "message"       => "filtro de tipificacion",
                "response"      => TypificationResource::collection($type)->paginate($request->pag),
            ]);
        }
        else {
            return response()->json([
                "message"       => "filtro de tipificacion",
                "response"      => TypificationResource::collection($type),
            ]);
        }
    }
}
