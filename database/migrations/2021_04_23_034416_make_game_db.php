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
            $table->integer('game_id')->autoIncrement();
            $table->boolean('en_creation');
            $table->integer('nb_jetons')->nullable();
            $table->integer('joueur_courant')->nullable();
            $table->boolean('en_attente')->nullable();
            $table->integer('last_move_id')->nullable();
            $table->boolean('gagnee')->nullable();
            $table->integer('gagnant_position')->nullable();
            $table->integer('last_de')->nullable();
        });

        Schema::create('joueur_jeton', function (Blueprint $table) {
            $table->integer('jeton_id')->autoIncrement();
            $table->integer('jeton_fk_game_id');
            $table->integer('jeton_joueur_position');
            $table->integer('jeton_position');
        });

        Schema::create('move', function (Blueprint $table) {
            $table->integer('move_id')->autoIncrement();
            $table->integer('move_fk_jeton_id');
            $table->integer('move_fk_game_id');
            $table->integer('move_last_position');
            $table->integer('move_new_position');
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
        Schema::drop('joueur_jeton');
        Schema::drop('move');
    }
}
