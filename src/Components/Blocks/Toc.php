<?php

namespace BenjaminHoegh\ParsedownToc\Components\Blocks;

use Erusev\Parsedown\Components\Block;
use Erusev\Parsedown\Components\ContinuableBlock;
use Erusev\Parsedown\Components\StateUpdatingBlock;
use Erusev\Parsedown\Html\Renderables\Invisible;
use Erusev\Parsedown\Parsing\Context;
use Erusev\Parsedown\Parsing\Line;
use Erusev\Parsedown\Parsing\Lines;
use Erusev\Parsedown\State;
use BenjaminHoegh\ParsedownToc\Configurables\TocBook;


final class Toc implements Block
{
    /** @var State */
    private $State;

    /** @var string */
    private $title;

    /** @var Lines */
    private $Lines;

    private function __construct(State $State, string $title, Lines $Lines)
    {
        $this->State = $State;
        $this->title = $title;
        $this->Lines = $Lines;
    }

    /**
     * @param Context $Context
     * @param State $State
     * @param Block|null $Block
     * @return static|null
     */
    public static function build(
        Context $Context,
        State $State,
        Block $Block = null
    ) {
        if ($Context->line()->text() == '[toc]') {
            return;
        }

        return null;
    }

    /** @return State */
    public function latestState()
    {
        return $this->State;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function lines(): Lines
    {
        return $this->Lines;
    }

    /**
     * @return Invisible
     */
    public function stateRenderable()
    {
        return new Invisible;
    }
}