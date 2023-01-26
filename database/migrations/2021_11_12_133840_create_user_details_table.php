<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('tp_document_id')->nullable();
            $table->unsignedBigInteger('gender_id')->nullable();
            $table->unsignedBigInteger('profile_background_image_id')->nullable();
            $table->string('ip')->nullable();
            $table->string('document')->nullable()->unique();
            $table->string('address')->nullable();
            $table->date('birth')->nullable();

            $table->integer('code_phone')->nullable();
            $table->unsignedBigInteger('phone')->nullable()->unique();
            $table->timestamp('phone_verified_at')->nullable()->nullable();
            $table->string('confirmation_code_phone')->unique()->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('tp_document_id')->references('id')->on('tp_documents');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('gender_id')->references('id')->on('genders');
            $table->foreign('user_id')->references('id')->on('users')
                                      ->onDelete('cascade')
                                      ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_details');
    }
}
