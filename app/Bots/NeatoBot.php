<?php

namespace App\Bots;

use App\Entities\BotMove;
use App\Models\PlayerChip;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class NeatoBot
 * @package App\Bots
 */
class NeatoBot implements Bot
{
    use CommonBotTrait;

    /**
     * @var Client
     */
    private Client $guzzleClient;

    /**
     * @param Client $guzzleClient
     */
    public function __construct(Client $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

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
     * @throws \GuzzleHttp\Exception\GuzzleException
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
        $left_course = [9,6,3,0,1,4,7,10,12,13,15,18,17,14];
        $right_course = [11,8,5,2,1,4,7,10,12,13,15,18,19,16];

        $bot_course = $bot_id === 1
            ? $left_course
            : $right_course;

        $player_course = $enemy_id === 1
            ? $left_course
            : $right_course;

        $bot_pawn_course_indices = [];
        $enemy_pawn_course_indices = [];

        $bot_chip_course_position_id_map = [];

        /** @var PlayerChip $player_chip */
        foreach ($player_chips as $player_chip) {
            if ($player_chip->position === -1) {
                if ($player_chip->player === $bot_id) {
                    $bot_chip_course_position_id_map[-1] = $player_chip->id;
                }
                continue;
            }
            if ($player_chip->position === -2) {
                continue;
            }
            if ($player_chip->player === $bot_id) {
                $course_index = array_search($player_chip->position, $bot_course, true);
                $bot_pawn_course_indices[] = $course_index;
                $bot_chip_course_position_id_map[$course_index] = $player_chip->id;
            } else {
                $course_index = array_search($player_chip->position, $player_course, true);
                $enemy_pawn_course_indices[] = $course_index;
            }
        }
        $body = json_encode([
            'pawn_per_player' => $pawn_per_player,
            'ai_pawn_out' => $bot_pawn_out,
            'enemy_pawn_out' => $player_pawn_out,
            'dice' => $dice,
            'ai_pawn_positions' => $bot_pawn_course_indices,
            'enemy_pawn_positions' => $enemy_pawn_course_indices,
        ]);
        $response = $this->guzzleClient->post(config('ur_neat.baseurl') . '/infer', [
            'body' => $body,
        ]);

        $decoded_response = json_decode($response->getBody(), true);
        $ai_picked_pawn = $decoded_response['pawn']; // This is an index of the $bot_pawn_course_indices array
        if ($ai_picked_pawn === -1) {
            $ai_picked_chip_id = $bot_chip_course_position_id_map[$ai_picked_pawn]; // finally, map between relative position and ID so we can find the possible move
        } else {
            $ai_picked_course_position = $bot_pawn_course_indices[$ai_picked_pawn]; // this will tell us where the pawn the AI wants to play is located in the relative course
            $ai_picked_chip_id = $bot_chip_course_position_id_map[$ai_picked_course_position]; // finally, map between relative position and ID so we can find the possible move
        }

        return $possible_moves->firstWhere('jeton_joue', $ai_picked_chip_id);
    }
}
