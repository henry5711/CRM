<?php

namespace Database\Seeders;

use App\Models\Typification;
use Illuminate\Database\Seeder;

class TypificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $type=new Typification();
        $type->name="Cliente Prospecto";
        $type->description="posible cliente";
        $type->type_typification_id=1;
        $type->save();

        $type=new Typification();
        $type->name="No contacto";
        $type->description="sin contactar";
        $type->type_typification_id=1;
        $type->save();

        $type=new Typification();
        $type->name="Desea validar legalidad de la empresa";
        $type->description="";
        $type->type_typification_id=1;
        $type->save();


        $type=new Typification();
        $type->name="Volver a llamar";
        $type->description=null;
        $type->type_typification_id=1;
        $type->save();


        $type=new Typification();
        $type->name="Solicita visita presencial";
        $type->description=null;
        $type->type_typification_id=1;
        $type->save();

        $type=new Typification();
        $type->name="Solicita envio de informacion";
        $type->description=null;
        $type->type_typification_id=1;
        $type->save();

        $type=new Typification();
        $type->name="Efectiva";
        $type->description=null;
        $type->type_typification_id=2;
        $type->save();

        $type=new Typification();
        $type->name="No cumple con las politicas del servicio";
        $type->description=null;
        $type->type_typification_id=2;
        $type->save();

        $type=new Typification();
        $type->name="Desconfia no atiende asesor";
        $type->description=null;
        $type->type_typification_id=2;
        $type->save();

        $type=new Typification();
        $type->name="No cumple expectativas";
        $type->description=null;
        $type->type_typification_id=2;
        $type->save();

        $type=new Typification();
        $type->name="No tiene cuota inicial";
        $type->description=null;
        $type->type_typification_id=2;
        $type->save();

        $type=new Typification();
        $type->name="Ya adquirio el credito con otra entidad";
        $type->description=null;
        $type->type_typification_id=2;
        $type->save();

        $type=new Typification();
        $type->name="Medio de trasporte no disponible";
        $type->description=null;
        $type->type_typification_id=2;
        $type->save();

        $type=new Typification();
        $type->name="Ya tiene vehiculo no desea renovar";
        $type->description=null;
        $type->type_typification_id=2;
        $type->save();

        $type=new Typification();
        $type->name="Necesita vehiculo inmediatamente";
        $type->description=null;
        $type->type_typification_id=2;
        $type->save();
    }
}
