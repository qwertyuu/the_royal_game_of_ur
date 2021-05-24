<?php

namespace App\Http\Controllers;

use App\Bots\Bot;
use App\Bots\FireBot;
use App\Entities\BotMove;
use App\Bots\AlasBot;
use App\Bots\TunehrBot;
use App\Entities\Jeu;
use App\Models\Game;
use App\Models\Move;
use App\Models\PlayerChip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class GameController
 * @package App\Http\Controllers
 */
class GameController extends Controller
{
    private $botStrategies = [
        "alas" => AlasBot::class,
        "tunehr" => TunehrBot::class,
        "fire" => FireBot::class,
    ];

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function userAction(Request $request)
    {
        if (!$request->post('action')) {
            return response();
        }
        $game_id = (int)$request->post('game_id');
        $player = (int)$request->post('player');
        /** @var Game $game */
        $game = Game::query()->find($game_id);
        $autre_player = $player === 1 ? 2 : 1;
        $is_current_player = $game->current_player === $player;
        switch ($request->post('action')) {
            case 'refresh':
                $last_move = (int)$request->post('last_move');
                $json_retour = [
                    'dice' => null,
                    'your_turn' => false,
                    'turn_state' => 'play',
                    'possible_moves' => [],
                    'count' => [
                        'yours' => [
                            'attente' => 0,
                            'out' => 0,
                        ],
                        'other' => [
                            'attente' => 0,
                            'out' => 0,
                        ],
                    ],
                    'moves' => [],
                    'last_move_id' => null,
                    'gagnant' => null,
                ];
                if ($game && !$game->creating) {
                    $json_retour['last_move_id'] = $game->last_move_id;
                    $json_retour['dice'] = $game->current_dice;
                    $jetons_current_player = $this->get_jeton_count($game_id, $player);
                    $json_retour['count']['yours'] = (array)$jetons_current_player;
                    $jetons_other_player = $this->get_jeton_count($game_id, $autre_player);
                    $json_retour['count']['other'] = (array)$jetons_other_player;
                    $result_moves = DB::select('
SELECT
       player_chip.id as player_chip_id,
       new_position,
       player_chip.player as player
FROM move
    LEFT JOIN player_chip ON player_chip.id = player_chip_id
WHERE move.id > :last_move AND move.game_id = :game_id
ORDER BY move.id ASC', [
                        'game_id' => $game_id,
                        'last_move' => $last_move,
                    ]);
                    foreach ($result_moves as $move) {
                        $json_retour['moves'][] = [
                            'new_pos' => $move->new_position,
                            'jeton_id' => $move->player_chip_id,
                            'joueur' => $move->player,
                        ];
                    }
                    if ((int)$jetons_current_player->total === (int)$jetons_current_player->out) {
                        $request->session()->start();
                        $request->session()->flush();
                        $json_retour['gagnant'] = true;
                        Game::query()
                            ->where('id', $game_id)
                            ->update([
                                'winner' => $player,
                                'ended_at' => DB::raw('CURRENT_TIMESTAMP'),
                            ]);
                    }
                    if ((int)$jetons_other_player->total === (int)$jetons_other_player->out) {
                        $request->session()->start();
                        $request->session()->flush();
                        $json_retour['gagnant'] = false;
                    }
                    if ($game->dice_dirty) {
                        $json_retour['turn_state'] = 'dice';
                    }
                    if ($is_current_player) {
                        $json_retour['your_turn'] = true;

                        if ($json_retour['turn_state'] !== 'dice') {
                            $json_retour['possible_moves'] = $this->generate_possible_moves($game_id, $player, $json_retour['dice']);
                            if (count($json_retour['possible_moves']) === 0) {
                                Game::query()
                                    ->where('id', $game_id)
                                    ->update([
                                        'current_player' => $autre_player,
                                        'dice_dirty' => true,
                                    ]);
                                $json_retour['your_turn'] = false;
                            }
                        }
                    } elseif ($game->bot) {
                        // dans ce contexte, "autre_player" est le bot et $player est le seul joueur
                        if ($json_retour['turn_state'] === 'dice') {
                            $this->throw_dice($game_id, $autre_player, $player);
                        } else {
                            /** @var Bot $strategy */
                            $strategy = new $this->botStrategies[$game->bot]();
                            $possible_moves = $this->generate_possible_moves($game_id, $autre_player, $json_retour['dice']);
                            $positions = array_keys($possible_moves);
                            $possible_moves_for_bot = [];
                            foreach ($positions as $position) {
                                $possible_moves_for_bot[] = new BotMove($possible_moves[$position], $position);
                            }
                            $move = $strategy->play(
                                PlayerChip::query()->where("game_id", $game_id)->get(),
                                collect($possible_moves_for_bot),
                                $autre_player,
                                $player,
                            );
                            $this->play_jeton($game_id, $move->jeton_newpos, $player, $move->jeton_joue, $autre_player);
                        }
                    }
                }
                return response(json_encode($json_retour), 200, [
                    'Content-Type' => 'application/json',
                ]);

            case 'roll_dice':
                if ($game->dice_dirty && $is_current_player) {
                    $this->throw_dice($game_id, $player, $autre_player);
                }
                return response("{}", 200, [
                    'Content-Type' => 'application/json',
                ]);

            case 'play':
                if (!$game->creating && $game->waiting && $is_current_player) {
                    $jeton_joue = (int)$request->post('jeton_id');
                    $jeton_newpos = (int)$request->post('new_pos');
                    $this->play_jeton($game_id, $jeton_newpos, $autre_player, $jeton_joue, $player);
                }
                return response("{}", 200, [
                    'Content-Type' => 'application/json',
                ]);
        }
        return response();
    }

    /**
     * @param $game_id
     * @param $position
     * @param null $player
     * @return PlayerChip|null
     */
    private function get_chip_at($game_id, $position, $player = null): ?PlayerChip
    {
        $query = PlayerChip::query()
            ->where('game_id', $game_id)
            ->where('position', $position);
        if ($player) {
            $query->where('player', $player);
        }
        return $query->first();
    }

    private function l_de(): int
    {
        $count = 0;
        foreach (range(0, 3) as $de) {
            $valeur = rand(1, 2);
            if ($valeur === 1) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * @param $game_id
     * @param $player
     * @param $dice
     * @return array
     */
    private function generate_possible_moves($game_id, $player, $dice): array
    {
        if ($dice === 0) {
            return [];
        }

        $result_jeton = PlayerChip::query()
            ->where('game_id', $game_id)
            ->where('player', $player)
            ->get();

        $course = $player === 1
            ? [9,6,3,0,1,4,7,10,12,13,15,18,17,14]
            : [11,8,5,2,1,4,7,10,12,13,15,18,19,16];

        $possible_moves = [];

        /** @var PlayerChip $jeton */
        foreach ($result_jeton as $jeton) {
            $position_jeton = $jeton->position;
            $jeton_courant = $jeton->id;
            if ($position_jeton === -1) {
                $position_tir = $course[$dice - 1];
                if (!$this->get_chip_at($game_id, $position_tir)) {
                    $possible_moves[$position_tir] = $jeton_courant;
                }
            } elseif ($position_jeton > -1) {
                $position_jeton_course = array_search($position_jeton, $course);
                $position_final_tir = $position_jeton_course + $dice;
                if ($position_final_tir === count($course)) {
                    $possible_moves[-2] = $jeton_courant;
                } elseif ($position_final_tir < count($course)) {
                    $position_tir = $course[$position_final_tir];
                    if ($position_tir === 10) {
                        if (!$this->get_chip_at($game_id, $position_tir)) {
                            $possible_moves[$position_tir] = $jeton_courant;
                        }
                    } elseif (!$this->get_chip_at($game_id, $position_tir, $player)) {
                        $possible_moves[$position_tir] = $jeton_courant;
                    }
                }
            }
        }

        return $possible_moves;
    }

    /**
     * @param int $game_id
     * @param int $player
     * @return mixed
     */
    private function get_jeton_count(int $game_id, int $player)
    {
        return DB::selectOne('SELECT SUM(position=-1) AS \'attente\', SUM(position=-2) AS \'out\', SUM(position>-1) AS \'en_jeu\', COUNT(id) AS \'total\' FROM player_chip WHERE player = :player AND game_id = :game_id', [
            'game_id' => $game_id,
            'player' => $player,
        ]);
    }

    /**
     * @param int $jeton_joue
     * @param int $jeton_newpos
     */
    private function update_jeton_position(int $jeton_joue, int $jeton_newpos): void
    {
        PlayerChip::query()
            ->where('id', $jeton_joue)
            ->update(['position' => $jeton_newpos]);
    }

    /**
     * @param int $game_id
     * @param $player_lanceur_du_de
     * @param int $player_a_qui_le_tour_si_de_0
     */
    private function throw_dice(int $game_id, $player_lanceur_du_de, int $player_a_qui_le_tour_si_de_0): void
    {
        $dice = $this->l_de();
        DB::table('dice_throw')->insert([
            'value' => $dice,
            'game_id' => $game_id,
            'player' => $player_lanceur_du_de,
        ]);
        $update_parts = [
            'current_dice' => $dice,
            'id' => $game_id,
        ];
        $possible_moves = $this->generate_possible_moves($game_id, $player_lanceur_du_de, $dice);
        if ($dice === 0 || count($possible_moves) === 0) {
            $update_parts['dice_dirty'] = true;
            $update_parts['current_player'] = $player_a_qui_le_tour_si_de_0;
        } else {
            $update_parts['waiting'] = true;
            $update_parts['dice_dirty'] = false;
        }
        Game::query()
            ->where('id', $game_id)
            ->update($update_parts);
    }

    /**
     * @param int $game_id
     * @param int $jeton_newpos
     * @param int $autre_player
     * @param int $jeton_joue
     * @param $player
     */
    private function play_jeton(int $game_id, int $jeton_newpos, int $autre_player, int $jeton_joue, $player): void
    {
        //TODO: Ajouter une verif que le move est bon pour empêcher la triche
        $jeton_ennemi = $this->get_chip_at($game_id, $jeton_newpos, $autre_player);
        if ($jeton_ennemi && $jeton_newpos >= 0) {
            Move::query()->insert([
                'player_chip_id' => $jeton_ennemi->id,
                'game_id' => $game_id,
                'old_position' => $jeton_newpos,
                'new_position' => -1,
                'rosette' => false,
            ]);
            $this->update_jeton_position($jeton_ennemi->id, -1);
        }

        $rosette = 1;
        if (($jeton_newpos >= 0 && !(new Jeu())->planche[$jeton_newpos]->est_rosette) || ($jeton_newpos == -2)) {
            $rosette = 0;
        }

        /** @var PlayerChip $result_jeton */
        $result_jeton = PlayerChip::query()->find($jeton_joue);
        $this->update_jeton_position($jeton_joue, $jeton_newpos);

        Move::query()->insert([
            'player_chip_id' => $jeton_joue,
            'game_id' => $game_id,
            'old_position' => $result_jeton->position,
            'new_position' => $jeton_newpos,
            'rosette' => $rosette,
        ]);

        Game::query()
            ->where('id', $game_id)
            ->update([
                'last_move_id' => DB::getPdo()->lastInsertId(),
                'current_player' => $rosette === 0 ? $autre_player : $player,
                'waiting' => false,
                'dice_dirty' => true,
            ]);
    }
}
