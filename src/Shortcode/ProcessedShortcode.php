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
    private $textOffset;
    private $shortcodeText;
    private $iterationNumber;
    private $recursionLevel;
    /** @var ProcessorInterface */
    private $processor;
    private $contentOffset;

    private function __construct()
    {
    }

    public static function createFromContext(ProcessorContext $context)
    {
        $self = new self();

        // basic properties
        $self->name = $context->shortcode->getName();
        $self->parameters = $context->shortcode->getParameters();
        $self->content = $context->shortcode->getContent();

        // runtime context
        $self->parent = $context->parent;
        $self->position = $context->position;
        $self->namePosition = $context->namePosition[$self->name];
        $self->text = $context->text;
        $self->shortcodeText = $context->shortcodeText;

        // processor state
        $self->iterationNumber = $context->iterationNumber;
        $self->recursionLevel = $context->recursionLevel;
        $self->processor = $context->processor;

        // text context
        $self->textOffset = $context->textOffset;
        $self->contentOffset = $context->contentOffset;

        return $self;
    }

    public function withContent($content)
    {
        $self = clone $this;
        $self->content = $content;

        return $self;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function getNamePosition()
    {
        return $this->namePosition;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getShortcodeText()
    {
        return $this->shortcodeText;
    }

    public function getOffset()
    {
        return $this->textOffset;
    }

    public function getContentOffset()
    {
        return $this->contentOffset;
    }

    public function getIterationNumber()
    {
        return $this->iterationNumber;
    }

    public function getRecursionLevel()
    {
        return $this->recursionLevel;
    }

    public function getProcessor()
    {
        return $this->processor;
    }
}
