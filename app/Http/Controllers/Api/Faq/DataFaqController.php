<?php

namespace App\Http\Controllers\Api\Faq;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Models\DataFaq;
use App\Models\Image;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class DataFaqController extends Controller
{
    public function index(Request $request)
    {
        try {
            DB::beginTransaction();
            $category = DataFaq::orderBy("id", "DESC")->withTrashed()->paginate($request->pag);
            DB::commit();

            return response()->json(
                [
                "message"       => "Lista de Data FAQ",
                "response"      => $category->load('images'),
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
            $category = DataFaq::get();
            DB::commit();

            return response()->json(
                [
                "message"       => "Data FAQ",
                "response"      => $category->load('images'),
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
            $category = DataFaq::where('id', $id)->withTrashed()->first();
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
                'title' => 'required',
                'body' => 'required',
                'category_faq_id' => 'required',
                ]
            );

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data= DataFaq::create($request->only(['title', 'body','category_faq_id']));


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
                'title' => 'required',
                'body' => 'required',
                'category_faq_id' => 'required',
                ]
            );

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            DataFaq::find($id)->update($request->only(['title', 'body','category_faq_id']));

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
