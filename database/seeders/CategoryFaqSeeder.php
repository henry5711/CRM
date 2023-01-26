<?php

namespace Database\Seeders;

use App\Models\CategoryFaq;
use Illuminate\Database\Seeder;

class CategoryFaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $category=new CategoryFaq();
        $category->title = 'Nosotros';
        $category->body = '<p> Conoce a EwexCar y aprende sobre nuestra filosofía y compromiso contigo y el bienestar económico y social de nuestro entorno.</p>';
        $category->save();

        $category=new CategoryFaq();
        $category->title = 'Plataforma';
        $category->body = '<p> Descubre cómo funciona nuestra plataforma y las herramientas y beneficios únicos que esta ofrece a través de diferentes dispositivos.</p>';
        $category->save();

        $category=new CategoryFaq();
        $category->title = 'Financiamiento';
        $category->body = '<p> Aprende cómo funciona nuestro simulador de financiamiento y cómo puedes financiar el automotor que desees.</p>';
        $category->save();

        $category=new CategoryFaq();
        $category->title = 'Plan de ahorro';
        $category->body = '<p> Conoce cómo puedes optar por nuestros planes de ahorro y definir tus cuotas y modalidades de pago con las mejores flexibilidades y beneficios.</p>';
        $category->save();

        $category=new CategoryFaq();
        $category->title = 'Vende';
        $category->body = '<p> Conoce todos los detalles acerca del proceso de venta de automotores y cómo puedes publicar tu vehículo a través de nuestra plataforma.</p>';
        $category->save();

        $category=new CategoryFaq();
        $category->title = 'Compra';
        $category->body = '<p> Resuelve todas tus dudas acerca de nuestro proceso de compra y disfruta de la mejor experiencia al adquirir tu vehículo.</p>';
        $category->save();

        $category=new CategoryFaq();
        $category->title = 'Clientes';
        $category->body = '<p> Encuentra la respuesta que buscas acerca de tu interacción personal con nuestros servicios.</p>';
        $category->save();

        $category=new CategoryFaq();
        $category->title = 'Automotores';
        $category->body = '<p> Conoce los diferentes automotores que puedes adquirir a través de nuestra plataforma.</p>';
        $category->save();
    }
}
