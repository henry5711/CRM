<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Users\UpdateDriversRequest;
use App\Models\RegisterDriver;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Storage;
use App\Models\Image;
use App\Http\Resources\DriversResource;

class UpdateDriversController extends Controller
{
    public function update(UpdateDriversRequest $request, int $id)
    {
        if (!isLocalOrTesting()) {
            if (!\App\Services\AuthCognito::user()->hasRole('Admin')) {
                if (!\App\Services\AuthCognito::user()->can('api.v1.users.update.drivers')) {
                    return response()->json([
                        'data' => [
                            'code'   => 403,
                            'title'  => ["COGNITO - Actualizar coonductor"],
                            'errors' => [__("Unauthorized")],
                        ]
                    ], Response::HTTP_UNAUTHORIZED);
                }
            }
        }

        request()->merge(['name' => strtoupper(request('name'))]);
        try {
            DB::beginTransaction();
            $driver = RegisterDriver::with(
                    'country',
                    'model',
                    'brand',
                    'images'
                )
                ->where('id', $id)
                ->first();
            if (empty($driver)) {
                return response()->json([
                    "error" => [
                        "message"  => ["id no encontrado"]
                    ]
                ]);
            }

            $driver->country_id        = $request->filled('nat')        ? $request->nat       : $driver->country_id;
            $driver->vehicle_model_id  = $request->filled('vehicle_model_id')  ? $request->vehicle_model     : $driver->vehicle_model_id;
            $driver->vehicle_brand_id  = $request->filled('vehicle_brand_id')  ? $request->vehicle_make      : $driver->vehicle_brand_id;
            $driver->vehicle_number    = $request->filled('vehicle_number')    ? $request->vehicle_number    : $driver->vehicle_number;
            $driver->vehicle_colour    = $request->filled('vehicle_colour')    ? $request->vehicle_colour    : $driver->vehicle_colour;
            $driver->vehicle_type      = $request->filled('vehicle_type')      ? $request->vehicle_type      : $driver->vehicle_type;
            $driver->city              = $request->filled('city')              ? $request->city              : $driver->city;
            $driver->postal_code       = $request->filled('postal_code')       ? $request->postal_code       : $driver->postal_code;
            $driver->area              = $request->filled('area')              ? $request->area              : $driver->area;
            $driver->service           = $request->filled('service')           ? $request->service           : $driver->service;
            $driver->name              = $request->filled('name')              ? $request->name              : $driver->name;
            $driver->surname           = $request->filled('surname')           ? $request->surname           : $driver->surname;
            $driver->date_birth        = $request->filled('date_birth')        ? $request->date_birth        : $driver->date_birth;
            $driver->update();
            $file = $request->file('property_card');
            if ($request->hasFile('property_card')) {
                $property_card = Image::where('property_card', RegisterDriver::class)
                    ->where('imageable_id', $id)
                    ->where('tag', 'driver')
                    ->first();
                if($file) {
                    $path = $file->store('img/ewex-car/driver', 's3');
                    if ($property_card) {
                        $property_card->delete();
                    }
                    Image::create([
                        'filename'       => basename($path),
                        'imageable_type' => RegisterDriver::class,
                        'imageable_id'   => $id,
                        'url'            => Storage::disk('s3')->url($path),
                        'tag'            => 'driver',
                        'category'       =>  null,
                    ]);
                }
            }
            $file = $request->file('vehicle_plate');
            if ($request->hasFile('vehicle_plate')) {
                $vehicle_plate = Image::where('imageable_type', RegisterDriver::class)
                    ->where('imageable_id', $id)
                    ->where('tag', 'driver')
                    ->first();
                if($file) {
                    $path = $file->store('img/ewex-car/driver', 's3');
                    if ($vehicle_plate) {
                        $vehicle_plate->delete();
                    }
                    Image::create([
                        'filename'       => basename($path),
                        'imageable_type' => RegisterDriver::class,
                        'imageable_id'   => $id,
                        'url'            => Storage::disk('s3')->url($path),
                        'tag'            => 'driver',
                        'category'       =>  null,
                    ]);
                }
            }
            $file = $request->file('vehicle_photo');
            if ($request->hasFile('vehicle_photo')) {
                $vehicle_photo = Image::where('imageable_type', RegisterDriver::class)
                    ->where('imageable_id', $id)
                    ->where('tag', 'driver')
                    ->first();
                if($file) {
                    $path = $file->store('img/ewex-car/driver', 's3');
                    if ($vehicle_photo) {
                        $vehicle_photo->delete();
                    }
                    Image::create([
                        'filename'       => basename($path),
                        'imageable_type' => RegisterDriver::class,
                        'imageable_id'   => $id,
                        'url'            => Storage::disk('s3')->url($path),
                        'tag'            => 'driver',
                        'category'       =>  null,
                    ]);
                }
            }

            // $file = $request->file('identification_document');
            // if ($request->hasFile('identification_document')) {
            //     $identification_document = Image::where('imageable_type', RegisterDriver::class)
            //         ->where('imageable_id', $id)
            //         ->where('tag', 'driver')
            //         ->first();
            //     if($file) {
            //         $path = $file->store('img/ewex-car/driver', 's3');
            //         if ($identification_document) {
            //             $identification_document->delete();
            //         }
            //         Image::create([
            //             'filename'       => basename($path),
            //             'imageable_type' => RegisterDriver::class,
            //             'imageable_id'   => $id,
            //             'url'            => Storage::disk('s3')->url($path),
            //             'tag'            => 'driver',
            //             'category'       =>  null,
            //         ]);
            //     }
            // }
            $file = $request->file('license');
            if ($request->hasFile('license')) {
                $license = Image::where('imageable_type', RegisterDriver::class)
                    ->where('imageable_id', $id)
                    ->where('tag', 'driver')
                    ->first();
                if($file) {
                    $path = $file->store('img/ewex-car/driver', 's3');
                    if ($license) {
                        $license->delete();
                    }
                    Image::create([
                        'filename'       => basename($path),
                        'imageable_type' => RegisterDriver::class,
                        'imageable_id'   => $id,
                        'url'            => Storage::disk('s3')->url($path),
                        'tag'            => 'driver',
                        'category'       =>  null,
                    ]);
                }
            }
            $response = RegisterDriver::with(
                'country',
                'model',
                'brand',
                'images'
            )
            ->where('id', $id)
            ->first();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => "error interno",
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "Driver actualizado Correctamente!",
            "response"      => DriversResource::make($response),
        ]);
    }
}
