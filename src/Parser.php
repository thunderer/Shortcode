<?php
namespace Thunder\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Parser implements ParserInterface
    {
    /** @var Syntax */
    private $syntax;

    public function __construct(Syntax $syntax = null)
        {
        $this->syntax = $syntax ?: new Syntax();
        }

    public function parse($text)
        {
        $count = preg_match($this->syntax->getSingleShortcodeRegex(), $text, $matches);

        if(!$count)
            {
            $msg = 'Failed to match single shortcode in text "%s"!';
            throw new \RuntimeException(sprintf($msg, $text));
            }

        return new Shortcode(
            $matches[2],
            isset($matches[3]) ? $this->parseParameters($matches[3]) : array(),
            isset($matches[4]) ? $matches[4] : null
            );
        }

    private function parseParameters($text)
        {
        preg_match_all($this->syntax->getArgumentsRegex(), $text, $argsMatches);

        $return = array();
        foreach($argsMatches[1] as $item)
            {
            $parts = explode($this->syntax->getParameterValueSeparator(), $item, 2);
            $return[$parts[0]] = $this->parseValue(isset($parts[1]) ? $parts[1] : null);
            }

        return $return;
        }

    private function parseValue($value)
        {
        return $this->isStringValue($value)
            ? $this->extractStringValue($value)
            : $value;
        }

    private function extractStringValue($value)
        {
        $length = strlen($this->syntax->getParameterValueDelimiter());

        return substr($value, $length, -1 * $length);
        }

    private function isStringValue($value)
        {
        return preg_match('/^'.$this->syntax->getParameterValueDelimiter().'/us', $value)
            && preg_match('/'.$this->syntax->getParameterValueDelimiter().'$/us', $value);
        }
    }
