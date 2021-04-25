<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\PlayerChip;
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
                    $nb_jetons = 5;

                    if (in_array($request->get('nb_jetons'), [3, 5, 7])) {
                        $nb_jetons = $request->get('nb_jetons');
                    }
                    Game::query()
                        ->insert([
                            'creating' => true,
                            'token_amt' => $nb_jetons,
                        ]);
                    $last_id = DB::getPdo()->lastInsertId();
                    $request->session()->put('game_id', $last_id);
                    $request->session()->put('en_creation', true);
                    $request->session()->put('joueur', 1);

                    break;

                case 'refresh':
                    // check if the game has been created
                    if ($request->session()->get('en_creation') === true) {
                        /** @var Game $result */
                        $result = Game::query()->find($request->session()->get('game_id'));

                        if ($result && !$result->creating) {
                            $request->session()->put('en_creation', false);
                        }
                    }
                    break;

                case 'join':
                    $game_id = $request->get('game_id');
                    if (!$game_id) {
                        break;
                    }
                    /** @var Game $result */
                    $result = Game::query()->find($request->get('game_id'));
                    //game already exists
                    if ($result) {
                        // game is already started and the user is just refreshing their page
                        if (!$result->creating) {
                            break;
                        }
                        $nb_jetons_partie = $result->token_amt;
                        Game::query()
                            ->where('id', $game_id)
                            ->update([
                                'creating' => false,
                                'current_player' => 1,
                                'waiting' => false,
                            ]);

                        $request->session()->put('game_id', $game_id);
                        $request->session()->put('joueur', 2);

                        $chips = [];
                        for ($i = 0; $i < $nb_jetons_partie; $i++) {
                            $chips[] = [
                                'game_id' => $game_id,
                                'player' => 1,
                                'position' => -1,
                            ];
                            $chips[] = [
                                'game_id' => $game_id,
                                'player' => 2,
                                'position' => -1,
                            ];
                        }
                        PlayerChip::query()->insert($chips);
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
