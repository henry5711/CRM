<?php

namespace App\Http\Controllers\Api\Faq;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CategoryFaq;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CategoryFaqController extends Controller
{
    public function index(Request $request)
    {

        try {
            DB::beginTransaction();
            $category = CategoryFaq::with('images')->orderBy("id", "DESC")->withTrashed()->paginate($request->pag);
            DB::commit();



            return response()->json(
                [
                "message"       => "Lista de categorias FAQ",
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
            $category = CategoryFaq::get();
            DB::commit();

            return response()->json(
                [
                "message"       => "categorias FAQ",
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
            $category = CategoryFaq::where('id', $id)->withTrashed()->first();
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
                ]
            );

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = CategoryFaq::create($request->only(['title', 'body']));

            $file = $request->file('file');
            if ($request->hasFile('file')) {
                $path = $file->store('img/ewex-car/faq', 's3');

                $image = Image::create(
                    [
                    'filename'       => basename($path),
                    'imageable_type' => CategoryFaq::class,
                    'imageable_id'   => $data->id,
                    'url'            => Storage::disk('s3')->url($path),
                    'tag'            => 'faq',
                    'category'       =>  null,
                    ]
                );
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

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $validator = Validator::make(
                $request->all(), [
                'title' => 'required',
                'body' => 'required',
                ]
            );

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $brand=  CategoryFaq::find($id)->update($request->only(['title', 'body']));

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $image = Image::where('imageable_type', CategoryFaq::class)
                    ->where('imageable_id', $id)
                    ->where('tag', 'faq')
                    ->first();
                if ($file) {
                    if ($image) {

                        $image->delete();

                        $path = $file->store('img/ewex-car/faq', 's3');

                        $image = Image::create(
                            [
                            'filename'       => basename($path),
                            'imageable_type' => CategoryFaq::class,
                            'imageable_id'   => $id,
                            'url'            => Storage::disk('s3')->url($path),
                            'tag'            => 'faq',
                            'category'       =>  null,
                            ]
                        );
                    } else {
                        $path = $file->store('img/ewex-car/faq', 's3');

                        $image = Image::create(
                            [
                            'filename'       => basename($path),
                            'imageable_type' => CategoryFaq::class,
                            'imageable_id'   => $id,
                            'url'            => Storage::disk('s3')->url($path),
                            'tag'            => 'faq',
                            'category'       =>  null,
                            ]
                        );
                    }
                }
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


}
