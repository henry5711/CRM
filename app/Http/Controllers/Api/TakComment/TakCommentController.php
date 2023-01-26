<?php

namespace App\Http\Controllers\Api\TakComment;

use App\Models\TakComment;
use App\Http\Requests\TakComment\StoreTakCommentRequest;
use App\Http\Requests\TakComment\UpdateTakCommentRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\TakCommentResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TakCommentController extends Controller
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
        $takComment =TakComment::with(['getUser','geTak'])->get();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.TakCommentController.index.index.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if(isset($request->pag)){
            return response()->json([
                "message"       => "Tak comment",
                "response"      => TakCommentResource::collection($takComment)->paginate($request->pag),
            ]);
        }
        else {
            return response()->json([
                "message"       => "Tak comment",
                "response"      => TakCommentResource::collection($takComment),
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTakCommentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTakCommentRequest $request)
    {
        try {
            DB::beginTransaction();
            $id =  $this->createTakComment($request);

            $response = TakComment::where('id', $id)->first();

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.TakComment.store.store.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "Se registro una nuevo comentario en la tarea",
            "response"      => TakCommentResource::make($response),
        ]);
    }

    protected function createTakComment($request)
    {

        $tak = new TakComment();
        $tak->content = $request->content;
        $tak->tak_id = $request->tak_id;
        $tak->user_id = $request->user_id;
        $tak->save();
        return  $tak->id;
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TakComment  $takComment
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            DB::beginTransaction();

            $tak = TakComment::with(['getUser','geTak'])
                ->where('id', $id)->get();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.TakComment.show.show.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "detalle de takComment",
            "response"      => TakCommentResource::make($tak),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTakCommentRequest  $request
     * @param  \App\Models\TakComment  $takComment
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTakCommentRequest $request, $id)
    {
        $takValide = TakComment::where('id', '=', $id)->first();
        if (!$takValide) {
            return response()->json([
                "errors" => [
                    "message"       => "No es posible editar este commentario",
                ]
            ], 422);
        }
        try {
            DB::beginTransaction();

            $client = TakComment::findOrFail($id);
            $this->updateTakComment($client, $request);

            $response = TakComment::where('id', $id)->first();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.TakComment.update.update.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "tak comment actulizado",
            "response"      => TakCommentResource::make($response),
        ]);
    }

    protected function updateTakComment($tak, $request)
    {

        $tak->content  = $request->content  ? $request->content  :  $tak->content;
        $tak->tak_id  = $request->tak_id ? $request->tak_id  : $tak->tak_id ;
        $tak->user_id = $request->user_id ? $request->user_id  :  $tak->user_id;
        $tak->updated_at  = Carbon::now();
        $tak->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TakComment  $takComment
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $tak = TakComment::where('id', $id)->first();
            if ($tak) {
                $tak->delete();
            } else {
                return response()->json([
                    "errors" => [
                        "message"       =>  ["no es posible realizar eliminar este commentario"],
                    ]
                ], 422);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.TakComment.delete.delete.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json([
            "message"       => "comentario Eliminado",
        ]);
    }

    public function filter(Request $request)
    {
        try {
            DB::beginTransaction();
            $tak = TakComment::with(['getUser','geTak'])
            ->filtro($request)->get();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.takComment.filter.filter.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if(isset($request->pag)){
            return response()->json([
                "message"       => "filtro tak comment",
                "response"      => TakCommentResource::collection($tak)->paginate($request->pag),
            ]);
        }
        else {
            return response()->json([
                "message"       => "filtro tak comment",
                "response"      => TakCommentResource::collection($tak),
            ]);
        }
    }
}
