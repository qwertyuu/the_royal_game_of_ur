<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Laravel\Lumen\Application;
use Laravel\Lumen\Http\ResponseFactory;

/**
 * Class HomeController
 * @package App\Http\Controllers
 */
class HomeController extends Controller
{
    /**
     * @param Request $request
     * @return Response|View|Application|ResponseFactory
     */
    public function index(Request $request)
    {
        $request->session()->start();

        $host = $request->server('HTTP_HOST');
        $uri_sans_get = explode('?', $request->server('REQUEST_URI'))[0];
        if ($request->get('reset')) {
            $request->session()->flush();
            return response("<meta http-equiv=\"Refresh\" content=\"0;http://<?php echo $host . $uri_sans_get; ?>\">");
        }
        $action = $request->get('action');
        if ($action) {
            switch ($action) {
                case 'new':
                    $bindings = ['nb_jetons' => 5];

                    if (in_array($request->get('nb_jetons'), [3, 5, 7])) {
                        $bindings['nb_jetons'] = $request->get('nb_jetons');
                    }
                    DB::insert('INSERT INTO game (en_creation, nb_jetons) VALUES (1, :nb_jetons)', $bindings);
                    $lastId = DB::getPdo()->lastInsertId();
                    $request->session()->put('game_id', $lastId);
                    $request->session()->put('en_creation', true);
                    $request->session()->put('joueur', 1);

                    break;

                case 'refresh':
                    //on va voir si notre game a été créée
                    if ($request->session()->get('en_creation') === True) {
                        $result = DB::selectOne('SELECT en_creation FROM game WHERE game_id = :game_id', [
                            'game_id' => $request->session()->get('game_id'),
                        ]);

                        if ($result && (int)$result->en_creation === 0) {
                            $request->session()->put('en_creation', false);
                        }
                    }
                    break;

                case 'join':
                    if (!$request->get('game_id')) {
                        break;
                    }
                    $result = DB::selectOne('SELECT game_id, nb_jetons, en_creation FROM game WHERE game_id = :game_id', [
                        'game_id' => $request->get('game_id'),
                    ]);
                    //game already exists
                    if ($result) {
                        // game is already started and the user is just refreshing their page
                        if ((int)$result->en_creation === 0) {
                            break;
                        }
                        $nb_jetons_partie = $result->nb_jetons;
                        DB::update('UPDATE game SET en_creation=0,joueur_courant=1,en_attente=0 WHERE game_id = :game_id', [
                            'game_id' => $request->get('game_id'),
                        ]);

                        $game_id = $request->get('game_id');

                        $request->session()->put('game_id', $game_id);
                        $request->session()->put('joueur', 2);

                        $values_insert = [];
                        $prepared = [];
                        foreach (range(0, $nb_jetons_partie - 1) as $jeton_index) {
                            $values_insert[] = "(?, 1, -1)";
                            $values_insert[] = "(?, 2, -1)";
                            $prepared[] = $game_id;
                            $prepared[] = $game_id;
                        }
                        $values_implode = implode(',', $values_insert);
                        DB::insert('INSERT INTO joueur_jeton (jeton_fk_game_id, jeton_joueur_position, jeton_position) VALUES ' . $values_implode, $prepared);
                        $request->session()->put('en_creation', false);
                    } else {
                        $request->session()->forget([
                            'game_id',
                            'joueur',
                            'en_creation',
                        ]);
                    }
                    break;
            }
        } else {
            $request->session()->flush();
            return view('main.menu');
        }

        if ($request->session()->get('en_creation') === false) {
            return view('main.game', [
                'joueur' => $request->session()->get('joueur'),
                'game_id' => $request->session()->get('game_id'),
                'host' => $host,
                'uri_sans_get' => $uri_sans_get,
            ]);
        } elseif ($request->session()->get('en_creation')) {
            return view('main.creating_game', [
                'game_id' => $request->session()->get('game_id'),
                'host' => $host,
                'uri_sans_get' => $uri_sans_get,
            ]);
        }
    }
}
