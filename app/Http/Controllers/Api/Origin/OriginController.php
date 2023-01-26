<?php

namespace App\Http\Controllers\Api\Origin;

use App\Models\Origin;
use App\Http\Requests\Origin\StoreOriginRequest;
use App\Http\Requests\Origin\UpdateOriginRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\OriginResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class OriginController extends Controller
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
            $origin = Origin::get();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.OriginController.index.index.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (isset($request->pag)) {
            return response()->json([
                "message"       => "Origenes",
                "response"      => OriginResource::collection($origin)->paginate($request->pag),
            ]);
        } else {
            return response()->json([
                "message"       => "Origenes",
                "response"      => OriginResource::collection($origin),
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreOriginRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreOriginRequest $request)
    {
        try {
            DB::beginTransaction();

            $id =  $this->createOrigin($request);

            $response = Origin::where('id', $id)->first();

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.Origin.store.store.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "Se registro una nuevo origin",
            "response"      => OriginResource::make($response),
        ]);
    }

    protected function createOrigin($request)
    {

        $origin = new Origin();
        $origin->name = $request->name;
        $origin->description = $request->description;
        $origin->save();
        return  $origin->id;
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Origin  $origin
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            DB::beginTransaction();

            $origin = Origin::where('id', $id)->first();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.Origin.show.show.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "detalle de origin",
            "response"      => OriginResource::make($origin),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateOriginRequest  $request
     * @param  \App\Models\Origin  $origin
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateOriginRequest $request, $id)
    {
        $originValide = Origin::where('id', '=', $id)->first();
        if (!$originValide) {
            return response()->json([
                "errors" => [
                    "message"       => "No es posible realizar editar el origen",
                ]
            ], 422);
        }
        try {
            DB::beginTransaction();

            $origin = Origin::findOrFail($id);
            $this->updateOrigin($origin, $request);

            $response = Origin::where('id', $id)->first();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.Origin.update.update.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "origen actulizado",
            "response"      => OriginResource::make($response),
        ]);
    }

    protected function updateOrigin($origin, $request)
    {

        $origin->name  = $request->name  ? $request->name  :  $origin->name;
        $origin->description  = $request->description  ? $request->description  : $origin->description;
        $origin->updated_at  = Carbon::now();
        $origin->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Origin  $origin
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $origin = Origin::where('id', $id)->first();
            if ($origin) {
                $origin->delete();
            } else {
                return response()->json([
                    "errors" => [
                        "message"       =>  ["no es posible realizar eliminar esta origen"],
                    ]
                ], 422);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.Origin.delete.delete.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json([
            "message"       => "Origen Eliminado",
        ]);
    }
    public function filter(Request $request)
    {
        try {
            DB::beginTransaction();
            $origin = Origin::filtro($request)->get();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.Origin.filter.filter.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (isset($request->pag)) {
            return response()->json([
                "message"       => "filtro origin",
                "response"      => OriginResource::collection($origin)->paginate($request->pag),
            ]);
        } else {
            return response()->json([
                "message"       => "filtro origin",
                "response"      => OriginResource::collection($origin),
            ]);
        }
    }
}
