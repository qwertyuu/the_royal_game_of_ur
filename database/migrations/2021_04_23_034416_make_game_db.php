<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeGameDb extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->boolean('creating');
            $table->integer('token_amt')->nullable();
            $table->integer('current_player')->nullable();
            $table->boolean('waiting')->nullable();
            $table->integer('last_move_id')->nullable();
            $table->integer('winner')->nullable();
            $table->integer('current_dice')->nullable();
        });

        Schema::create('player_chip', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('game_id');
            $table->integer('player');
            $table->integer('position');
        });

        Schema::create('move', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('player_chip_id');
            $table->integer('game_id');
            $table->integer('old_position');
            $table->integer('new_position');
            $table->boolean('rosette');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('game');
        Schema::drop('player_chip');
        Schema::drop('move');
    }
}
