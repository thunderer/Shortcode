<?php
namespace Thunder\Shortcode\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ContextAwareShortcode extends Shortcode
    {
    private $parent;
    private $position;
    private $namePosition;
    private $text;
    private $textPosition;
    private $textMatch;
    private $iterationNumber;
    private $recursionLevel;

    public function __construct(ShortcodeInterface $s, ShortcodeInterface $parent = null,
                                $position, $namePosition,
                                $text, $textPosition, $textMatch,
                                $iterationNumber, $recursionLevel)
        {
        parent::__construct($s->getName(), $s->getParameters(), $s->getContent());

        $this->parent = $parent;
        $this->position = $position;
        $this->namePosition = $namePosition;
        $this->text = $text;
        $this->textPosition = $textPosition;
        $this->textMatch = $textMatch;
        $this->iterationNumber = $iterationNumber;
        $this->recursionLevel = $recursionLevel;
        }

    public function withContent($content)
        {
        $s = new Shortcode($this->getName(), $this->getParameters(), $content);

        return new self($s, $this->parent,
            $this->position, $this->namePosition,
            $this->text, $this->textPosition, $this->textMatch,
            $this->iterationNumber, $this->recursionLevel);
        }

    public function getParent()
        {
        return $this->parent;
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
     * @return string
     */
    public function getText()
        {
        return $this->text;
        }

    /**
     * Returns position at which shortcode was found in text
     *
     * @return int
     */
    public function getTextPosition()
        {
        return $this->textPosition;
        }

    /**
     * Returns exact match, ie. exact string that was found in text
     *
     * @return string
     */
    public function getTextMatch()
        {
        return $this->textMatch;
        }
    }
