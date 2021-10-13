<?php

namespace App\Bots;

use App\Entities\BotMove;
use Illuminate\Support\Collection;

/**
 * Class FireBot
 * @package App\Bots
 */
class FireBot implements Bot
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
        $course = $bot_id === 1
            ? [9,6,3,0,1,4,7,10,12,13,15,18,17,14]
            : [11,8,5,2,1,4,7,10,12,13,15,18,19,16];
        $common_course = [1,4,7,10,12,13,15,18];
        foreach ($common_course as $common_position) {
            if ($common_position === 5) {
                continue;
            }
            if ($enemy_token_positions->search($common_position, true)) {
                $position_of_enemy_token_index = array_search($common_position, $course);
                for ($position = $position_of_enemy_token_index; $position >= 0; $position--) {
                    $move = $possible_moves->firstWhere('jeton_newpos', $course[$position]);
                    if ($move) {
                        return $move;
                    }
                }
                if (rand(0, 100) > 50) {
                    break;
                }
            }
        }

        $possible_out = $possible_moves->firstWhere('jeton_newpos', -2);
        if ($possible_out) {
            return $possible_out;
        }
        for ($position = count($course) - 1; $position >= 0; $position--) {
            $move = $possible_moves->firstWhere('jeton_newpos', $course[$position]);
            if ($move) {
                return $move;
            }
        }
        return $possible_moves->random();
    }
}
