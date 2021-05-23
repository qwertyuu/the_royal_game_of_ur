<?php
namespace App\Entities;

/**
 * Class Jeu
 * @package App\Entities
 */
class Jeu
{
    /**
     * @var array|int[]
     */
    public static array $POS_ROSETTES = [0, 2, 10, 14, 16];

    /**
     * @var array
     */
    public array $planche;

    /**
     * Jeu constructor.
     */
    function __construct(){
        $this->planche = [];
        foreach(range(0, 19) as $index){
            $this->planche[] = new Cellule(in_array($index, self::$POS_ROSETTES));
        }
    }
}
