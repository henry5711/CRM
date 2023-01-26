<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusTakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $status              = new Status();
        $status->name        = "Finalizada";
        $status->description = "Tak";
        $status->save();

        $status              = new Status();
        $status->name        = "Pendiente";
        $status->description = "Tak";
        $status->save();

        $status              = new Status();
        $status->name        = "En curso";
        $status->description = "Tak";
        $status->save();
    }
}
