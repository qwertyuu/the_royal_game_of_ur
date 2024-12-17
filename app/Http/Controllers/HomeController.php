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
    public function export(Request $request)
    {
        $type = $request->get('type');

        if ($type) {
            $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Content-type'        => 'text/csv',
                'Content-Disposition' => "attachment; filename=$type.csv",
                'Expires'             => '0',
                'Pragma'              => 'public'
            ];
            $list = DB::table($type)->get()->toArray();

            $list = array_map(function ($value) {
                return (array)$value;
            }, $list);

            # add headers for each column in the CSV download
            array_unshift($list, array_keys($list[0]));

            $callback = function () use ($list) {
                $FH = fopen('php://output', 'w');
                foreach ($list as $row) {
                    fputcsv($FH, $row);
                }
                fclose($FH);
            };

            return response()->stream($callback, 200, $headers);
        }        
        return response();
    }

    /**
     * @param Request $request
     * @return Response|View|Application|ResponseFactory
     */
    public function index(Request $request)
    {
        return redirect()->to('https://ur.raphaelcote.com');
    }

    /**
     * @param int $nb_jetons_partie
     * @param $game_id
     */
    private function initialize_tokens(int $nb_jetons_partie, $game_id): void
    {
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
    }
}
