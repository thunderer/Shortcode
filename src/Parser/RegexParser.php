<?php
namespace Thunder\Shortcode\Parser;

use Thunder\Shortcode\ParserInterface;
use Thunder\Shortcode\Shortcode;
use Thunder\Shortcode\Syntax;
use Thunder\Shortcode\Utility\RegexBuilderUtility;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class RegexParser implements ParserInterface
    {
    /** @var Syntax */
    private $syntax;
    private $shortcodeRegex;
    private $argumentsRegex;

    public function __construct(Syntax $syntax = null)
        {
        $this->syntax = $syntax ?: new Syntax();
        $this->shortcodeRegex = RegexBuilderUtility::buildSingleShortcodeRegex($this->syntax);
        $this->argumentsRegex = RegexBuilderUtility::buildArgumentsRegex($this->syntax);
        }

    public function parse($text)
        {
        $count = preg_match($this->shortcodeRegex, $text, $matches);

        if(!$count)
            {
            $msg = 'Failed to match single shortcode in text "%s"!';
            throw new \RuntimeException(sprintf($msg, $text));
            }

        $name = $matches[2];
        $parameters = isset($matches[3]) ? $this->parseParameters($matches[3]) : array();
        $content = isset($matches[4]) ? $matches[4] : null;

        return new Shortcode($name, $parameters, $content);
        }

    private function parseParameters($text)
        {
        preg_match_all($this->argumentsRegex, $text, $argsMatches);

        $return = array();
        foreach($argsMatches[1] as $item)
            {
            $parts = explode($this->syntax->getParameterValueSeparator(), $item, 2);
            $return[trim($parts[0])] = $this->parseValue(isset($parts[1]) ? $parts[1] : null);
            }

        return $return;
        }

    private function parseValue($value)
        {
        return null === $value ? null : $this->extractValue(trim($value));
        }

    private function extractValue($value)
        {
        $length = strlen($this->syntax->getParameterValueDelimiter());

        return $this->isDelimitedValue($value) ? substr($value, $length, -1 * $length) : $value;
        }

    private function isDelimitedValue($value)
        {
        return preg_match('/^'.$this->syntax->getParameterValueDelimiter().'/us', $value)
            && preg_match('/'.$this->syntax->getParameterValueDelimiter().'$/us', $value);
        }
    }
