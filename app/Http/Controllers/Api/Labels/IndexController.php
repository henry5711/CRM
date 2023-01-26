<?php

namespace App\Http\Controllers\Api\Labels;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Label;
use App\Http\Resources\LabelResource;

class IndexController extends Controller
{
    public function index(Request $request)
    {

        try {
            DB::beginTransaction();
            $label = Label::orderBy("id", "DESC")->get();

            if (empty($request->pag)) {
                $request->pag = 10;
            } else {
                $request->pag = (int)$request->pag;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => "error interno",
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "Lista de etiquetas Adm",
            "response"      => LabelResource::collection($label)->paginate($request->pag),
        ]);
    }

    public function getLabel()
    {

        try {
            DB::beginTransaction();


            $label = Label::get();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => "error interno",
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "Lista de etiquetas Adm",
            "response"      => LabelResource::collection($label),
        ]);
    }
}
