<?php

namespace App\Http\Controllers\Api\TypeTypification;

use App\Models\typeTypification;
use App\Http\Requests\typeTypification\StoretypeTypificationRequest;
use App\Http\Requests\typeTypification\UpdatetypeTypificationRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\TypeTypificationResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TypeTypificationController extends Controller
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
            $type = typeTypification::all();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.TypeTypificationController.index.index.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (isset($request->pag)) {
            return response()->json([
                "message"       => "Tipo de tipificacion",
                "response"      => TypeTypificationResource::collection($type)->paginate($request->pag),
            ]);
        } else {
            return response()->json([
                "message"       => "Tipo de tipificacion",
                "response"      => TypeTypificationResource::collection($type),
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoretypeTypificationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoretypeTypificationRequest $request)
    {
        try {
            DB::beginTransaction();

            $id =  $this->createTypeTypification($request);

            $response = typeTypification::where('id', $id)->first();

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.TypeTyfication.store.store.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "Se registro un nuevo tipo de tipificacion",
            "response"      => TypeTypificationResource::make($response),
        ]);
    }

    protected function createTypeTypification($request)
    {

        $type = new typeTypification();
        $type->name = $request->name;
        $type->description = $request->description;
        $type->save();
        return  $type->id;
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\typeTypification  $typeTypification
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            DB::beginTransaction();

            $type = typeTypification::where('id', $id)->get();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.typeTification.show.show.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "detalle de tipo de tipificacion",
            "response"      => TypeTypificationResource::make($type),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatetypeTypificationRequest  $request
     * @param  \App\Models\typeTypification  $typeTypification
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatetypeTypificationRequest $request, $id)
    {
        $typeValide = typeTypification::where('id', '=', $id)->first();
        if (!$typeValide) {
            return response()->json([
                "errors" => [
                    "message"       => "No es posible editar el tipo",
                ]
            ], 422);
        }
        try {
            DB::beginTransaction();

            $type = typeTypification::findOrFail($id);
            $this->updateType($type, $request);
            $response = typeTypification::where('id', $id)->first();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.TypeTypification.update.update.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "tipo actulizado",
            "response"      => TypeTypificationResource::make($response),
        ]);
    }


    protected function updateType($type, $request)
    {

        $type->name  = $request->name  ? $request->name  :  $type->name;
        $type->description  = $request->description  ? $request->description  : $type->description;
        $type->updated_at  = Carbon::now();
        $type->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\typeTypification  $typeTypification
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $type = typeTypification::where('id', $id)->first();
            if ($type) {
                $type->delete();
            } else {
                return response()->json([
                    "errors" => [
                        "message"       =>  ["no es posible realizar eliminar este tipo"],
                    ]
                ], 422);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.typeTypification.delete.delete.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json([
            "message"       => "tipo Eliminado",
        ]);
    }

    public function filter(Request $request)
    {
        try {
            DB::beginTransaction();
            $type = typeTypification::filtro($request)->get();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.typeTypification.filter.filter.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (isset($request->pag)) {
            return response()->json([
                "message"       => "filtro tipo de tipificacion",
                "response"      => TypeTypificationResource::collection($type)->paginate($request->pag),
            ]);
        } else {
            return response()->json([
                "message"       => "filtro tipo de tipificacion",
                "response"      => TypeTypificationResource::collection($type),
            ]);
        }
    }
}
