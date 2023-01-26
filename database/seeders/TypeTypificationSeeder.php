<?php

namespace Database\Seeders;

use App\Models\typeTypification;
use Illuminate\Database\Seeder;

class TypeTypificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $type=new typeTypification();
        $type->name="Abierta";
        $type->description="tipificaciones abiertas";
        $type->save();


        $type=new typeTypification();
        $type->name="Final";
        $type->description="tipificaciones cerradas";
        $type->save();
    }
}
