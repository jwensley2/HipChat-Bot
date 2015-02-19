<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRoomsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rooms', function ($table) {
            /** @var Blueprint $table */
            $table->increments('id');
            $table->integer('room_id');
            $table->integer('group_id');
            $table->integer('token_id')->unsigned();

            $table->foreign('token_id')->references('id')->on('oauth_tokens');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oauth_tokens');
    }
}
