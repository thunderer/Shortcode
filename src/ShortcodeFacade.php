<?php
namespace Thunder\Shortcode;

use Thunder\Shortcode\Serializer\JsonSerializer;
use Thunder\Shortcode\Serializer\TextSerializer;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ShortcodeFacade implements ExtractorInterface, ParserInterface, ProcessorInterface
    {
    private $extractor;
    private $parser;
    private $processor;

    private $jsonSerializer;
    private $textSerializer;

    private function __construct(ExtractorInterface $extractor = null, ParserInterface $parser = null)
        {
        $this->extractor = $extractor ?: new Extractor();
        $this->parser = $parser ?: new Parser();
        $this->processor = new Processor($this->extractor, $this->parser);

        $this->jsonSerializer = new JsonSerializer();
        $this->textSerializer = new TextSerializer();
        }

    public static function create(ExtractorInterface $extractor = null, ParserInterface $parser = null)
        {
        return new self($extractor, $parser);
        }

    public static function createWithSyntax(Syntax $syntax = null)
        {
        $syntax = $syntax ?: new Syntax();

        return new self(new Extractor($syntax), new Parser($syntax));
        }

    public function extract($text)
        {
        return $this->extractor->extract($text);
        }

    public function parse($code)
        {
        return $this->parser->parse($code);
        }

    public function addHandler($name, $handler)
        {
        $this->processor->addHandler($name, $handler);

        return $this;
        }

    public function addHandlerAlias($alias, $name)
        {
        $this->processor->addHandlerAlias($alias, $name);

        return $this;
        }

    public function process($text)
        {
        return $this->processor->process($text);
        }

    public function serializeToText(Shortcode $shortcode)
        {
        return $this->textSerializer->serialize($shortcode);
        }

    public function unserializeFromText($text)
        {
        return $this->textSerializer->unserialize($text);
        }

    public function serializeToJson(Shortcode $shortcode)
        {
        return $this->jsonSerializer->serialize($shortcode);
        }

    public function unserializeFromJson($json)
        {
        return $this->jsonSerializer->unserialize($json);
        }
    }
