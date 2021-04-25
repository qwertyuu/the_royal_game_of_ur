<?php

namespace App\Http\Controllers;

use App\Entities\Jeu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class GameController
 * @package App\Http\Controllers
 */
class GameController extends Controller
{
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
        $game = DB::selectOne('SELECT * FROM game WHERE game_id = :game_id', [
            'game_id' => $game_id,
        ]);
        $autre_player = $player === 1 ? 2 : 1;
        $is_current_player = (int)$game->joueur_courant === $player;
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
                if ($game && !(int)$game->en_creation) {
                    $json_retour['last_move_id'] = (int)$game->last_move_id;
                    $json_retour['dice'] = $game->last_de === null ? null : (int)$game->last_de;
                    $jetons_current_player = $this->get_jeton_count($game_id, $player);
                    $json_retour['count']['yours'] = (array)$jetons_current_player;
                    $jetons_other_player = $this->get_jeton_count($game_id, $autre_player);
                    $json_retour['count']['other'] = (array)$jetons_other_player;
                    $result_moves = DB::select('SELECT move_id, move_fk_jeton_id, move_new_position, jeton_joueur_position FROM move LEFT JOIN joueur_jeton ON jeton_id = move_fk_jeton_id WHERE move_id > :last_move AND move_fk_game_id = :game_id ORDER BY move_id ASC', [
                        'game_id' => $game_id,
                        'last_move' => $last_move,
                    ]);
                    foreach ($result_moves as $move) {
                        $json_retour['moves'][] = [
                            'new_pos' => $move->move_new_position,
                            'jeton_id' => $move->move_fk_jeton_id,
                            'joueur' => $move->jeton_joueur_position
                        ];
                    }
                    if ((int)$jetons_current_player->total === (int)$jetons_current_player->out) {
                        $request->session()->start();
                        $request->session()->flush();
                        $json_retour['gagnant'] = true;
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
                            $this->generate_possible_moves($game_id, $player, $json_retour);
                            if (count($json_retour['possible_moves']) === 0) {
                                DB::update('UPDATE game SET joueur_courant = :autre_player WHERE game_id = :game_id', [
                                    'autre_player' => $autre_player,
                                    'game_id' => $game_id,
                                ]);
                                $json_retour['your_turn'] = false;
                            }
                        }
                    }
                }
                return response(json_encode($json_retour), 200, [
                    'Content-Type' => 'application/json',
                ]);

            case 'roll_dice':
                if ($game->dice_dirty && $is_current_player) {
                    $dice = $this->l_de();
                    $update_parts = [
                        'last_de = :de',
                    ];
                    $bindings = [
                        'game_id' => $game_id,
                        'de' => $dice,
                    ];
                    if ($dice === 0) {
                        $update_parts = [
                            ...$update_parts,
                            'dice_dirty = true',
                            'joueur_courant = :autre_player',
                        ];
                        $bindings['autre_player'] = $autre_player;
                    } else {
                        $update_parts = [
                            ...$update_parts,
                            'en_attente = 1',
                            'dice_dirty = false',
                        ];
                    }
                    $query = 'UPDATE game SET ' . implode(', ', $update_parts) . ' WHERE game_id = :game_id';
                    DB::update($query, $bindings);
                }
                return response("{}", 200, [
                    'Content-Type' => 'application/json',
                ]);

            case 'play':
                $jeton_joue = (int)$request->post('jeton_id');
                $jeton_newpos = (int)$request->post('new_pos');
                //TODO: Ajouter une verif que le move est bon pour empêcher la triche

                if ((int)$game->en_creation === 0 && (int)$game->en_attente === 1 && $is_current_player) {
                    $result_jeton = DB::selectOne('SELECT jeton_position FROM joueur_jeton WHERE jeton_id = :jeton_id', [
                        'jeton_id' => $jeton_joue,
                    ]);
                    $old_pos = (int)$result_jeton->jeton_position;

                    $jeton_ennemi = $this->ya_tu_un_jeton_a($game_id, $jeton_newpos, $autre_player, true);
                    if ($jeton_ennemi && $jeton_newpos >= 0) {
                        DB::insert('INSERT INTO move (move_fk_jeton_id, move_fk_game_id, move_last_position, move_new_position, rosette) VALUES (:jeton_id, :game_id, :last_position, -1, 0)', [
                            'jeton_id' => $jeton_ennemi->jeton_id,
                            'game_id' => $game_id,
                            'last_position' => $jeton_newpos,
                        ]);
                        $this->update_jeton_position($jeton_ennemi->jeton_id, -1);
                    }

                    $this->update_jeton_position($jeton_joue, $jeton_newpos);
                    $rosette = 1;
                    if (($jeton_newpos >= 0 && !(new Jeu())->planche[$jeton_newpos]->est_rosette) || ($jeton_newpos == -2)) {
                        $rosette = 0;
                    }
                    DB::insert('INSERT INTO move (move_fk_jeton_id, move_fk_game_id, move_last_position, move_new_position, rosette) VALUES (:jeton_id, :game_id, :last_position, :new_position, :rosette)', [
                        'jeton_id' => $jeton_joue,
                        'game_id' => $game_id,
                        'new_position' => $jeton_newpos,
                        'last_position' => $old_pos,
                        'rosette' => $rosette,
                    ]);
                    $lastId_move = DB::getPdo()->lastInsertId();

                    DB::update('UPDATE game SET last_move_id = :move_id, joueur_courant = :player, en_attente = 0, dice_dirty = true WHERE game_id = :game_id', [
                        'move_id' => $lastId_move,
                        'game_id' => $game_id,
                        'player' => $rosette === 0 ? $autre_player : $player,
                    ]);
                }
                return response("{}", 200, [
                    'Content-Type' => 'application/json',
                ]);
        }
        return response();
    }

    private function ya_tu_un_jeton_a($game_id, $position, $player = null, $return = false)
    {
        $querystring = 'SELECT jeton_id FROM joueur_jeton WHERE jeton_fk_game_id = :game_id AND jeton_position =:postition';
        $bindings = [
            'game_id' => $game_id,
            'postition' => $position,
        ];
        if ($player !== null) {
            $querystring .= ' AND jeton_joueur_position=:player';
            $bindings['player'] = $player;
        }
        $result_jeton = DB::select($querystring, $bindings);
        if (count($result_jeton) > 0 && $return) {
            return $result_jeton[0];
        }
        return count($result_jeton) > 0;
    }

    private function l_de()
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

    private function generate_possible_moves($game_id, $player, &$json_retour)
    {
        $result_jeton = DB::select('SELECT jeton_id, jeton_position FROM joueur_jeton WHERE jeton_fk_game_id = :game_id AND jeton_joueur_position = :player', [
            'game_id' => $game_id,
            'player' => $player,
        ]);
        if ($json_retour['dice'] === 0) {
            return;
        }

        $course = $player === 1
            ? [9,6,3,0,1,4,7,10,12,13,15,18,17,14]
            : [11,8,5,2,1,4,7,10,12,13,15,18,19,16];

        foreach ($result_jeton as $jeton) {
            $position_jeton = (int)$jeton->jeton_position;
            $jeton_courant = $jeton->jeton_id;
            if ($position_jeton === -1) {
                $position_tir = $course[$json_retour['dice'] - 1];
                if (!$this->ya_tu_un_jeton_a($game_id, $position_tir)) {
                    $json_retour['possible_moves'][$position_tir] = $jeton_courant;
                }
            } elseif ($position_jeton > -1) {
                $position_jeton_course = array_search($position_jeton, $course);
                $position_final_tir = $position_jeton_course + ($json_retour['dice']);
                if ($position_final_tir === count($course)) {
                    $json_retour['possible_moves'][-2] = $jeton_courant;
                } elseif ($position_final_tir < count($course)) {
                    $position_tir = $course[$position_final_tir];
                    if ($position_tir === 10) {
                        if (!$this->ya_tu_un_jeton_a($game_id, $position_tir)) {
                            $json_retour['possible_moves'][$position_tir] = $jeton_courant;
                        }
                    } elseif (!$this->ya_tu_un_jeton_a($game_id, $position_tir, $player)) {
                        $json_retour['possible_moves'][$position_tir] = $jeton_courant;
                    }
                }
            }
        }
    }

    /**
     * @param int $game_id
     * @param int $player
     * @return mixed
     */
    private function get_jeton_count(int $game_id, int $player)
    {
        return DB::selectOne('SELECT SUM(jeton_position=-1) AS \'attente\', SUM(jeton_position=-2) AS \'out\', SUM(jeton_position>-1) AS \'en_jeu\', COUNT(jeton_id) AS \'total\' FROM joueur_jeton WHERE jeton_joueur_position = :player AND jeton_fk_game_id = :game_id', [
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
        DB::update('UPDATE joueur_jeton SET jeton_position=:position WHERE jeton_id = :jeton_id', [
            'jeton_id' => $jeton_joue,
            'position' => $jeton_newpos,
        ]);
    }
}
