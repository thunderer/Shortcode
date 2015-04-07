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
        $this->syntax = $syntax ?: Syntax::createDefaults();
        }

    public function serialize(Shortcode $s)
        {
        return
            $this->syntax->getOpen()
            .$s->getName().$this->serializeParameters($s->getParameters())
            .$this->syntax->getClose()
            .(null === $s->getContent() ? '' : $s->getContent().$this->syntax->getOpen().$this->syntax->getSlash().$s->getName().$this->syntax->getClose());
        }

    private function serializeParameters(array $parameters)
        {
        $return = '';
        foreach($parameters as $key => $value)
            {
            $return .= ' '.$key;
            if(null !== $value)
                {
                $return .= $this->syntax->getEquals().(preg_match('/^\w+$/us', $value)
                    ? $value
                    : $this->syntax->getString().$value.$this->syntax->getString());
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
