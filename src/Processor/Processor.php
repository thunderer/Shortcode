<?php
namespace Thunder\Shortcode\Processor;

use Thunder\Shortcode\Event\ReplaceShortcodesEvent;
use Thunder\Shortcode\Event\FilterShortcodesEvent;
use Thunder\Shortcode\EventContainer\EventContainerInterface;
use Thunder\Shortcode\Events;
use Thunder\Shortcode\HandlerContainer\HandlerContainerInterface as Handlers;
use Thunder\Shortcode\Parser\ParserInterface;
use Thunder\Shortcode\Shortcode\ReplacedShortcode;
use Thunder\Shortcode\Shortcode\ParsedShortcodeInterface;
use Thunder\Shortcode\Shortcode\ProcessedShortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Processor implements ProcessorInterface
{
    /** @var Handlers */
    private $handlers;
    /** @var ParserInterface */
    private $parser;
    /** @var EventContainerInterface|null */
    private $eventContainer;

    /** @var int|null */
    private $recursionDepth; // infinite recursion
    /** @var int|null */
    private $maxIterations = 1; // one iteration
    /** @var bool */
    private $autoProcessContent = true; // automatically process shortcode content

    public function __construct(ParserInterface $parser, Handlers $handlers)
    {
        $this->parser = $parser;
        $this->handlers = $handlers;
    }

    /**
     * Entry point for shortcode processing. Implements iterative algorithm for
     * both limited and unlimited number of iterations.
     *
     * @param string $text Text to process
     *
     * @return string
     */
    public function process($text)
    {
        $iterations = $this->maxIterations === null ? 1 : $this->maxIterations;
        $context = new ProcessorContext();
        $context->processor = $this;

        while ($iterations--) {
            $context->iterationNumber++;
            $newText = $this->processIteration($text, $context, null);
            if ($newText === $text) {
                break;
            }
            $text = $newText;
            $iterations += $this->maxIterations === null ? 1 : 0;
        }

        return $text;
    }

    /**
     * @param string $name
     * @param object $event
     *
     * @return object
     */
    private function dispatchEvent($name, $event)
    {
        if(null === $this->eventContainer) {
            return $event;
        }

        $handlers = $this->eventContainer->getListeners($name);
        foreach($handlers as $handler) {
            $handler($event);
        }

        return $event;
    }

    /**
     * @param string $text
     *
     * @return string
     */
    private function processIteration($text, ProcessorContext $context, ProcessedShortcode $parent = null)
    {
        if (null !== $this->recursionDepth && $context->recursionLevel > $this->recursionDepth) {
            return $text;
        }

        $context->parent = $parent;
        $context->text = $text;
        $filterEvent = new FilterShortcodesEvent($this->parser->parse($text), $parent);
        $this->dispatchEvent(Events::FILTER_SHORTCODES, $filterEvent);
        $shortcodes = $filterEvent->getShortcodes();
        $replaces = array();
        $baseOffset = $parent && $shortcodes
            ? (int)mb_strpos($parent->getShortcodeText(), $shortcodes[0]->getText(), 0, 'utf-8') - $shortcodes[0]->getOffset() + $parent->getOffset()
            : 0;
        foreach ($shortcodes as $shortcode) {
            $name = $shortcode->getName();
            $hasNamePosition = array_key_exists($name, $context->namePosition);

            $context->baseOffset = $baseOffset + $shortcode->getOffset();
            $context->position++;
            $context->namePosition[$name] = $hasNamePosition ? $context->namePosition[$name] + 1 : 1;
            $context->shortcodeText = $shortcode->getText();
            $context->offset = $shortcode->getOffset();
            $context->shortcode = $shortcode;
            $context->textContent = (string)$shortcode->getContent();

            $handler = $this->handlers->get($name);
            $replace = $this->processHandler($shortcode, $context, $handler);

            $replaces[] = new ReplacedShortcode($shortcode, $replace);
        }

        $applyEvent = new ReplaceShortcodesEvent($text, $replaces, $parent);
        $this->dispatchEvent(Events::REPLACE_SHORTCODES, $applyEvent);

        return $applyEvent->hasResult() ? (string)$applyEvent->getResult() : $this->applyReplaces($text, $replaces);
    }

    /**
     * @param string $text
     * @param ReplacedShortcode[] $replaces
     *
     * @return string
     */
    private function applyReplaces($text, array $replaces)
    {
        foreach(array_reverse($replaces) as $s) {
            $offset = $s->getOffset();
            $length = mb_strlen($s->getText(), 'utf-8');
            $textLength = mb_strlen($text, 'utf-8');

            $text = mb_substr($text, 0, $offset, 'utf-8').$s->getReplacement().mb_substr($text, $offset + $length, $textLength, 'utf-8');
        }

        return $text;
    }

    /**
     * @psalm-param (callable(ShortcodeInterface):string)|null $handler
     * @return string
     */
    private function processHandler(ParsedShortcodeInterface $parsed, ProcessorContext $context, $handler)
    {
        $processed = ProcessedShortcode::createFromContext(clone $context);
        $content = $this->processRecursion($processed, $context);
        $processed = $processed->withContent($content);

        if($handler) {
            return $handler($processed);
        }

        $state = $parsed->getText();
        /** @psalm-suppress RedundantCast */
        $length = (int)mb_strlen($processed->getTextContent(), 'utf-8');
        $offset = (int)mb_strrpos($state, $processed->getTextContent(), 0, 'utf-8');

        return mb_substr($state, 0, $offset, 'utf-8').(string)$processed->getContent().mb_substr($state, $offset + $length, mb_strlen($state, 'utf-8'), 'utf-8');
    }

    /** @return string|null */
    private function processRecursion(ProcessedShortcode $shortcode, ProcessorContext $context)
    {
        $content = $shortcode->getContent();
        if ($this->autoProcessContent && null !== $content) {
            $context->recursionLevel++;
            // this is safe from using max iterations value because it's manipulated in process() method
            $content = $this->processIteration($content, clone $context, $shortcode);
            $context->recursionLevel--;

            return $content;
        }

        return $content;
    }

    /**
     * Container for event handlers used in this processor.
     *
     * @param EventContainerInterface $eventContainer
     *
     * @return self
     */
    public function withEventContainer(EventContainerInterface $eventContainer)
    {
        $self = clone $this;
        $self->eventContainer = $eventContainer;

        return $self;
    }

    /**
     * Recursion depth level, null means infinite, any integer greater than or
     * equal to zero sets value (number of recursion levels). Zero disables
     * recursion. Defaults to null.
     *
     * @param int|null $depth
     *
     * @return self
     */
    public function withRecursionDepth($depth)
    {
        /** @psalm-suppress DocblockTypeContradiction */
        if (null !== $depth && !(is_int($depth) && $depth >= 0)) {
            $msg = 'Recursion depth must be null (infinite) or integer >= 0!';
            throw new \InvalidArgumentException($msg);
        }

        $self = clone $this;
        $self->recursionDepth = $depth;

        return $self;
    }

    /**
     * Maximum number of iterations, null means infinite, any integer greater
     * than zero sets value. Zero is invalid because there must be at least one
     * iteration. Defaults to 1. Loop breaks if result of two consequent
     * iterations shows no change in processed text.
     *
     * @param int|null $iterations
     *
     * @return self
     */
    public function withMaxIterations($iterations)
    {
        /** @psalm-suppress DocblockTypeContradiction */
        if (null !== $iterations && !(is_int($iterations) && $iterations > 0)) {
            $msg = 'Maximum number of iterations must be null (infinite) or integer > 0!';
            throw new \InvalidArgumentException($msg);
        }

        $self = clone $this;
        $self->maxIterations = $iterations;

        return $self;
    }

    /**
     * Whether shortcode content will be automatically processed and handler
     * already receives shortcode with processed content. If false, every
     * shortcode handler needs to process content on its own. Default true.
     *
     * @param bool $flag True if enabled (default), false otherwise
     *
     * @return self
     */
    public function withAutoProcessContent($flag)
    {
        /** @psalm-suppress DocblockTypeContradiction */
        if (!is_bool($flag)) {
            $msg = 'Auto processing flag must be a boolean value!';
            throw new \InvalidArgumentException($msg);
        }

        $self = clone $this;
        $self->autoProcessContent = $flag;

        return $self;
    }
}
