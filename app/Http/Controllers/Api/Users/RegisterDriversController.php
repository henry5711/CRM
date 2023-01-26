<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\RegisterDriversRequest;
use App\Models\RegisterDriver;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;


class RegisterDriversController extends Controller
{

    public function register(RegisterDriversRequest $request)
    {
        try {
            DB::beginTransaction();

            $driver = new RegisterDriver();
            $driver->country_id         = $request->nat;
            $driver->vehicle_model_id   = $request->vehicle_model;
            $driver->vehicle_brand_id   = $request->vehicle_make;
            $driver->vehicle_number     = $request->vehicle_number;
            $driver->vehicle_colour     = $request->vehicle_colour;
            $driver->vehicle_type       = $request->vehicle_type;
            $driver->city               = $request->city;
            $driver->postal_code        = $request->postal_code;
            $driver->area               = $request->area;
            $driver->service            = $request->service;
            $driver->name               = $request->name;
            $driver->surname            = $request->surname;
            $driver->date_birth         = $request->date_birth;
            $driver->save();
            $vehicle_photo = $request->file('vehicle_photo');
            if ($request->hasFile('vehicle_photo')) {
                $path = $vehicle_photo->store('img/ewex-car/driver', 's3');

                $image = Image::create([
                    'filename'       => basename($path),
                    'imageable_type' => Dealership::class,
                    'imageable_id'   => $driver->id,
                    'url'            => Storage::disk('s3')->url($path),
                    'tag'            => 'driver',
                    'category'       => null,
                ]);
            }
            $vehicle_plate = $request->file('vehicle_plate');
            if ($request->hasFile('vehicle_plate')) {
                $path = $vehicle_plate->store('img/ewex-car/driver', 's3');
               Image::create([
                    'filename'       => basename($path),
                    'imageable_type' => Dealership::class,
                    'imageable_id'   => $driver->id,
                    'url'            => Storage::disk('s3')->url($path),
                    'tag'            => 'driver',
                    'category'       => null,
                ]);
            }
            $vehicle_plate = $request->file('vehicle_plate');
            if ($request->hasFile('vehicle_plate')) {
                $path = $vehicle_plate->store('img/ewex-car/driver', 's3');
                Image::create([
                    'filename'       => basename($path),
                    'imageable_type' => Dealership::class,
                    'imageable_id'   => $driver->id,
                    'url'            => Storage::disk('s3')->url($path),
                    'tag'            => 'driver',
                    'category'       => null,
                ]);
            }
            $property_card = $request->file('property_card');
            if ($request->hasFile('property_card')) {
                $path = $property_card->store('img/ewex-car/driver', 's3');
                Image::create([
                    'filename'       => basename($path),
                    'imageable_type' => Dealership::class,
                    'imageable_id'   => $driver->id,
                    'url'            => Storage::disk('s3')->url($path),
                    'tag'            => 'driver',
                    'category'       => null,
                ]);
            }
            // $identification_document = $request->file('identification_document');
            // if ($request->hasFile('identification_document')) {
            //     $path = $identification_document->store('img/ewex-car/driver', 's3');
            //     Image::create([
            //         'filename'       => basename($path),
            //         'imageable_type' => Dealership::class,
            //         'imageable_id'   => $driver->id,
            //         'url'            => Storage::disk('s3')->url($path),
            //         'tag'            => 'driver',
            //         'category'       => null,
            //     ]);
            // }
            $license = $request->file('license');
            if ($request->hasFile('license')) {
                $path = $license->store('img/ewex-car/driver', 's3');
                Image::create([
                    'filename'       => basename($path),
                    'imageable_type' => Dealership::class,
                    'imageable_id'   => $driver->id,
                    'url'            => Storage::disk('s3')->url($path),
                    'tag'            => 'driver',
                    'category'       => null,
                ]);
            }
            DB::commit();
            return response()->json(Response::HTTP_OK);

        } catch (ValidationException $ex) {
            return response()->json(
                [
                'data' => [
                    'title'  => $ex->getMessage(),
                    'errors' => collect($ex->errors())->flatten()
                ]
                ], Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json(
                [
                'data' => [
                    'code'        => $ex->getCode(),
                    'title'       => __('errors.server.title'),
                    'description' => $ex->getMessage(),
                ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
