<?php

namespace App\Entities;

/**
 * Class BotMove
 * @package App\Entities
 */
class BotMove
{
    /**
     * @var int
     */
    public int $jeton_joue;

    /**
     * @var int
     */
    public int $jeton_newpos;

    /**
     * BotMove constructor.
     * @param int $jeton_joue
     * @param int $jeton_newpos
     */
    public function __construct(int $jeton_joue, int $jeton_newpos)
    {
        $this->jeton_joue = $jeton_joue;
        $this->jeton_newpos = $jeton_newpos;
    }
}
