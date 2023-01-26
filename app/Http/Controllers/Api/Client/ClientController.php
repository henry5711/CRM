<?php

namespace App\Http\Controllers\Api\Client;

use App\Models\Client;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Http\Resources\CrmObserveResource;
use App\Http\Resources\StatusesResource;
use App\Imports\ClientImport;
use App\Jobs\migrationClientJob;
use App\Models\CrmObserve;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            DB::beginTransaction();
            $client = Client::with(['getCountry', 'getStatus', 'getOrigin', 'getUser', 'type', 'typification', 'getObserve'])->get();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.OriginController.index.index.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (isset($request->pag)) {
            return response()->json([
                "message"       => "Clients",
                "response"      => ClientResource::collection($client)->paginate($request->pag),
            ]);
        } else {
            return response()->json([
                "message"       => "Clients",
                "response"      => ClientResource::collection($client),
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreClientRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreClientRequest $request)
    {
        try {
            DB::beginTransaction();

            $verifi = User::where('email', $request->email)->first();
            if ($verifi) {
                return response()->json([
                    "errors" => [
                        "message"       =>  ["ya existe un usuario registrado con este correo"],
                    ]
                ], 422);
            }

            $id =  $this->createClient($request);

            $response = Client::where('id', $id)->first();

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.Client.store.store.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "Se registro una nuevo cliente",
            "response"      => ClientResource::make($response),
        ]);
    }

    protected function createClient($request)
    {

        $client = new Client();
        $client->name = $request->name;
        $client->document = $request->document;
        $client->email = $request->email;
        $client->code_phone = $request->code_phone;
        $client->phone = $request->phone;
        $client->origin_id = $request->origin_id;
        $client->segmento = $request->segmento;
        $client->calender = $request->calender;
        $client->country_id = $request->country_id;
        $client->status_id = $request->status_id;
        $client->user_id = $request->user_id;
        $client->type_typification_id = $request->type_typification_id;
        $client->typification_id = $request->typification_id;
        //$client->phone_ax=$request->phone_ax;

        $client->save();
        return  $client->id;
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            DB::beginTransaction();

            $client = Client::with(['getCountry', 'getStatus', 'getOrigin', 'getUser', 'type', 'typification', 'getObserve'])
                ->where('id', $id)->get();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.Client.show.show.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "detalle de cliente",
            "response"      => ClientResource::make($client),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateClientRequest  $request
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateClientRequest $request, $id)
    {
        $clientValide = Client::where('id', '=', $id)->first();
        if (!$clientValide) {
            return response()->json([
                "errors" => [
                    "message"       => "No es posible editar el cliente",
                ]
            ], 422);
        }
        try {
            DB::beginTransaction();

            $client = Client::findOrFail($id);
            $this->updateClient($client, $request);

            $response = Client::with(['getCountry', 'getStatus', 'getOrigin', 'getObserve'])->where('id', $id)->first();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.Client.update.update.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "client actulizado",
            "response"      => ClientResource::make($response),
        ]);
    }

    protected function updateClient($client, $request)
    {

        $client->name  = $request->name  ? $request->name  :  $client->name;
        $client->document  = $request->document  ? $request->document  : $client->document;
        $client->email  = $request->email  ? $request->email  :  $client->email;
        $client->code_phone  = $request->code_phone ? $request->code_phone  :  $client->code_phone;
        $client->phone  = $request->phone ? $request->phone  :  $client->phone;
        $client->origin_id  = $request->origin_id ? $request->origin_id  :  $client->origin_id;
        $client->segmento  = $request->segmento ? $request->segmento :  $client->segmento;
        $client->calender  = $request->calender ? $request->calender :  $client->calender;
        $client->status_id  = $request->status_id ? $request->status_id :  $client->status_id;

        $client->country_id = $request->country_id ? $request->country_id  :  $client->country_id;
        $client->user_id = $request->user_id ? $request->user_id  :  $client->user_id;
        $client->type_typification_id = $request->type_typification_id ? $request->type_typification_id  :  $client->type_typification_id;
        $client->typification_id = $request->typification_id ? $request->typification_id  : $client->typification_id;
        ///$client->phone_ax=$request->phone_ax? $request->phone_ax  :  $client->phone_ax;
        $client->updated_at  = Carbon::now();
        $client->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $client = Client::where('id', $id)->first();
            if ($client) {
                $client->delete();
            } else {
                return response()->json([
                    "errors" => [
                        "message"       =>  ["no es posible realizar eliminar este cliente"],
                    ]
                ], 422);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.Client.delete.delete.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json([
            "message"       => "client Eliminado",
        ]);
    }

    public function filter(Request $request)
    {
        try {
            DB::beginTransaction();
            $client = Client::with(['getCountry', 'getStatus', 'getOrigin', 'getUser', 'type', 'typification', 'getObserve'])
                ->filtro($request)->get();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.client.filter.filter.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (isset($request->pag)) {
            return response()->json([
                "message"       => "filtro client",
                "response"      => ClientResource::collection($client)->paginate($request->pag),
            ]);
        } else {
            return response()->json([
                "message"       => "filtro client",
                "response"      => ClientResource::collection($client),
            ]);
        }
    }

    public function filterStatusClient(Request $request)
    {

        try {
            DB::beginTransaction();
            $status = Status::filtro($request)->get();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.client.filter.filter.internal_error')],
                    'errors' => $e->getMessage()
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "filtro status",
            "response"      => StatusesResource::collection($status),
        ]);
    }

    public function addobserve(Request $request)
    {
        try {
            DB::beginTransaction();

            $newObserve=new CrmObserve();
            $newObserve->client_id=$request->client_id;
            $newObserve->contend=$request->contend;
            $newObserve->save();

            $response = CrmObserve::find($newObserve->id);

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.Client.addobserve.store.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "Se registro una nueva observacion",
            "response"      => $response,
        ]);

    }


    public function updateObserve(Request $request,$id)
    {
        $obsValide = CrmObserve::where('id', '=', $id)->first();
        if (!$obsValide) {
            return response()->json([
                "errors" => [
                    "message"       => "No es posible editar esta observacion",
                ]
            ], 422);
        }
        try {
            DB::beginTransaction();

            $obs = CrmObserve::findOrFail($id);
            $obs->client_id=$request->client_id  ? $request->client_id  :  $obs->client_id;
            $obs->contend=$request->contend  ? $request->contend  :  $obs->contend;
            $obs->updated_at  = Carbon::now();
            $obs->save();

            $response = CrmObserve::where('id', $id)->first();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.obs.update.update.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            "message"       => "observacion actualizada actulizado",
            "response"      => CrmObserveResource::make($response),
        ]);

    }

    public function destroyObserve($id)
    {
        try {
            DB::beginTransaction();

            $client = CrmObserve::where('id', $id)->first();
            if ($client) {
                $client->delete();
            } else {
                return response()->json([
                    "errors" => [
                        "message"       =>  ["no es posible realizar eliminar esta observacion"],
                    ]
                ], 422);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'code'   => $e->getCode(),
                    'title'  => [__('messages.observe.delete.delete.internal_error')],
                    'errors' => $e->getMessage(),
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json([
            "message"       => "observe Eliminado",
        ]);
    }

    //esta  funcion la hizo henryto sirve para la carga masiva de clientes
    public function importExcel(Request $request)
    {
        Excel::queueImport(new ClientImport,$request->file('file'));
         return response()->json([
            "message"       => "migracion completa",
            "response"      =>true,
        ]);
    }
}
