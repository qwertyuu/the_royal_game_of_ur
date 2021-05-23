<?php

namespace App\Bots;

use App\Entities\BotMove;
use Illuminate\Support\Collection;

/**
 * Interface Bot
 * @package App\Bots
 */
interface Bot
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
    public function play(Collection $player_chips, Collection $possible_moves, int $bot_id, int $player_id): BotMove;
}
