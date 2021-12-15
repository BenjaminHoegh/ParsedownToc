<?php

namespace BenjaminHoegh\ParsedownToc;

use Erusev\Parsedown\State;
use Erusev\Parsedown\StateBearer;

final class ParsedownToc implements StateBearer
{
    /** @var State */
    private $State;
    
    public function __construct(StateBearer $StateBearer = null)
    {
        $StateBearer = Tocs::from($StateBearer ?? new State);
    
        $this->State = $StateBearer->state();
    }
    
    public function state(): State
    {
        return $this->State;
    }
    
    /** @return self */
    public static function from(StateBearer $StateBearer)
    {
        return new self($StateBearer);
    }
}