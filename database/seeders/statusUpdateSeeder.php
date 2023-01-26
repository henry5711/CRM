<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class statusUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $status              = Status::where('id',17)->first();
        $status->name        = "en verificacion KYC";
        $status->save();

        $status              = Status::where('id',18)->first();
        $status->name        = "Rechazado KYC";
        $status->save();

        $status              = new Status();
        $status->name        = "Verificado KYC";
        $status->description = "Clientes";
        $status->save();

        $status              =  Status::where('id',19)->first();
        $status->name        = "Ewexcar Plan Pendiente deposito";
        $status->save();

        $status              =  Status::where('id',20)->first();
        $status->name        = "Ewexcar Plan Caducado";
        $status->save();

        $status              = new Status();
        $status->name        = "Ewexcar Plan vigente";
        $status->save();

        $status              = new Status();
        $status->name        = "Ewexcar Plan cancelado";
        $status->save();

        $status              = new Status();
        $status->name        = "Ewexcar Plan En finalizacion";
        $status->save();


    }
}
