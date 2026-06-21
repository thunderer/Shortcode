<?php
namespace Thunder\Shortcode\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ReplacedShortcode extends AbstractShortcode
{
    /** @var string */
    private $replacement;
    /** @var string */
    private $text;
    /** @var int */
    private $offset;

    /** @param string $replacement */
    public function __construct(ParsedShortcodeInterface $shortcode, $replacement)
    {
        $this->name = $shortcode->getName();
        $this->parameters = $shortcode->getParameters();
        $this->content = $shortcode->getContent();
        $this->bbCode = $shortcode->getBbCode();
        $this->text = $shortcode->getText();
        $this->offset = $shortcode->getOffset();

        $this->replacement = $replacement;
    }

    /**
     * @param string $replacement
     *
     * @return self
     */
    public function withReplacement($replacement)
    {
        $base = new Shortcode($this->name, $this->parameters, $this->content, $this->bbCode);
        $parsed = new ParsedShortcode($base, $this->text, $this->offset);

        return new self($parsed, $replacement);
    }

    /** @return string */
    public function getReplacement()
    {
        return $this->replacement;
    }

    /** @return string */
    public function getText()
    {
        return $this->text;
    }

    /** @return int */
    public function getOffset()
    {
        return $this->offset;
    }
}
