<?php
namespace Thunder\Shortcode;

use Thunder\Shortcode\HandlerContainer\HandlerContainerInterface;
use Thunder\Shortcode\Parser\RegexParser;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Serializer\JsonSerializer;
use Thunder\Shortcode\Serializer\TextSerializer;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;
use Thunder\Shortcode\Syntax\Syntax;
use Thunder\Shortcode\Syntax\SyntaxInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
class ShortcodeFacade
{
    private $parser;
    private $processor;
    private $jsonSerializer;
    private $textSerializer;

    protected function __construct(HandlerContainerInterface $handlers, SyntaxInterface $syntax)
    {
        $this->parser = new RegexParser($syntax ?: new Syntax());
        $this->processor = new Processor($this->parser, $handlers);

        $this->textSerializer = new TextSerializer();
        $this->jsonSerializer = new JsonSerializer();
    }

    public static function create(HandlerContainerInterface $handlers, SyntaxInterface $syntax)
    {
        return new self($handlers, $syntax);
    }

    final public function parse($code)
    {
        return $this->parser->parse($code);
    }

    final public function process($text)
    {
        return $this->processor->process($text);
    }

    final public function serializeToText(ShortcodeInterface $shortcode)
    {
        return $this->textSerializer->serialize($shortcode);
    }

    final public function unserializeFromText($text)
    {
        return $this->textSerializer->unserialize($text);
    }

    final public function serializeToJson(ShortcodeInterface $shortcode)
    {
        return $this->jsonSerializer->serialize($shortcode);
    }

    final public function unserializeFromJson($json)
    {
        return $this->jsonSerializer->unserialize($json);
    }
}
