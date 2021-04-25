<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Move
 * @package App\Models
 * @property int $id
 * @property int $player_chip_id
 * @property int $game_id
 * @property int $position
 * @property int $old_position
 * @property int $new_position
 * @property bool $rosette
 */
class Move extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'move';

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
        'player_chip_id' => 'int',
        'game_id' => 'int',
        'position' => 'int',
        'old_position' => 'int',
        'new_position' => 'int',
        'rosette' => 'bool',
    ];
}
