<?php

namespace App\Http\Controllers\Api\Labels;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Label;
use App\Http\Resources\LabelResource;

class RegisterController extends Controller
{
    public function register(Request $request)
    {

        request()->merge(['name' => strtoupper(request('name'))]);
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|unique:labels,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            DB::beginTransaction();

            $label = new Label();
            $label->name = $request->name;
            $label->save();

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
            "message"       => "Etiqueta creada correctamente!",
            "response"      => LabelResource::make($label),
        ]);
    }
}
