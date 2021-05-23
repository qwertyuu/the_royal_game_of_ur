<?php

namespace App\Bots;

use App\Entities\BotMove;
use App\Entities\Jeu;
use Illuminate\Support\Collection;

/**
 * Class TunehrBot
 * @package App\Bots
 */
class TunehrBot implements Bot
{
    /**
     * Given a valid boardState and a list of possible moves, outputs one of the possible moves to play
     *
     * @param Collection $player_chips
     * @param Collection $possible_moves
     * @param int $bot_id Always 2 for the moment
     * @param int $player_id Always 1 for the moment
     * @return BotMove
     */
    public function play(Collection $player_chips, Collection $possible_moves, int $bot_id, int $player_id): BotMove
    {
        if ($possible_moves->count() === 1) {
            return $possible_moves->first();
        }
        $enemy_token_positions = $player_chips
            ->where('player', $player_id)
            ->pluck('position');
        $possible_new_positions = $possible_moves->whereIn('jeton_newpos', $enemy_token_positions);
        if ($possible_new_positions->isNotEmpty()) {
            return $possible_new_positions->random();
        }
        $possible_rosette_moves = $possible_moves->whereIn('jeton_newpos', Jeu::$POS_ROSETTES);
        if ($possible_rosette_moves->isNotEmpty()) {
            return $possible_rosette_moves->random();
        }
        return $possible_moves->random();
    }
}
