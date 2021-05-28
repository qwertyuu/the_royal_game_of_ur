<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ComputeWinners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::update('UPDATE game SET
winner = CASE WHEN (SELECT AVG(position)
                    FROM player_chip
                    where player = 1
                      and player_chip.game_id = game.id) = -2 THEN 1 WHEN
                      (SELECT AVG(position)
                       FROM player_chip
                       where player = 2
                         and player_chip.game_id = game.id) = -2
                  THEN 2 END');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Cannot rollback sadly, this action is destructive
    }
}
