<?php
namespace Thunder\Shortcode\Processor;

use Thunder\Shortcode\Event\FilterShortcodesEvent;
use Thunder\Shortcode\EventDispatcher\EventDispatcherInterface;
use Thunder\Shortcode\Events;
use Thunder\Shortcode\HandlerContainer\HandlerContainerInterface as Handlers;
use Thunder\Shortcode\Parser\ParserInterface;
use Thunder\Shortcode\Shortcode\ParsedShortcodeInterface;
use Thunder\Shortcode\Shortcode\ProcessedShortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Processor implements ProcessorInterface
{
    private $handlers;
    private $parser;
    private $recursionDepth = null; // infinite recursion
    private $maxIterations = 1; // one iteration
    private $autoProcessContent = true; // automatically process shortcode content
    private $eventDispatcher;

    public function __construct(ParserInterface $parser, Handlers $handlers, EventDispatcherInterface $events = null)
    {
        $this->parser = $parser;
        $this->handlers = $handlers;
        $this->eventDispatcher = $events;
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

    private function dispatchEvent($name, $event)
    {
        return $this->eventDispatcher ? $this->eventDispatcher->dispatch($name, $event) : $event;
    }

    private function processIteration($text, ProcessorContext $context, ShortcodeInterface $parent = null)
    {
        if (null !== $this->recursionDepth && $context->recursionLevel > $this->recursionDepth) {
            return $text;
        }

        $context->parent = $parent;
        $context->text = $text;
        $event = new FilterShortcodesEvent($this->parser->parse($text), $parent);
        /** @var $event FilterShortcodesEvent */
        $event = $this->dispatchEvent(Events::FILTER_SHORTCODES, $event);
        /** @var $shortcodes ParsedShortcodeInterface[] */
        $shortcodes = $event->getShortcodes();
        $replaces = array();
        $results = array();
        foreach ($shortcodes as $shortcode) {
            $this->prepareHandlerContext($shortcode, $context);
            $handler = $this->handlers->get($shortcode->getName());
            $replace = $this->processHandler($shortcode, $context, $handler);
            $length = mb_strlen($shortcode->getText());

            $replaces[] = array($replace, $shortcode->getOffset(), $length);
            $results[$shortcode->getOffset()] = $replace;
        }
        $context->results = $results;
        $replaces = array_reverse(array_filter($replaces));

        return array_reduce($replaces, function ($state, array $item) {
            return mb_substr($state, 0, $item[1]).$item[0].mb_substr($state, $item[1] + $item[2]);
        }, $text);
    }

    private function prepareHandlerContext(ParsedShortcodeInterface $shortcode, ProcessorContext $context)
    {
        $context->position++;
        $hasNamePosition = array_key_exists($shortcode->getName(), $context->namePosition);
        $context->namePosition[$shortcode->getName()] = $hasNamePosition ? $context->namePosition[$shortcode->getName()] + 1 : 1;

        $context->shortcodeText = $shortcode->getText();
        $context->offset = $shortcode->getOffset();
        $context->shortcode = $shortcode;
        $context->textContent = $shortcode->getContent();
    }

    private function processHandler(ParsedShortcodeInterface $parsed, ProcessorContext $context, $handler)
    {
        $content = $this->processRecursion($parsed, $context);
        $processed = ProcessedShortcode::createFromContext(clone $context);
        $processed = $processed->withContent($content);

        return $handler
            ? call_user_func_array($handler, array($processed))
            : substr_replace($parsed->getText(), $processed->getContent(), strrpos($parsed->getText(), $parsed->getContent()), mb_strlen($parsed->getContent()));
    }

    private function processRecursion(ParsedShortcodeInterface $shortcode, ProcessorContext $context)
    {
        if ($this->autoProcessContent && null !== $shortcode->getContent()) {
            $context->recursionLevel++;
            // this is safe from using max iterations value because it's manipulated in process() method
            $subContext = clone $context;
            $content = $this->processIteration($shortcode->getContent(), $subContext, $shortcode);
            $context->results = $subContext->results;
            $context->recursionLevel--;

            return $content;
        }

        return $shortcode->getContent();
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
        if (!is_bool($flag)) {
            $msg = 'Auto processing flag must be a boolean value!';
            throw new \InvalidArgumentException($msg);
        }

        $self = clone $this;
        $self->autoProcessContent = (bool)$flag;

        return $self;
    }
}
