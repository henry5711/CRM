<?php

namespace App\Http\Controllers\Api\Tak;

use App\Models\Tak;
use App\Http\Requests\Tak\StoreTakRequest;
use App\Http\Requests\Tak\UpdateTakRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\TakResource;
use App\Models\Image;
use App\Models\LabelTaks;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TakController extends Controller
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
            $tak = Tak::with(['getUser', 'getCreator', 'getStatus', 'getComments', 'labesTaks','images'])->get();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.TakController.index.index.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


        foreach ($tak as $taks) {
            foreach ($taks->images as $img) {
                $img->pathS3 =  'img/ewex-car/tak/';
            }
        }

        if (isset($request->pag)) {
            return response()->json([
                "message"       => "Taks",
                "response"      => TakResource::collection($tak)->paginate($request->pag),
            ]);
        } else {
            return response()->json([
                "message"       => "Taks",
                "response"      => TakResource::collection($tak),
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTakRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTakRequest $request)
    {
        try {
            DB::beginTransaction();
            $id =  $this->createTak($request);

            $response = Tak::where('id', $id)->first();

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.Tak.store.store.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "Se registro una nuava tak",
            "response"      => TakResource::make($response),
        ]);
    }

    protected function createTak($request)
    {

        $tak = new Tak();
        $tak->title = $request->title;
        $tak->description = $request->description;
        $tak->user_id = $request->user_id;
        $tak->creator_id = $request->creator_id;
        $tak->fec_ini = $request->fec_ini;
        $tak->fec_end = $request->fec_end;
        $tak->status_id = $request->status_id;

        $tak->save();

        if (isset($request->labels)) {
            $tak->labesTaks()->sync($request->labels);
        }

        return  $tak->id;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tak  $tak
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            DB::beginTransaction();

            $tak =Tak::with(['getUser', 'getCreator', 'getStatus', 'getComments', 'labesTaks','images'])
                ->where('id', $id)->get();

                foreach ($tak as $taks) {
                    foreach ($taks->images as $img) {
                        $img->pathS3 =  'img/ewex-car/tak/';
                    }
                }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.Tak.show.show.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "detalle de tak",
            "response"      => TakResource::collection($tak),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTakRequest  $request
     * @param  \App\Models\Tak  $tak
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTakRequest $request, $id)
    {
        $takValide = Tak::where('id', '=', $id)->first();
        if (!$takValide) {
            return response()->json([
                "errors" => [
                    "message"       => "No es posible editar la tarea",
                ]
            ], 422);
        }
        try {
            DB::beginTransaction();

            $client = Tak::findOrFail($id);
            $this->updateTak($client, $request);
            if (isset($request->labels)) {
                $client->labesTaks()->sync($request->labels);
            }

            $response = Tak::where('id', $id)->first();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.Tak.update.update.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "tak actulizado",
            "response"      => TakResource::make($response),
        ]);
    }

    protected function updateTak($tak, $request)
    {

        $tak->title  = $request->title  ? $request->title  :  $tak->title;
        $tak->description  = $request->description ? $request->description  : $tak->description;
        $tak->status_id  = $request->status_id ? $request->status_id :  $tak->status_id;
        $tak->user_id = $request->user_id ? $request->user_id  :  $tak->user_id;
        $tak->fec_ini = $request->fec_ini ? $request->fec_ini : $tak->fec_ini;
        $tak->fec_end = $request->fec_end ? $request->fec_end : $tak->fec_end;
        $tak->creator_id = $request->creator_id ? $request->creator_id  :  $tak->creator_id;
        $tak->updated_at  = Carbon::now();
        $tak->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tak  $tak
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $tak = Tak::where('id', $id)->first();
            if ($tak) {
                $tak->delete();
            } else {
                return response()->json([
                    "errors" => [
                        "message"       =>  ["no es posible realizar eliminar esta tarea"],
                    ]
                ], 422);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.Tak.delete.delete.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json([
            "message"       => "Tak Eliminado",
        ]);
    }

    public function filter(Request $request)
    {
        try {
            DB::beginTransaction();
            $tak = Tak::with(['getUser', 'getCreator', 'getStatus', 'getComments', 'labesTaks','images'])
                ->filtro($request)->get();

                foreach ($tak as $taks) {
                    foreach ($taks->images as $img) {
                        $img->pathS3 =  'img/ewex-car/tak/';
                    }
                }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.tak.filter.filter.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (isset($request->pag)) {
            return response()->json([
                "message"       => "filtro tak",
                "response"      => TakResource::collection($tak)->paginate($request->pag),
            ]);
        } else {
            return response()->json([
                "message"       => "filtro tak",
                "response"      => TakResource::collection($tak),
            ]);
        }
    }

    public function relationLabels(Request $request, $id)
    {

        $tak = Tak::find($id);
        if (!$tak) {
            return response()->json([
                "errors" => [
                    "message"       => "No es posible añadir etiqueta",
                ]
            ], 422);
        }
        try {
            DB::beginTransaction();

            $tak = Tak::findOrFail($id);
            $tak->labesTaks()->sync($request->labels);

            $response = $tak = Tak::with(['getUser', 'getCreator', 'getStatus', 'getComments', 'labesTaks','images'])->find($id);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.Tak.relationLabels.update.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "tak actulizado",
            "response"      => TakResource::make($response),
        ]);
    }

    public function addFile(Request $request,$id){
        $file = $request->file('file');
        if ($request->hasFile('file')) {
            $path = $file->store('img/ewex-car/tak', 's3');

            $image = Image::create([
                'filename'       => basename($path),
                'imageable_type' => Tak::class,
                'imageable_id'   => $id,
                'url'            => Storage::disk('s3')->url($path),
                'tag'            => 'Tak',
                'category'       => 'Tak',
            ]);
        }

        return response()->json([
            "message"       => "tak archivo",
            "response"      => $image,
        ]);
    }

    public function listAgetsTaks($user_id,Request $request)
    {
        try {
            DB::beginTransaction();

            $tak =Tak::with(['getUser', 'getCreator', 'getStatus', 'getComments', 'labesTaks','images'])
                ->where('user_id', $user_id)->orWhere('creator_id',$user_id)->get();

                foreach ($tak as $taks) {
                    foreach ($taks->images as $img) {
                        $img->pathS3 =  'img/ewex-car/tak/';
                    }
                }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.Tak.listAgetsTaks.listAgetsTaks.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


        if (isset($request->pag)) {
            return response()->json([
                "message"       => "Taks",
                "response"      => TakResource::collection($tak)->paginate($request->pag),
            ]);
        } else {
            return response()->json([
                "message"       => "Taks",
                "response"      => TakResource::collection($tak),
            ]);
        }
    }
}
