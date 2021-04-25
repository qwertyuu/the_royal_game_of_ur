<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PlayerChip
 * @package App\Models
 * @property int $id
 * @property int $game_id
 * @property int $player
 * @property int $position
 */
class PlayerChip extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'player_chip';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'game_id' => 'int',
        'player' => 'int',
        'position' => 'int',
    ];
}
