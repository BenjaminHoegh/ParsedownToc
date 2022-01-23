<?php

namespace BenjaminHoegh\ParsedownToc\Features;

use Erusev\Parsedown\Components\Blocks\Header as CoreHeader;
use Erusev\Parsedown\Components\Blocks\SetextHeader as CoreSetextHeader;
use Erusev\Parsedown\Configurables\BlockTypes;
use Erusev\Parsedown\Configurables\RenderStack;
use Erusev\Parsedown\Html\Renderable;
use Erusev\Parsedown\Html\Renderables\Container;
use Erusev\Parsedown\Html\Renderables\Element;
use Erusev\Parsedown\Html\Renderables\RawHtml;
use Erusev\Parsedown\Html\Renderables\Text;
use Erusev\Parsedown\Html\TransformableRenderable;
use Erusev\Parsedown\Parsedown;
use Erusev\Parsedown\State;
use Erusev\Parsedown\StateBearer;

use BenjaminHoegh\ParsedownToc\Components\Blocks\Toc;
use BenjaminHoegh\ParsedownToc\Components\Blocks\Header;
use BenjaminHoegh\ParsedownToc\Components\Blocks\SetextHeader;
use BenjaminHoegh\ParsedownToc\Configurables\TocBook;

final class Tocs implements StateBearer
{
    /** @var State */
    private $State;

    public function __construct(StateBearer $StateBearer = null)
    {
        $State = ($StateBearer ?? new State)->state();
        
        $BlockTypes = $State->get(BlockTypes::class)
            ->replacing(CoreHeader::class, Header::class)
            ->replacing(CoreSetextHeader::class, SetextHeader::class)
            ->addingMarkedLowPrecedence('[', [Toc::class])
        ;

        $this->State = $State
            ->setting($BlockTypes)
        ;
        
        $RenderStack = $State->get(RenderStack::class)
            ->push(self::renderToc())
        ;
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
    
    /** @return \Closure(Renderable[],State):Renderable[] */
    public static function renderToc()
    {
        return function (array $Rs, State $S): array {
            $TB = $S->get(TocBook::class);
        };
    }
}
