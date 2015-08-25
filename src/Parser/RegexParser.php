<?php
namespace Thunder\Shortcode\Parser;

use Thunder\Shortcode\Shortcode\ParsedShortcode;
use Thunder\Shortcode\Syntax\Syntax;
use Thunder\Shortcode\Syntax\SyntaxInterface;
use Thunder\Shortcode\Utility\RegexBuilderUtility;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class RegexParser implements ParserInterface
{
    /** @var SyntaxInterface */
    private $syntax;
    private $shortcodeRegex;
    private $singleShortcodeRegex;
    private $argumentsRegex;

    public function __construct(SyntaxInterface $syntax = null)
    {
        $this->syntax = $syntax ?: new Syntax();
        $this->shortcodeRegex = RegexBuilderUtility::buildShortcodeRegex($this->syntax);
        $this->singleShortcodeRegex = RegexBuilderUtility::buildSingleShortcodeRegex($this->syntax);
        $this->argumentsRegex = RegexBuilderUtility::buildArgumentsRegex($this->syntax);
    }

    /**
     * @param string $text
     *
     * @return ParsedShortcode[]
     */
    public function parse($text)
    {
        preg_match_all($this->shortcodeRegex, $text, $matches, PREG_OFFSET_CAPTURE);

        return array_map(array($this, 'parseSingle'), $matches[0]);
    }

    private function parseSingle(array $match)
    {
        $text = $match[0];
        $position = $match[1];

        preg_match($this->singleShortcodeRegex, $text, $matches);

        $name = $matches[2];
        $parameters = isset($matches[3]) ? $this->parseParameters($matches[3]) : array();
        $content = isset($matches[4]) ? $matches[4] : null;

        return new ParsedShortcode($name, $parameters, $content, $text, $position);
    }

    private function parseParameters($text)
    {
        preg_match_all($this->argumentsRegex, $text, $argsMatches);

        $return = array();
        foreach ($argsMatches[1] as $item) {
            $parts = explode($this->syntax->getParameterValueSeparator(), $item, 2);
            $return[trim($parts[0])] = $this->parseValue(isset($parts[1]) ? $parts[1] : null);
        };

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
