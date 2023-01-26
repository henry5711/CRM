<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $status              = new Status();
        $status->name        = "Vigente";
        $status->description = "Financiamiento";
        $status->save();

        $status              = new Status();
        $status->name        = "Cancelado";
        $status->description = "Financiamiento";
        $status->save();

        $status              = new Status();
        $status->name        = "Finalizado";
        $status->description = "Financiamiento, Saldo Cliente";
        $status->save();

        $status              = new Status();
        $status->name        = "Aceptado";
        $status->description = "Depósito";
        $status->save();

        $status              = new Status();
        $status->name        = "Rechazado";
        $status->description = "Depósito";
        $status->save();

        $status              = new Status();
        $status->name        = "En verificación";
        $status->description = "Depósito";
        $status->save();

        $status              = new Status();
        $status->name        = "Pagado";
        $status->description = "Reembolsos";
        $status->save();

        $status              = new Status();
        $status->name        = "Proximo Pago";
        $status->description = "Pagos";
        $status->save();

        $status              = new Status();
        $status->name        = "Activo";
        $status->description = "Saldo Cliente";
        $status->save();

        $status              = new Status();
        $status->name        = "Devolución";
        $status->description = "Financiamiento";
        $status->save();

        $status              = new Status();
        $status->name        = "En proceso de compra";
        $status->description = "Financiamiento";
        $status->save();

        $status              = new Status();
        $status->name        = "Completado";
        $status->description = "Financiamiento, Pagos";
        $status->save();

        $status              = new Status();
        $status->name        = "Caducado";
        $status->description = "Financiamiento";
        $status->save();

        $status              = new Status();
        $status->name        = "Pendiente";
        $status->description = "Financiamiento Solicitud";
        $status->save();

    }
}
