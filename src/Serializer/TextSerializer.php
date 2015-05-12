<?php
namespace Thunder\Shortcode\Serializer;

use Thunder\Shortcode\Parser;
use Thunder\Shortcode\SerializerInterface as OldSerializerInterface;
use Thunder\Shortcode\Shortcode;
use Thunder\Shortcode\Syntax;

final class TextSerializer implements OldSerializerInterface
    {
    private $syntax;

    public function __construct(Syntax $syntax = null)
        {
        $this->syntax = $syntax ?: new Syntax();
        }

    public function serialize(Shortcode $s)
        {
        $open = $this->syntax->getOpeningTag();
        $close = $this->syntax->getClosingTag();
        $marker = $this->syntax->getClosingTagMarker();

        $parameters = $this->serializeParameters($s->getParameters());
        $return = $open.$s->getName().$parameters.$close;

        if(null !== $s->getContent())
            {
            $return .= $s->getContent().$open.$marker.$s->getName().$close;
            }

        return $return;
        }

    private function serializeParameters(array $parameters)
        {
        $return = '';
        foreach($parameters as $key => $value)
            {
            $return .= ' '.$key;
            if(null !== $value)
                {
                $delimiter = $this->syntax->getParameterValueDelimiter();
                $separator = $this->syntax->getParameterValueSeparator();

                $return .= $separator.(preg_match('/^\w+$/us', $value)
                    ? $value
                    : $delimiter.$value.$delimiter);
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
