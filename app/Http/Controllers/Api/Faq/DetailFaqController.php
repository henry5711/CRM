<?php

namespace App\Http\Controllers\Api\Faq;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryFaqResource;
use App\Models\CategoryFaq;
use Symfony\Component\HttpFoundation\Response;

class DetailFaqController extends Controller
{
    public function fetch()
    {
        try {
            DB::beginTransaction();
            $category = CategoryFaq::with(['faqs','images'])->get();
            DB::commit();

            return response()->json(
                [
                "message"       => "Category FAQ and relationships data FAQ",
                "response"      => CategoryFaqResource::collection($category),
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

}
