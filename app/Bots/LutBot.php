<?php

namespace App\Bots;

use App\Entities\BotMove;
use App\Models\PlayerChip;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

/**
 * Class LutBot
 * @package App\Bots
 */
class LutBot implements Bot
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

        $pawn_ids = [];

        /** @var PlayerChip $player_chip */
        foreach ($player_chips as $player_chip) {
            if ($player_chip->position < 0) {
                continue;
            }
            if ($player_chip->player === $bot_id) {
                $game_positions[$player_chip->position] = "D";
                $bot_pawn_in_play++;
            } else {
                $game_positions[$player_chip->position] = "L";
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
        $payload_per_pawn = [
            'game' => $game_positions_string,
            'light_score' => $player_pawn_out,
            'dark_score' => $bot_pawn_out,
            'roll' => $dice,
            'dark_left' => $pawn_per_player - $bot_pawn_out - $bot_pawn_in_play,
            'light_left' => $pawn_per_player - $player_pawn_out - $player_pawn_in_play,
            'light_turn' => false,
        ];

        $response = $this->guzzleClient->post(config('ur_lut.baseurl') . '/jsonEndpoint', [
            'body' => json_encode($payload_per_pawn),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
        $response = $response->getBody()->getContents();

        // response format is either null or x,y as a string
        print($response);
        print($possible_moves);
        print($dice);

        if ($response != "null") {
            $response = explode(",", $response);
            // convert x, y to 1D
            // 0, 0 => 2
            // 0, 1 => 1
            // 0, 2 => 0
            // 1, 0 => 5
            // 1, 1 => 4
            // 1, 2 => 3
            // 2, 0 => 8
            // 2, 1 => 7
            // 2, 2 => 6
            $response = $response[0] + 3 * $response[1];
            // 12, 14, 15, 17 does not exist
            // 13 => 12
            // 16 => 13
            // 18 => 14
            // 19 => 15
            // 20 => 16
            // 21 => 17
            // 22 => 18
            // 23 => 19
            $map = [
                13 => 12,
                16 => 13,
                18 => 14,
                19 => 15,
                20 => 16,
                21 => 17,
                22 => 18,
                23 => 19,
            ];
            print($response);
            if (array_key_exists($response, $map)) {
                $response = $map[$response];
            }
            print($response);
            return $possible_moves->where('jeton_newpos', $response)->first();
        } else if ($response == "null") {
            return $possible_moves->where('jeton_newpos', '<', 0)->first();
        }
    }
}
