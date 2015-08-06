<?php
namespace Thunder\Shortcode;

use Thunder\Shortcode\Extractor\ExtractorInterface;
use Thunder\Shortcode\Extractor\RegexExtractor;
use Thunder\Shortcode\HandlerContainer\HandlerContainerInterface;
use Thunder\Shortcode\Parser\ParserInterface;
use Thunder\Shortcode\Parser\RegexParser;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Processor\ProcessorInterface;
use Thunder\Shortcode\Serializer\JsonSerializer;
use Thunder\Shortcode\Serializer\SerializerInterface;
use Thunder\Shortcode\Serializer\TextSerializer;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;
use Thunder\Shortcode\Syntax\Syntax;
use Thunder\Shortcode\Syntax\SyntaxInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
class ShortcodeFacade
    {
    private $syntax;
    /** @var ExtractorInterface */
    private $extractor;
    /** @var ParserInterface */
    private $parser;
    /** @var ProcessorInterface */
    private $processor;

    /** @var SerializerInterface */
    private $jsonSerializer;
    /** @var SerializerInterface */
    private $textSerializer;

    protected function __construct(SyntaxInterface $syntax = null, HandlerContainerInterface $handlers)
        {
        $this->syntax = $syntax ?: new Syntax();

        $this->createExtractor();
        $this->createParser();
        $this->createProcessor($handlers);

        $this->createTextSerializer();
        $this->createJsonSerializer();
        }

    public static function create(SyntaxInterface $syntax = null, HandlerContainerInterface $handlers)
        {
        return new self($syntax, $handlers);
        }

    protected function createExtractor()
        {
        $this->extractor = new RegexExtractor($this->syntax);
        }

    protected function createParser()
        {
        $this->parser = new RegexParser($this->syntax);
        }

    protected function createProcessor(HandlerContainerInterface $handlers)
        {
        $this->processor = new Processor($this->extractor, $this->parser, $handlers);
        }

    protected function createTextSerializer()
        {
        $this->textSerializer = new TextSerializer();
        }

    protected function createJsonSerializer()
        {
        $this->jsonSerializer = new JsonSerializer();
        }

    final public function extract($text)
        {
        return $this->extractor->extract($text);
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
