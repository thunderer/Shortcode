<?php
namespace Thunder\Shortcode\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ParsedShortcode extends AbstractShortcode implements ParsedShortcodeInterface
{
    private $position;
    private $text;

    public function __construct($name, array $parameters, $content, $text, $position)
    {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->content = $content;
        $this->text = $text;
        $this->position = $position;
    }

    public function withContent($content)
    {
        return new self($this->getName(), $this->getParameters(), $content, $this->getText(), $this->getPosition());
    }

    /**
     * Returns position in text
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Returns exact text match
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }
}
