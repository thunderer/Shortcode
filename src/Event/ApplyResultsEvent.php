<?php
namespace Thunder\Shortcode\Event;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
class ApplyResultsEvent
{
    private $shortcode;
    private $text;
    private $replaces;
    private $result;

    public function __construct(ShortcodeInterface $shortcode = null, $text, array $replaces)
    {
        $this->shortcode = $shortcode;
        $this->text = $text;
        $this->replaces = $replaces;
        $this->result = null;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getReplaces()
    {
        return $this->replaces;
    }

    public function getShortcode()
    {
        return $this->shortcode;
    }

    public function setResult($result)
    {
        $this->result = $result;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function hasResult()
    {
        return null !== $this->result;
    }
}
