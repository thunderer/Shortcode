<?php
namespace Thunder\Shortcode;

use Thunder\Shortcode\Serializer\JsonSerializer;
use Thunder\Shortcode\Serializer\TextSerializer;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
class ShortcodeFacade
    {
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

    protected function __construct(Syntax $syntax = null, array $handlers = array(), array $aliases = array())
        {
        $syntax = $syntax ?: new Syntax();

        $this->createExtractor(new Extractor($syntax));
        $this->createParser(new Parser($syntax));
        $this->createProcessor(new Processor($this->extractor, $this->parser), $handlers, $aliases);

        $this->createTextSerializer(new TextSerializer());
        $this->createJsonSerializer(new JsonSerializer());
        }

    public static function create(Syntax $syntax = null, array $handlers = array(), array $aliases = array())
        {
        return new self($syntax, $handlers, $aliases);
        }

    protected function createExtractor(ExtractorInterface $extractor)
        {
        $this->extractor = $extractor;
        }

    protected function createParser(ParserInterface $parser)
        {
        $this->parser = $parser;
        }

    protected function createProcessor(ProcessorInterface $processor, array $handlers, array $aliases)
        {
        /** @var $processor Processor */
        $this->processor = $processor;

        foreach($handlers as $name => $handler)
            {
            $this->processor->addHandler($name, $handler);
            }
        foreach($aliases as $alias => $name)
            {
            $this->processor->addHandlerAlias($alias, $name);
            }
        }

    protected function createTextSerializer(SerializerInterface $serializer)
        {
        $this->textSerializer = $serializer;
        }

    protected function createJsonSerializer(SerializerInterface $serializer)
        {
        $this->jsonSerializer = $serializer;
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

    final public function serializeToText(Shortcode $shortcode)
        {
        return $this->textSerializer->serialize($shortcode);
        }

    final public function unserializeFromText($text)
        {
        return $this->textSerializer->unserialize($text);
        }

    final public function serializeToJson(Shortcode $shortcode)
        {
        return $this->jsonSerializer->serialize($shortcode);
        }

    final public function unserializeFromJson($json)
        {
        return $this->jsonSerializer->unserialize($json);
        }
    }
