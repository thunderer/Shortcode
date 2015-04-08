<?php
namespace Thunder\Shortcode\Serializer;

use Thunder\Shortcode\Parser;
use Thunder\Shortcode\SerializerInterface;
use Thunder\Shortcode\Shortcode;
use Thunder\Shortcode\Syntax;

final class TextSerializer implements SerializerInterface
    {
    private $syntax;

    public function __construct(Syntax $syntax = null)
        {
        $this->syntax = $syntax ?: new Syntax();
        }

    public function serialize(Shortcode $s)
        {
        return
            $this->syntax->getOpeningTag()
            .$s->getName().$this->serializeParameters($s->getParameters())
            .$this->syntax->getClosingTag()
            .(null === $s->getContent() ? '' : $s->getContent().$this->syntax->getOpeningTag().$this->syntax->getClosingTagMarker().$s->getName().$this->syntax->getClosingTag());
        }

    private function serializeParameters(array $parameters)
        {
        $return = '';
        foreach($parameters as $key => $value)
            {
            $return .= ' '.$key;
            if(null !== $value)
                {
                $return .= $this->syntax->getParameterValueSeparator().(preg_match('/^\w+$/us', $value)
                    ? $value
                    : $this->syntax->getParameterValueDelimiter().$value.$this->syntax->getParameterValueDelimiter());
                }
            }

        return $return;
        }

    public function unserialize($text)
        {
        $parser = new Parser();

        return $parser->parse($text);
        }
    }
