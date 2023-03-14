<?php

namespace App\Bots;

use App\Entities\BotMove;
use App\Models\PlayerChip;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

/**
 * Class ExpectoBot
 * @package App\Bots
 */
class ExpectoBot implements Bot
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
        $game_positions = [];
        for ($i = 0; $i < 20; $i++) {
            $game_positions[$i] = "-";
        }
            
        $bot_pawn_in_play = 0;
        $player_pawn_in_play = 0;

        $payload_per_pawn = [];
        $pawn_ids = [];

        /** @var PlayerChip $player_chip */
        foreach ($player_chips as $player_chip) {
            if ($player_chip->position < 0) {
                continue;
            }
            if ($player_chip->player === $bot_id) {
                $game_positions[$player_chip->position] = "L";
                $bot_pawn_in_play++;
            } else {
                $game_positions[$player_chip->position] = "D";
                $player_pawn_in_play++;
            }
        }

        // Make this string from $game_positions L-- --- --- --- .-. .-. --- ---
        $game_positions_string = "";
        for ($i = 0; $i < 20; $i++) {
            if ($i === 12 || $i === 13) {
                $game_positions_string .= ".";
            }
            $game_positions_string .= $game_positions[$i];
            if ($i === 12 || $i === 13) {
                $game_positions_string .= ".";
            }
            if ($i === 2 || $i === 5 || $i === 8 || $i === 11 || $i === 12 || $i === 13 || $i === 16) {
                $game_positions_string .= " ";
            }
        }

        $pos_to_x = [
            0, 1, 2,
            0, 1, 2,
            0, 1, 2,
            0, 1, 2,
               1, 
               1,
            0, 1, 2,
            0, 1, 2,
        ];

        $pos_to_y = [
            0, 0, 0,
            1, 1, 1,
            2, 2, 2,
            3, 3, 3,
               4,
               5,
            6, 6, 6,
            7, 7, 7,
        ];

        foreach ($possible_moves as $possible_move) {
            // find the move in $player_chips
            $player_chip = $player_chips->where('id', $possible_move->jeton_joue)->first();
            $x = 0;
            $y = 0;
            if ($player_chip->position === -1) {
                if ($player_chip->player === $bot_id) {
                    $x = 0;
                    $y = 4;
                } else {
                    $x = 2;
                    $y = 4;
                }
            } else {
                $x = $pos_to_x[$player_chip->position];
                $y = $pos_to_y[$player_chip->position];
            }
            $payload_per_pawn[] = [
                'game' => $game_positions_string,
                'light_score' => $bot_pawn_out,
                'dark_score' => $player_pawn_out,
                'roll' => $dice,
                'light_left' => $pawn_per_player - $bot_pawn_out - $bot_pawn_in_play,
                'dark_left' => $pawn_per_player - $player_pawn_out - $player_pawn_in_play,
                'x' => $x,
                'y' => $y,
                'light_turn' => true,
            ];
            $pawn_ids[] = $player_chip->id;
        }

        $response = $this->guzzleClient->post(config('ur_expectimax.baseurl') . '/infer', [
            'body' => json_encode($payload_per_pawn),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        $decoded_response = json_decode($response->getBody(), true);
        $utilities = $decoded_response['utilities'];

        // Get the top utilities index and pick that move
        $ai_picked_move_index = array_search(max($utilities), $utilities, true);
        $ai_picked_move = $pawn_ids[$ai_picked_move_index];
        return $possible_moves->where('jeton_joue', $ai_picked_move)->first();
    }
}
