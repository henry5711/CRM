<?php

namespace App\Http\Controllers\Api\Images;

use App\Models\Image;
use Illuminate\Http\Request;
use App\Models\SystemImageType;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Images\RegisterImageRequest;
use App\Http\Resources\SystemImageTypeResource;
use App\Http\Requests\Images\RegisterSystemImageTypeRequest;
use App\Http\Requests\Images\UpdateImageRequest;
use Carbon\Carbon;

class RegisterController extends Controller
{
    public function registerSystemImageType(RegisterSystemImageTypeRequest $request)
    {

        try {
            DB::beginTransaction();

            $systemImageType              = new SystemImageType();
            $systemImageType->name        = $request->name;
            $systemImageType->description = $request->description;
            $systemImageType->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.currency.register.registerNewBrands.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "Registrar tipo de imagen de sistema (adm)",
            "response"      => SystemImageTypeResource::make($systemImageType),
        ]);
    }

    public function registerImage(RegisterImageRequest $request)
    {


        try {
            DB::beginTransaction();

            $file = $request->file('file');
            if ($request->hasFile('file')) {
                $path = $file->store('img/ewex-car/imagenes-del-sistema', 's3');

                $image = Image::create([
                    'filename'       => basename($path),
                    'imageable_type' => SystemImageType::class,
                    'imageable_id'   => $request->idSystemImageType,
                    'url'            => Storage::disk('s3')->url($path),
                    'name'           => $request->name,
                    'description'    => $request->description,
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.currency.register.registerNewBrands.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "Se registro imagen correctamente! (adm)",
        ]);
    }

    public function update(UpdateImageRequest $request, $id)
    {

        $imagenValide = Image::where('id', '=', $id)->first();
        if (!$imagenValide) {
            return response()->json([
                "errors" => [
                    "message"       => "No existe esta imagen",
                ]
            ], 422);
        }
        try {
            DB::beginTransaction();

            $imagen = Image::findOrFail($id);
            $this->updateImagen($imagen, $request);

            $response = Image::where('id', $id)->first();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.images.register.update.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "Archivo actualizado",
            "response"      => SystemImageTypeResource::make($response),
        ]);
    }

    protected function updateImagen($imagen, $request)
    {

        $imagen->imageable_id  = $request->imageable_id  ? $request->imageable_id  : $imagen->imageable_id;
        $imagen->name = $request->name  ? $request->name : $imagen->name;
        $imagen->description  = $request->description ? $request->description  : $imagen->description;
        $imagen->updated_at  = Carbon::now();
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('img/ewex-car/imagenes-del-sistema', 's3');
            $imagen->filename = basename($path);
            $imagen->url = Storage::disk('s3')->url($path);
        }
        $imagen->save();
    }
}
