<?php
namespace Thunder\Shortcode\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ParsedShortcode extends AbstractShortcode implements ParsedShortcodeInterface
{
    private $text;
    private $offset;
    private $offsets = array(
        'name' => null,
        'parameters' => null,
        'content' => null,
        'slash' => null,
        );

    public function __construct(ShortcodeInterface $shortcode, $text, $offset, array $offsets = array())
    {
        $this->name = $shortcode->getName();
        $this->parameters = $shortcode->getParameters();
        $this->content = $shortcode->getContent();
        $this->text = $text;
        $this->offset = $offset;

        if(array_diff_key($offsets, $this->offsets)) {
            throw new \InvalidArgumentException('Invalid positions data!');
        }
        $this->offsets = array_merge($this->offsets, $offsets);
    }

    public function withContent($content)
    {
        $self = clone $this;
        $self->content = $content;

        return $self;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function getContentOffset()
    {
        return $this->offsets['content'];
    }

    public function getSlashOffset()
    {
        return $this->offsets['slash'];
    }
}
