<?php
namespace Thunder\Shortcode\Event;

use Thunder\Shortcode\Shortcode\ParsedShortcodeInterface;
use Thunder\Shortcode\Shortcode\ProcessedShortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
class FilterShortcodesEvent
{
    private $parent;
    private $shortcodes;

    public function __construct(array $shortcodes, ProcessedShortcode $parent = null)
    {
        $this->parent = $parent;
        $this->shortcodes = $shortcodes;
    }

    /**
     * @return ParsedShortcodeInterface[]
     */
    public function getShortcodes()
    {
        return $this->shortcodes;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setShortcodes(array $shortcodes)
    {
        $this->shortcodes = $shortcodes;
    }
}
