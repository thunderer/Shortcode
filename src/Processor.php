<?php
namespace Thunder\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Processor implements ProcessorInterface
    {
    private $handlers = array();
    private $extractor;
    private $parser;
    private $defaultHandler;
    private $recursion = true;

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
     * @return $this
     */
    public function addHandler($name, $handler)
        {
        if(!is_callable($handler) && !$handler instanceof HandlerInterface)
            {
            $msg = 'Shortcode handler must be callable or implement HandlerInterface!';
            throw new \RuntimeException(sprintf($msg));
            }
        if($this->hasHandler($name))
            {
            $msg = 'Cannot register duplicate shortcode handler for %s!';
            throw new \RuntimeException(sprintf($msg, $name));
            }

        $this->handlers[$name] = $handler;

        return $this;
        }

    /**
     * Registers handler alias for given shortcode name, which means that
     * handler for target name will be called when alias is found.
     *
     * @param string $alias Alias shortcode name
     * @param string $name Aliased shortcode name
     *
     * @return $this
     */
    public function addHandlerAlias($alias, $name)
        {
        $this->addHandler($alias, function(Shortcode $shortcode) use($name) {
            return call_user_func_array($this->getHandler($name), array($shortcode));
            });

        return $this;
        }

    /**
     * Default library behavior is to ignore and return matches of shortcodes
     * without handler just like they were found. With this callable being set,
     * all matched shortcodes without registered handler will be passed to it.
     *
     * @param callable $handler Handler for shortcodes without registered name handler
     */
    public function setDefaultHandler(callable $handler)
        {
        $this->defaultHandler = $handler;
        }

    /**
     * Expects matches sorted by position returned from Extractor. Matches are
     * processed from the last to the first to avoid replace position errors.
     * Edge cases are described in README.
     *
     * @param string $text
     *
     * @return string
     */
    public function process($text)
        {
        /** @var $matches Match[] */
        $matches = array_reverse($this->extractor->extract($text));

        foreach($matches as $match)
            {
            $shortcode = $this->parser->parse($match->getString());
            $content = $shortcode->hasContent() && $this->recursion
                ? $this->process($shortcode->getContent())
                : $shortcode->getContent();
            $shortcode = new Shortcode($shortcode->getName(), $shortcode->getParameters(), $content);
            $handler = $this->getHandler($shortcode->getName());
            if($handler)
                {
                $replace = $this->callHandler($handler, $shortcode, $match);
                $text = substr_replace($text, $replace, $match->getPosition(), $match->getLength());
                }
            }

        return $text;
        }

    /**
     * Recursive shortcodes processing is enabled by default, with this method
     * it can be turned on or off as required.
     *
     * @param $status
     * @return $this
     */
    public function setRecursion($status)
        {
        $this->recursion = $status;

        return $this;
        }

    private function callHandler($handler, Shortcode $shortcode, Match $match)
        {
        if($handler instanceof HandlerInterface)
            {
            return $handler->isValid($shortcode)
                ? $handler->handle($shortcode)
                : $match->getString();
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
