<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class statusClientNewsSedder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $status              = new Status();
        $status->name        = "En finalizacion";
        $status->description = "Clientes";
        $status->save();
    }
}
