<?php

namespace App\Bots;

use App\Entities\BotMove;
use Illuminate\Support\Collection;

/**
 * Class TunehrBot
 * @package App\Bots
 */
class TunehrBot implements Bot
{
    use CommonBotTrait;

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
        [$enemy_token_positions, $possible_enemy_positions, $possible_rosette_moves] = $this->get_common($player_chips, $player_id, $possible_moves);
        if ($possible_enemy_positions->isNotEmpty()) {
            return $possible_enemy_positions->random();
        }
        if ($possible_rosette_moves->isNotEmpty()) {
            return $possible_rosette_moves->random();
        }
        return $possible_moves->random();
    }
}
