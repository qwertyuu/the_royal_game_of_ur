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
     * @param int $pawn_per_player
     * @param int $bot_pawn_out
     * @param int $player_pawn_out
     * @param int $dice
     * @param int $bot_id Always 2 for the moment
     * @param int $enemy_id Always 1 for the moment
     * @return BotMove
     */
    public function play(
        Collection $player_chips,
        Collection $possible_moves,
        int $pawn_per_player,
        int $bot_pawn_out,
        int $player_pawn_out,
        int $dice,
        int $bot_id,
        int $enemy_id
    ): BotMove {
        [$enemy_token_positions, $possible_enemy_positions, $possible_rosette_moves] = $this->get_common($player_chips, $enemy_id, $possible_moves);
        if ($possible_enemy_positions->isNotEmpty()) {
            return $possible_enemy_positions->random();
        }
        if ($possible_rosette_moves->isNotEmpty()) {
            return $possible_rosette_moves->random();
        }
        return $possible_moves->random();
    }
}
