<?php
namespace Thunder\Shortcode\Event;

use Symfony\Component\EventDispatcher\Event;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
class FilterShortcodesEvent extends Event
{
    private $parent;
    private $shortcodes;

    public function __construct(array $shortcodes, ShortcodeInterface $parent = null)
    {
        $this->parent = $parent;
        $this->shortcodes = $shortcodes;
    }

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
