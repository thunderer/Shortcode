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

        return null === $s->getContent()
            ? $return
            : $return.$s->getContent().$open.$marker.$s->getName().$close;
    }

    private function serializeParameters(array $parameters)
    {
        // unfortunately array_reduce() does not support keys
        $return = '';
        foreach ($parameters as $key => $value) {
            $return .= ' '.$key.$this->serializeParameter($value);
        }

        return $return;
    }

    private function serializeParameter($value)
    {
        if (null === $value) {
            return '';
        }

        $delimiter = $this->syntax->getParameterValueDelimiter();
        $separator = $this->syntax->getParameterValueSeparator();

        return $separator.(preg_match('/^\w+$/us', $value)
            ? $value
            : $delimiter.$value.$delimiter);
    }

    public function unserialize($text)
    {
        $parser = new RegexParser();

        $shortcodes = $parser->parse($text);

        if (empty($shortcodes)) {
            $msg = 'Failed to unserialize shortcode from text %s!';
            throw new \InvalidArgumentException(sprintf($msg, $text));
        }
        if (count($shortcodes) > 1) {
            $msg = 'Provided text %s contains more than one shortcode!';
            throw new \InvalidArgumentException(sprintf($msg, $text));
        }

        return array_shift($shortcodes);
    }
}
