<?php
namespace App\Entities;

/**
 * Class Jeu
 * @package App\Entities
 */
class Jeu
{
    /**
     * @var array
     */
    public $planche;

    /**
     * Jeu constructor.
     */
    function __construct(){
        $pos_rosettes = [0, 2, 10, 14, 16];
        $this->planche = [];
        foreach(range(0, 19) as $index){
            $this->planche[] = new Cellule(in_array($index, $pos_rosettes));
        }
    }
}
