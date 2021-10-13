<?php

namespace App\Bots;

use App\Entities\Jeu;
use Illuminate\Support\Collection;

trait CommonBotTrait
{
    /**
     * @param Collection $player_chips
     * @param int $player_id
     * @return Collection
     */
    public function get_enemy_token_positions(Collection $player_chips, int $player_id): Collection
    {
        return $player_chips
            ->where('player', $player_id)
            ->pluck('position');
    }

    /**
     * @param Collection $possible_moves
     * @param Collection $enemy_token_positions
     * @return Collection
     */
    public function get_possible_enemy_positions(Collection $possible_moves, Collection $enemy_token_positions): Collection
    {
        return $possible_moves->whereIn('jeton_newpos', $enemy_token_positions);
    }

    /**
     * @param Collection $possible_moves
     * @return Collection
     */
    public function get_possible_rosettes(Collection $possible_moves): Collection
    {
        return $possible_moves->whereIn('jeton_newpos', Jeu::$POS_ROSETTES);
    }

    /**
     * @param Collection $player_chips
     * @param int $player_id
     * @param Collection $possible_moves
     * @return array
     */
    public function get_common(Collection $player_chips, int $player_id, Collection $possible_moves): array
    {
        $enemy_token_positions = $this->get_enemy_token_positions($player_chips, $player_id);
        return [
            $enemy_token_positions,
            $this->get_possible_enemy_positions($possible_moves, $enemy_token_positions),
            $this->get_possible_rosettes($possible_moves),
        ];
    }
}
