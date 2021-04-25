<?php
namespace App\Entities;

/**
 * Class Joueur
 * @package App\Entities
 */
class Joueur
{
    /**
     * @var
     */
    public $joueur_nb;

    /**
     * @var int[]
     */
    public $course;

    /**
     * Joueur constructor.
     * @param $position
     */
    function __construct($position){
        $this->joueur_nb = $position;
        if($position === 1){
            $this->course = [9,6,3,0,1,4,7,10,12,13,15,18,17,14];
        }
        else {
            $this->course = [11,8,5,2,1,4,7,10,12,13,15,18,19,16];
        }
    }
}
