<?php

namespace App\Http\Controllers\Api\LabelTaks;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CategoryFaq;
use App\Models\Image;
use App\Models\LabelTaks;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LabelTaksController extends Controller
{
    public function index(Request $request)
    {
        try {
            DB::beginTransaction();
            $category = LabelTaks::orderBy("id", "DESC")->withTrashed()->paginate($request->pag);
            DB::commit();

            return response()->json(
                [
                "message"       => "Lista de etiquetas",
                "response"      => $category,
                ]
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => "error interno",
                    'errors' => $e->getMessage(),
                ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function fetch()
    {
        try {
            DB::beginTransaction();
            $category = LabelTaks::get();
            DB::commit();

            return response()->json(
                [
                "message"       => "etiquetas tareas",
                "response"      => $category,
                ]
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => "error interno",
                    'errors' => $e->getMessage(),
                ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $category = LabelTaks::where('id', $id)->withTrashed()->first();
            if (!$category->deleted_at) {
                $category->delete();
            } else {
                $category->restore();
            }
            DB::commit();

            return response()->json(['data' => ['sent' => true]]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => "error interno",
                    'errors' => $e->getMessage(),
                ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make(
                $request->all(), [
                'name' => 'required',
                'description' => 'required',
                ]
            );

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = LabelTaks::create($request->only(['name', 'description']));

            DB::commit();

            return response()->json(['data' => ['sent' => true]]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => "error interno",
                    'errors' => $e->getMessage(),
                ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $validator = Validator::make(
                $request->all(), [
                'name' => 'required',
                'description' => 'required',
                ]
            );

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $brand=  LabelTaks::find($id)->update($request->only(['name', 'description']));

            DB::commit();

            return response()->json(['data' => ['sent' => true]]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => "error interno",
                    'errors' => $e->getMessage(),
                ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
