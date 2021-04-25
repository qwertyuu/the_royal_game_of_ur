<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Game
 * @package App\Models
 * @property int $id
 * @property bool $creating
 * @property int $token_amt
 * @property int $current_player
 * @property bool $waiting
 * @property int $last_move_id
 * @property int $winner
 * @property int $current_dice
 * @property bool $dice_dirty
 * @property \DateTime $ended_at
 */
class Game extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'game';

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
        'creating' => 'bool',
        'token_amt' => 'int',
        'current_player' => 'int',
        'waiting' => 'bool',
        'last_move_id' => 'int',
        'winner' => 'int',
        'current_dice' => 'int',
        'dice_dirty' => 'bool',
        'ended_at' => 'timestamp',
    ];
}
