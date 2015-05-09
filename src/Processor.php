<?php
namespace Thunder\Shortcode;
use Thunder\Shortcode\Shortcode\ContextAwareShortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Processor implements ProcessorInterface
    {
    private $handlers = array();
    private $extractor;
    private $parser;
    private $defaultHandler;
    private $recursionDepth = null;
    private $maxIterations = 1;

    public function __construct(ExtractorInterface $extractor, ParserInterface $parser)
        {
        $this->extractor = $extractor;
        $this->parser = $parser;
        }

    /**
     * Registers handler for given shortcode name.
     *
     * @param string $name
     * @param callable|HandlerInterface $handler
     *
     * @return self
     */
    public function addHandler($name, $handler)
        {
        $this->guardHandler($handler);

        if(!$name || $this->hasHandler($name))
            {
            $msg = 'Invalid name or duplicate shortcode handler for %s!';
            throw new \RuntimeException(sprintf($msg, $name));
            }

        $this->handlers[$name] = $handler;

        return $this;
        }

    private function guardHandler($handler)
        {
        if(!is_callable($handler) && !$handler instanceof HandlerInterface)
            {
            $msg = 'Shortcode handler must be callable or implement HandlerInterface!';
            throw new \RuntimeException(sprintf($msg));
            }
        }

    /**
     * Registers handler alias for given shortcode name, which means that
     * handler for target name will be called when alias is found.
     *
     * @param string $alias Alias shortcode name
     * @param string $name Aliased shortcode name
     *
     * @return self
     */
    public function addHandlerAlias($alias, $name)
        {
        $handler = $this->getHandler($name);

        $this->addHandler($alias, function(Shortcode $shortcode) use($handler) {
            return call_user_func_array($handler, array($shortcode));
            });

        return $this;
        }

    /**
     * Default library behavior is to ignore and return matches of shortcodes
     * without handler just like they were found. With this callable being set,
     * all matched shortcodes without registered handler will be passed to it.
     *
     * @param callable|HandlerInterface $handler Handler for shortcodes without registered name handler
     */
    public function setDefaultHandler($handler)
        {
        $this->guardHandler($handler);

        $this->defaultHandler = $handler;
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
        $position = 0;
        $namePositions = array();
        $iterations = $this->maxIterations === null ? 1 : $this->maxIterations;
        while($iterations--)
            {
            $newText = $this->processIteration($text, 0, $position, $namePositions);
            if($newText === $text)
                {
                break;
                }
            $text = $newText;
            $iterations += $this->maxIterations === null ? 1 : 0;
            }

        return $text;
        }

    /**
     * Expects matches sorted by position returned from Extractor. Matches are
     * processed from the last to the first to avoid replace position errors.
     * Edge cases are described in README.
     *
     * @param string $text Current text state
     * @param int $level Current recursion depth level
     * @param int $position Current shortcode position
     * @param array $namePositions Current shortcodes name positions
     *
     * @return string
     */
    private function processIteration($text, $level, &$position, array &$namePositions)
        {
        if(null !== $this->recursionDepth && $level > $this->recursionDepth)
            {
            return $text;
            }

        /** @var $matches Match[] */
        $matches = $this->extractor->extract($text);
        $matchesCount = count($matches);

        /** @var $shortcodes ContextAwareShortcode[] */
        $shortcodes = $this->prepareContextAwareShortcodes($matches, $position, $namePositions);
        for($i = $matchesCount - 1; $i >= 0; $i--)
            {
            $match = $matches[$i];
            $shortcode = $shortcodes[$i];

            if($shortcode->hasContent())
                {
                $content = $this->processIteration($shortcode->getContent(), $level + 1, $position, $namePositions);
                $shortcode = $shortcode->withContent($content);
                }

            $handler = $this->getHandler($shortcode->getName());
            if($handler)
                {
                $replace = $this->callHandler($handler, $shortcode, $match->getString());
                $text = substr_replace($text, $replace, $match->getPosition(), $match->getLength());
                }
            }

        return $text;
        }

    private function prepareContextAwareShortcodes(array $matches, &$position, array &$namePositions)
        {
        $processed = array();

        /** @var $matches Match[] */
        foreach($matches as $match)
            {
            $shortcode = $this->parser->parse($match->getString());
            $name = $shortcode->getName();
            $namePositions[$name] = array_key_exists($name, $namePositions)
                ? $namePositions[$name] + 1
                : 1;
            $position++;

            $processed[] = new ContextAwareShortcode($shortcode, $position, $namePositions[$name], $match->getString(), $match->getPosition());
            }

        return $processed;
        }

    /**
     * Recursion depth level, null means infinite, any integer greater than or
     * equal to zero sets value.
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
     * than or equal to zero sets value.
     *
     * @param int|null $iterations
     *
     * @return self
     */
    public function setMaxIterations($iterations)
        {
        if(null !== $iterations && !(is_int($iterations) && $iterations >= 0))
            {
            $msg = 'Maximum number of iterations must be null (infinite) or integer >= 0!';
            throw new \InvalidArgumentException($msg);
            }

        $this->maxIterations = $iterations;

        return $this;
        }

    /**
     * @deprecated Use self::setRecursionDepth() instead
     *
     * @param bool $recursion
     *
     * @return self
     */
    public function setRecursion($recursion)
        {
        return $this->setRecursionDepth($recursion ? null : 0);
        }

    private function callHandler($handler, ShortcodeInterface $shortcode, $string)
        {
        if($handler instanceof HandlerInterface)
            {
            return $handler->isValid($shortcode)
                ? $handler->handle($shortcode)
                : $string;
            }

        return call_user_func_array($handler, array($shortcode));
        }

    private function getHandler($name)
        {
        return $this->hasHandler($name)
            ? $this->handlers[$name]
            : ($this->defaultHandler ? $this->defaultHandler : null);
        }

    private function hasHandler($name)
        {
        return array_key_exists($name, $this->handlers);
        }
    }
