<?php
namespace Thunder\Shortcode\Shortcode;

use Thunder\Shortcode\Processor\ProcessorContext;
use Thunder\Shortcode\Processor\ProcessorInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ProcessedShortcode extends AbstractShortcode implements ParsedShortcodeInterface
{
    private $parent;
    private $position;
    private $namePosition;
    private $text;
    private $textPosition;
    private $textMatch;
    private $iterationNumber;
    private $recursionLevel;
    private $processor;

    private function __construct()
    {
    }

    public static function createFromContext(ProcessorContext $context)
    {
        $self = new self();

        $shortcode = $context->shortcode;
        $self->name = $shortcode->getName();
        $self->parameters = $shortcode->getParameters();
        $self->content = $shortcode->getContent();

        $self->parent = $context->parent;
        $self->position = $context->position;
        $self->namePosition = $context->namePosition[$shortcode->getName()];
        $self->text = $context->text;
        $self->textPosition = $context->textPosition;
        $self->textMatch = $context->textMatch;
        $self->iterationNumber = $context->iterationNumber;
        $self->recursionLevel = $context->recursionLevel;
        $self->processor = $context->processor;

        return $self;
    }

    public function withContent($content)
    {
        $self = new self();

        $self->name = $this->getName();
        $self->parameters = $this->getParameters();
        $self->content = $content;

        $self->parent = $this->parent;
        $self->position = $this->position;
        $self->namePosition = $this->namePosition;
        $self->text = $this->text;
        $self->textPosition = $this->textPosition;
        $self->textMatch = $this->textMatch;
        $self->iterationNumber = $this->iterationNumber;
        $self->recursionLevel = $this->recursionLevel;
        $self->processor = $this->processor;

        return $self;
    }

    /**
     * @return ShortcodeInterface
     */
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

    /**
     * Returns number of current iteration
     *
     * @return int
     */
    public function getIterationNumber()
    {
        return $this->iterationNumber;
    }

    /**
     * Returns current level of recursive processing
     *
     * @return int
     */
    public function getRecursionLevel()
    {
        return $this->recursionLevel;
    }

    /**
     * Returns instance of processor processing the text
     *
     * @return ProcessorInterface
     */
    public function getProcessor()
    {
        return $this->processor;
    }
}
