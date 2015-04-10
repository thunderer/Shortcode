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

    public function __construct(ExtractorInterface $extractor, ParserInterface $parser)
        {
        $this->extractor = $extractor;
        $this->parser = $parser;
        }

    public function addHandler($name, callable $handler)
        {
        if($this->hasHandler($name))
            {
            $msg = 'Cannot register duplicate shortcode handler for %s!';
            throw new \RuntimeException(sprintf($msg, $name));
            }

        $this->handlers[$name] = $handler;

        return $this;
        }

    public function addHandlerAlias($alias, $name)
        {
        $this->addHandler($alias, function(Shortcode $shortcode) use($name) {
            return call_user_func_array($this->getHandler($name), array($shortcode));
            });

        return $this;
        }

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
            $shortcode = new Shortcode(
                $shortcode->getName(),
                $shortcode->getParameters(),
                $shortcode->hasContent() ? $this->process($shortcode->getContent()) : null
                );
            $handler = $this->getHandler($shortcode->getName());
            if($handler)
                {
                $replace = call_user_func_array($handler, array($shortcode));
                $text = substr_replace($text, $replace, $match->getPosition(), $match->getLength());
                }
            }

        return $text;
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
