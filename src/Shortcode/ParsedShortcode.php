<?php
namespace Thunder\Shortcode\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ParsedShortcode extends AbstractShortcode implements ParsedShortcodeInterface
{
    /** @var string */
    private $text;
    /** @var int */
    private $offset;

    /**
     * @param string $text
     * @param int $offset
     */
    public function __construct(ShortcodeInterface $shortcode, $text, $offset)
    {
        $this->name = $shortcode->getName();
        $this->parameters = $shortcode->getParameters();
        $this->content = $shortcode->getContent();
        $this->bbCode = $shortcode->getBbCode();
        $this->text = $text;
        $this->offset = $offset;
    }

    public function withContent($content)
    {
        $self = clone $this;
        $self->content = $content;

        return $self;
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
