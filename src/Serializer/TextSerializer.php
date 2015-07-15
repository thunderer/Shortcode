<?php
namespace Thunder\Shortcode\Serializer;

use Thunder\Shortcode\Parser\RegexParser;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;
use Thunder\Shortcode\Syntax\Syntax;
use Thunder\Shortcode\Syntax\SyntaxInterface;

final class TextSerializer implements SerializerInterface
    {
    private $syntax;

    public function __construct(SyntaxInterface $syntax = null)
        {
        $this->syntax = $syntax ?: new Syntax();
        }

    public function serialize(ShortcodeInterface $s)
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
        $parser = new RegexParser();

        return $parser->parse($text);
        }
    }
