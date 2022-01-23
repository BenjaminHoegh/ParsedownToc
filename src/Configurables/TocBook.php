<?php

namespace BenjaminHoegh\ParsedownToc\Configurables;

use Erusev\Parsedown\MutableConfigurable;
use Erusev\ParsedownToc\Components\Blocks\Header;
use Erusev\ParsedownToc\Components\Blocks\SetextHeader;

final class TocBook implements MutableConfigurable
{
    /** @var array<string, string> */
    private $book;
    
    /**
     * @param array<string, string> $book
     */
    public function __construct(array $book = [])
    {
        $this->book = $book;
    }
    
    /** @return self */
    public static function initial()
    {
        return new self;
    }
    
    public function addTocElement(int $level, string $text, string $id): void
    {
        $this->book[$id]['text'] = $text;
        $this->book[$id]['level'] = $level;
    }
    
    public function lookup(string $id): ?string
    {
        return $this->book[$id] ?? null;
    }
    
    /** @return array<string, string> */
    public function all()
    {
        return $this->book;
    }
    
    /** @return self */
    public function isolatedCopy(): self
    {
        return new self($this->book);
    }
}
