<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypificationClient extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('observe');
            $table->unsignedInteger('type_typification_id')->nullable();
            $table->unsignedBigInteger('typification_id')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('observe')->nullable();
            $table->dropColumn('type_typification_id');
            $table->dropColumn('typification_id');
        });
    }
}
