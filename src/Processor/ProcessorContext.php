<?php
namespace Thunder\Shortcode\Processor;

use Thunder\Shortcode\Shortcode\ProcessedShortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ProcessorContext
{
    /**
     * @var ShortcodeInterface
     * @psalm-suppress PropertyNotSetInConstructor
     */
    public $shortcode;

    /** @var ProcessedShortcode|null */
    public $parent = null;

    /**
     * @var ProcessorInterface
     * @psalm-suppress PropertyNotSetInConstructor
     */
    public $processor;

    /**
     * @var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    public $textContent;
    /** @var int */
    public $position = 0;
    /** @psalm-var array<string,int> */
    public $namePosition = array();
    /** @var string */
    public $text = '';
    /** @var string */
    public $shortcodeText = '';
    /** @var int */
    public $iterationNumber = 0;
    /** @var int */
    public $recursionLevel = 0;
    /**
     * @var int
     * @psalm-suppress PropertyNotSetInConstructor
     */
    public $offset;
    /**
     * @var int
     * @psalm-suppress PropertyNotSetInConstructor
     */
    public $baseOffset;

    public function __construct()
    {
    }
}
