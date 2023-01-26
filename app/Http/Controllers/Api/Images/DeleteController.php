<?php

namespace App\Http\Controllers\Api\Images;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Images\DeleteImageSystemRequest;

class DeleteController extends Controller
{
    public function deleteImageSystem(DeleteImageSystemRequest $request, $id)
    {

        try {
            DB::beginTransaction();

            $image = Image::where('imageable_type', 'App\Models\SystemImageType')
                ->where('id', $id)
                ->first();

            if ($image) {
                $image->delete();
            } else {
                return response()->json([
                    "errors" => [
                        "message"       =>  ["no es posible realizar la transaccion"],
                    ]
                ], 422);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.contacts.delete.delete.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json([
            "message"       => "Eliminada imagen correctamente!",
        ]);
    }

    public function deleteImageSystemAll($id)
    {

        try {
            DB::beginTransaction();

            $image = Image::where('id', $id)
                ->first();

            if ($image) {
                $image->delete();
            } else {
                return response()->json([
                    "errors" => [
                        "message"       =>  ["no es posible realizar el archivo"],
                    ]
                ], 422);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.contacts.delete.delete.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json([
            "message"       => "archivo Eliminado correctamente!",
        ]);
    }
}
