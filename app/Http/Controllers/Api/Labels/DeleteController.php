<?php

namespace App\Http\Controllers\Api\Labels;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Label;
use App\Http\Requests\Labels\DeleteRequest;

class DeleteController extends Controller
{
    public function delete(DeleteRequest $request, int $id)
    {


        try {
            DB::beginTransaction();

            $label = Label::where('id', $id)->first();
            if ($label) {
                $label->delete();
            } else {
                return response()->json([
                    "errors" => [
                        "message"       =>  ["No es posible realizar la transaccion"],
                    ]
                ], 400);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.labels.delete.delete.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json([
            "message"       => "Eliminada la etiqueta",
        ]);
    }
}
