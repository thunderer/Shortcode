<?php
namespace Thunder\Shortcode\Shortcode;

use Thunder\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ContextAwareShortcode extends Shortcode
    {
    private $position;
    private $namePosition;
    private $text;
    private $offset;

    public function __construct(ShortcodeInterface $s, $position, $namePosition, $text, $offset)
        {
        parent::__construct($s->getName(), $s->getParameters(), $s->getContent());

        $this->position = $position;
        $this->namePosition = $namePosition;
        $this->text = $text;
        $this->offset = $offset;
        }

    public function withContent($content)
        {
        return new self(new Shortcode($this->getName(), $this->getParameters(), $content), $this->position, $this->namePosition, $this->text, $this->offset);
        }

    /**
     * Return position in sequence of shortcodes in the whole text
     *
     * @return int
     */
    public function getPosition()
        {
        return $this->position;
        }

    /**
     * Return position in sequence of shortcodes with given name
     *
     * @return int
     */
    public function getNamePosition()
        {
        return $this->namePosition;
        }

    /**
     * Returns text in which shortcode was found
     *
     * @return mixed
     */
    public function getText()
        {
        return $this->text;
        }

    /**
     * Returns offset at which shortcode was found in text
     *
     * @return int
     */
    public function getOffset()
        {
        return $this->offset;
        }
    }
