<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('document')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->unsignedBigInteger('country_id');
            $table->integer('code_phone');
            $table->unsignedBigInteger('phone');
            $table->unsignedBigInteger('origin_id');
            $table->enum('segmento',['personal','corporativo','otro']);
            $table->enum('tipification', ['final','abierta']);
            $table->dateTime('calender')->nullable();
            $table->string('observe')->nullable();
            $table->unsignedBigInteger('status_id');
            $table->timestamps();

            $table->foreign('status_id')->references('id')->on('statuses');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('origin_id')->references('id')->on('origins');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
}
