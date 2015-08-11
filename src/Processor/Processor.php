<?php
namespace Thunder\Shortcode\Processor;

use Thunder\Shortcode\Extractor\ExtractorInterface;
use Thunder\Shortcode\HandlerContainer\HandlerContainerInterface;
use Thunder\Shortcode\Match\MatchInterface;
use Thunder\Shortcode\Parser\ParserInterface;
use Thunder\Shortcode\Shortcode\ProcessedShortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Processor implements ProcessorInterface
    {
    private $handlers;
    private $extractor;
    private $parser;
    private $recursionDepth = null; // infinite recursion
    private $maxIterations = 1; // one iteration
    private $autoProcessContent = true; // automatically process shortcode content
    private $shortcodeBuilder;

    public function __construct(ExtractorInterface $extractor, ParserInterface $parser, HandlerContainerInterface $handlers)
        {
        $this->extractor = $extractor;
        $this->parser = $parser;
        $this->handlers = $handlers;

        $this->shortcodeBuilder = function(ProcessorContext $context) {
            return ProcessedShortcode::createFromContext($context);
            };
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

        while($iterations--)
            {
            $context->iterationNumber++;
            $newText = $this->processIteration($text, $context);
            if($newText === $text)
                {
                break;
                }
            $text = $newText;
            $iterations += $this->maxIterations === null ? 1 : 0;
            }

        return $text;
        }

    private function processIteration($text, ProcessorContext $context)
        {
        if(null !== $this->recursionDepth && $context->recursionLevel > $this->recursionDepth)
            {
            return $text;
            }

        $context->text = $text;
        $matches = $this->extractor->extract($text);
        $replaces = array();
        foreach($matches as $match)
            {
            $context->textMatch = $match->getString();
            $context->textPosition = $match->getPosition();
            $replaces[] = $this->processMatch($match, $context);
            }
        $replaces = array_reverse(array_filter($replaces));

        return array_reduce($replaces, function($state, array $item) {
            return substr_replace($state, $item[0], $item[1], $item[2]);
            }, $text);
        }

    private function processMatch(MatchInterface $match, ProcessorContext $context)
        {
        $shortcode = $this->parser->parse($match->getString());
        $context->position++;
        $context->namePosition[$shortcode->getName()] = array_key_exists($shortcode->getName(), $context->namePosition)
            ? $context->namePosition[$shortcode->getName()] + 1
            : 1;

        /** @var $shortcode ShortcodeInterface */
        $context->shortcode = $shortcode;
        $shortcode = call_user_func_array($this->shortcodeBuilder, array($context));
        if($this->autoProcessContent && $shortcode->hasContent())
            {
            $context->recursionLevel++;
            $context->parent = $shortcode;
            $content = $this->processIteration($shortcode->getContent(), $context);
            $shortcode = $shortcode->withContent($content);
            $context->parent = null;
            $context->recursionLevel--;
            }

        $handler = $this->handlers->getHandler($shortcode->getName());
        if(!$handler)
            {
            return null;
            }

        $replace = call_user_func_array($handler, array($shortcode));

        return array($replace, $match->getPosition(), mb_strlen($match->getString()));
        }

    /**
     * Recursion depth level, null means infinite, any integer greater than or
     * equal to zero sets value (number of recursion levels). Zero disables
     * recursion.
     *
     * @param int|null $depth
     *
     * @return self
     */
    public function setRecursionDepth($depth)
        {
        if(null !== $depth && !(is_int($depth) && $depth >= 0))
            {
            $msg = 'Recursion depth must be null (infinite) or integer >= 0!';
            throw new \InvalidArgumentException($msg);
            }

        $this->recursionDepth = $depth;

        return $this;
        }

    /**
     * Maximum number of iterations, null means infinite, any integer greater
     * than zero sets value. Zero is invalid because there must be at least one
     * iteration.
     *
     * @param int|null $iterations
     *
     * @return self
     */
    public function setMaxIterations($iterations)
        {
        if(null !== $iterations && !(is_int($iterations) && $iterations > 0))
            {
            $msg = 'Maximum number of iterations must be null (infinite) or integer > 0!';
            throw new \InvalidArgumentException($msg);
            }

        $this->maxIterations = $iterations;

        return $this;
        }

    /**
     * Whether shortcode content will be automatically processed and handler
     * already receives shortcode with processed content. If false, every
     * shortcode handler needs to process content on its own.
     *
     * @param bool $flag True if enabled (default), false otherwise
     *
     * @return self
     */
    public function setAutoProcessContent($flag)
        {
        $this->autoProcessContent = (bool)$flag;

        return $this;
        }
    }
