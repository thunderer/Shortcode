<?php
namespace Thunder\Shortcode\Event;

use Thunder\Shortcode\Shortcode\ParsedShortcodeInterface;
use Thunder\Shortcode\Shortcode\ProcessedShortcode;

/**
 * This event is called immediately after receiving shortcodes from parser to
 * make changes before processing with registered handler. Result of this event
 * is used directly in processor.
 *
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
class FilterShortcodesEvent
{
    /** @var ProcessedShortcode|null */
    private $parent;
    /** @var ParsedShortcodeInterface[] */
    private $shortcodes = array();

    /** @param ParsedShortcodeInterface[] $shortcodes */
    public function __construct(array $shortcodes, ProcessedShortcode $parent = null)
    {
        $this->parent = $parent;
        $this->setShortcodes($shortcodes);
    }

    /** @return ParsedShortcodeInterface[] */
    public function getShortcodes()
    {
        return $this->shortcodes;
    }

    /** @return ProcessedShortcode|null */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param ParsedShortcodeInterface[] $shortcodes
     *
     * @return void
     */
    public function setShortcodes(array $shortcodes)
    {
        $this->shortcodes = array();
        foreach($shortcodes as $shortcode) {
            $this->addShortcode($shortcode);
        }
    }

    /**
     * @param ParsedShortcodeInterface $shortcode
     *
     * @return void
     */
    private function addShortcode(ParsedShortcodeInterface $shortcode)
    {
        $this->shortcodes[] = $shortcode;
    }
}
