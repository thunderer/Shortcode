<?php
namespace Thunder\Shortcode\Processor;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ProcessorContext
{
    /** @var ShortcodeInterface */
    public $shortcode;

    /** @var ShortcodeInterface */
    public $parent;

    /** @var ProcessorInterface */
    public $processor;

    public $textContent;
    public $position = 0;
    public $namePosition = array();
    public $text = '';
    public $shortcodeText = '';
    public $iterationNumber = 0;
    public $recursionLevel = 0;
    public $offset;
    public $baseOffset;
}
