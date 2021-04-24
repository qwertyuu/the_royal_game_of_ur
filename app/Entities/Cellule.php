<?php
namespace App\Entities;

/**
 * Class Cellule
 * @package App\Entities
 */
class Cellule
{
    public $est_rosette;

    function __construct($rosette)
    {
        $this->est_rosette = $rosette;
    }
}
