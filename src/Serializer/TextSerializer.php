<?php
namespace Thunder\Shortcode\Serializer;

use Thunder\Shortcode\Parser;
use Thunder\Shortcode\SerializerInterface;
use Thunder\Shortcode\Shortcode;

final class TextSerializer implements SerializerInterface
    {
    private $open;
    private $close;
    private $slash;
    private $equals;
    private $string;

    public function __construct($open = '[', $close = ']', $slash = '/', $equals = '=', $string = '"')
        {
        $this->open = $open;
        $this->close = $close;
        $this->slash = $slash;
        $this->equals = $equals;
        $this->string = $string;
        }

    public function serialize(Shortcode $s)
        {
        return
            $this->open.$s->getName().$this->serializeParameters($s->getParameters()).$this->close
            .(null === $s->getContent() ? '' : $s->getContent().$this->open.$this->slash.$s->getName().$this->close);
        }

    private function serializeParameters(array $parameters)
        {
        $return = '';
        foreach($parameters as $key => $value)
            {
            $return .= ' '.$key;
            if(null !== $value)
                {
                $return .= $this->equals.(preg_match('/^\w+$/us', $value)
                    ? $value
                    : $this->string.$value.$this->string);
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
