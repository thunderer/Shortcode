<?php
namespace Thunder\Shortcode\Shortcode;

use Thunder\Shortcode\Processor\ProcessorContext;
use Thunder\Shortcode\Processor\ProcessorInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ProcessedShortcode extends AbstractShortcode implements ParsedShortcodeInterface
{
    /** @var ProcessedShortcode|null */
    private $parent;
    /** @var int */
    private $position;
    /** @var int */
    private $namePosition;
    /** @var string */
    private $text;
    /** @var string */
    private $textContent;
    /** @var int */
    private $offset;
    /** @var int */
    private $baseOffset;
    /** @var string */
    private $shortcodeText;
    /** @var int */
    private $iterationNumber;
    /** @var int */
    private $recursionLevel;
    /** @var ProcessorInterface */
    private $processor;

    private function __construct(ProcessorContext $context)
    {
        // basic properties
        $this->name = $context->shortcode->getName();
        $this->parameters = $context->shortcode->getParameters();
        $this->content = $context->shortcode->getContent();
        $this->bbCode = $context->shortcode->getBbCode();
        $this->textContent = $context->textContent;

        // runtime context
        $this->parent = $context->parent;
        $this->position = $context->position;
        $this->namePosition = $context->namePosition[$this->name];
        $this->text = $context->text;
        $this->shortcodeText = $context->shortcodeText;

        // processor state
        $this->iterationNumber = $context->iterationNumber;
        $this->recursionLevel = $context->recursionLevel;
        $this->processor = $context->processor;

        // text context
        $this->offset = $context->offset;
        $this->baseOffset = $context->baseOffset;
    }

    /** @return self */
    public static function createFromContext(ProcessorContext $context)
    {
        return new self($context);
    }

    /**
     * @param string|null $content
     *
     * @return self
     */
    public function withContent($content)
    {
        $self = clone $this;
        $self->content = $content;

        return $self;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasAncestor($name)
    {
        $self = $this;

        while($self = $self->getParent()) {
            if($self->getName() === $name) {
                return true;
            }
        }

        return false;
    }

    /** @return ProcessedShortcode|null */
    public function getParent()
    {
        return $this->parent;
    }

    /** @return string */
    public function getTextContent()
    {
        return $this->textContent;
    }

    /** @return int */
    public function getPosition()
    {
        return $this->position;
    }

    /** @return int */
    public function getNamePosition()
    {
        return $this->namePosition;
    }

    /** @return string */
    public function getText()
    {
        return $this->text;
    }

    /** @return string */
    public function getShortcodeText()
    {
        return $this->shortcodeText;
    }

    /** @return int */
    public function getOffset()
    {
        return $this->offset;
    }

    /** @return int */
    public function getBaseOffset()
    {
        return $this->baseOffset;
    }

    /** @return int */
    public function getIterationNumber()
    {
        return $this->iterationNumber;
    }

    /** @return int */
    public function getRecursionLevel()
    {
        return $this->recursionLevel;
    }

    /** @return ProcessorInterface */
    public function getProcessor()
    {
        return $this->processor;
    }
}
