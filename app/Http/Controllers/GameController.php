<?php

namespace App\Http\Controllers;

use App\Entities\Jeu;
use App\Entities\Joueur;
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
        if ($request->post('action') === null) {
            return response();
        }
        $game_id = (int)$request->post('game_id');
        $player = (int)$request->post('player');
        $autre_player = $player === 1 ? 2 : 1;
        $joueur = new Joueur($player);
        switch ($request->post('action')) {
            case 'refresh':
                $last_move = (int)$request->post('last_move');
                $result = DB::selectOne('SELECT en_attente, joueur_courant, en_creation, last_move_id FROM game WHERE game_id = :game_id', [
                    'game_id' => $game_id,
                ]);
                $json_retour = ['state' => 'wait'];
                if ($result->en_creation) {
                    $json_retour['en_cours'] = false;
                } else {
                    if ($last_move !== $result->last_move_id) {
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
                        $json_retour['last_move_id'] = $result->last_move_id;

                        $result_jetons = DB::selectOne('SELECT SUM(jeton_position=-1) AS \'attente\', SUM(jeton_position=-2) AS \'out\', SUM(jeton_position>-1) AS \'en_jeu\', COUNT(jeton_id) AS \'total\' FROM joueur_jeton WHERE jeton_joueur_position = :player AND jeton_fk_game_id = :game_id', [
                            'game_id' => $game_id,
                            'player' => $player,
                        ]);
                        $json_retour['count']['yours'] = (array)$result_jetons;
                        if ($result_jetons->total === (int)$result_jetons->out) {
                            $request->session()->start();
                            $request->session()->flush();
                            $json_retour['gagnant'] = 'toi';
                        }
                        $result_jetons = DB::selectOne('SELECT SUM(jeton_position=-1) AS \'attente\', SUM(jeton_position=-2) AS \'out\', SUM(jeton_position>-1) AS \'en_jeu\', COUNT(jeton_id) AS \'total\' FROM joueur_jeton WHERE jeton_joueur_position = :player AND jeton_fk_game_id = :game_id', [
                            'game_id' => $game_id,
                            'player' => $autre_player,
                        ]);
                        if ($result_jetons->total === (int)$result_jetons->out) {
                            $request->session()->start();
                            $request->session()->flush();
                            $json_retour['gagnant'] = 'pas toi';
                        }
                        $json_retour['count']['other'] = (array)$result_jetons;
                    }
                    $json_retour['en_cours'] = true;
                    $joueur_en_cours = $result->joueur_courant;

                    if ($joueur_en_cours === (int)$player) {

                        $json_retour['state'] = 'update';
                        $json_retour['your_turn'] = true;

                        if ($result->en_attente === 0) {
                            $json_retour['de'] = $this->l_de();
                            if ($json_retour['de'] !== 0) {
                                DB::update('UPDATE game SET en_attente = 1, last_de=:de WHERE game_id = :game_id', [
                                    'game_id' => $game_id,
                                    'de' => $json_retour['de'],
                                ]);
                            } else {
                                DB::update('UPDATE game SET last_de=:de WHERE game_id = :game_id', [
                                    'game_id' => $game_id,
                                    'de' => $json_retour['de'],
                                ]);
                            }
                        } else {
                            $result = DB::selectOne('SELECT last_de FROM game WHERE game_id = :game_id', [
                                'game_id' => $game_id,
                            ]);
                            $json_retour['de'] = $result->last_de;
                        }
                        $this->generate_possible_moves($game_id, $player, $joueur, $json_retour);
                        if (count($json_retour['possible_moves']) === 0) {
                            $autre_player = $player == 1 ? 2 : 1;
                            DB::update('UPDATE game SET joueur_courant = :autre_player WHERE game_id = :game_id', [
                                'autre_player' => $autre_player,
                                'game_id' => $game_id,
                            ]);
                            unset($json_retour['your_turn']);
                        }
                    }
                }
                return response(json_encode($json_retour), 200, [
                    'Content-Type' => 'application/json',
                ]);

            case 'jouer':
                $jeu = new Jeu();
                $jeton_joue = (int)$request->post('jeton_id');
                $jeton_newpos = (int)$request->post('new_pos');
                //TODO: Ajouter une verif que le move est bon pour empêcher la triche
                $json_retour = ['state' => 'bad'];

                $result = DB::selectOne('SELECT en_attente, joueur_courant, en_creation FROM game WHERE game_id = :game_id', [
                    'game_id' => $game_id,
                ]);

                if ($result->en_creation === 0 && $result->en_attente === 1 && $result->joueur_courant === $player) {
                    $result_jeton = DB::selectOne('SELECT jeton_position FROM joueur_jeton WHERE jeton_id = :jeton_id', [
                        'jeton_id' => $jeton_joue,
                    ]);
                    $old_pos = $result_jeton->jeton_position;

                    $jeton_ennemi = $this->ya_tu_un_jeton_a($game_id, $jeton_newpos, $autre_player, true);
                    if ($jeton_ennemi && $jeton_newpos >= 0) {
                        //lol umad
                        DB::insert('INSERT INTO move (move_fk_jeton_id, move_fk_game_id, move_last_position, move_new_position, rosette) VALUES (:jeton_id, :game_id, :last_position, -1, 0)', [
                            'jeton_id' => $jeton_ennemi->jeton_id,
                            'game_id' => $game_id,
                            'last_position' => $jeton_newpos,
                        ]);

                        DB::update('UPDATE joueur_jeton SET jeton_position=-1 WHERE jeton_id = :jeton_id', [
                            'jeton_id' => $jeton_ennemi->jeton_id,
                        ]);
                    }

                    DB::update('UPDATE joueur_jeton SET jeton_position=:position WHERE jeton_id = :jeton_id', [
                        'position' => $jeton_newpos,
                        'jeton_id' => $jeton_joue,
                    ]);
                    $rosette = 1;
                    if (($jeton_newpos >= 0 && !$jeu->planche[$jeton_newpos]->est_rosette) || ($jeton_newpos == -2)) {
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

                    DB::update('UPDATE game SET last_move_id=:move_id, joueur_courant = :player, en_attente=0 WHERE game_id = :game_id', [
                        'move_id' => $lastId_move,
                        'game_id' => $game_id,
                        'player' => $rosette === 0 ? $autre_player : $player,
                    ]);
                    $json_retour['state'] = 'good';

                }
                return response(json_encode($json_retour), 200, [
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
            if ($valeur == 1) {
                $count++;
            }
        }
        return $count;
    }

    private function generate_possible_moves($game_id, $player, $joueur, &$json_retour)
    {
        $result_jeton = DB::select('SELECT jeton_id, jeton_position FROM joueur_jeton WHERE jeton_fk_game_id = :game_id AND jeton_joueur_position = :player', [
            'game_id' => $game_id,
            'player' => $player,
        ]);
        $json_retour['possible_moves'] = [];
        if ($json_retour['de'] !== 0) {
            foreach ($result_jeton as $jeton) {
                $position_jeton = $jeton->jeton_position;
                $jeton_courant = $jeton->jeton_id;
                if ($position_jeton == -1) {
                    $position_tir = $joueur->course[$json_retour['de'] - 1];
                    if (!$this->ya_tu_un_jeton_a($game_id, $position_tir)) {
                        $json_retour['possible_moves'][$position_tir] = $jeton_courant;
                    }
                } elseif ($position_jeton > -1) {
                    $position_jeton_course = array_search($position_jeton, $joueur->course);
                    $position_final_tir = $position_jeton_course + ($json_retour['de']);
                    if ($position_final_tir === count($joueur->course)) {
                        $json_retour['possible_moves'][-2] = $jeton_courant;
                    } elseif ($position_final_tir < count($joueur->course)) {
                        $position_tir = $joueur->course[$position_final_tir];
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
    }
}
