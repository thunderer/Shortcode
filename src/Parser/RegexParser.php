<?php
namespace Thunder\Shortcode\Parser;

use Thunder\Shortcode\Shortcode\ParsedShortcode;
use Thunder\Shortcode\Shortcode\Shortcode;
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
    /** @var string */
    private $shortcodeRegex;
    /** @var string */
    private $singleShortcodeRegex;
    /** @var string */
    private $parametersRegex;

    public function __construct(SyntaxInterface $syntax = null)
    {
        $this->syntax = $syntax ?: new Syntax();
        $this->shortcodeRegex = RegexBuilderUtility::buildShortcodeRegex($this->syntax);
        $this->singleShortcodeRegex = RegexBuilderUtility::buildSingleShortcodeRegex($this->syntax);
        $this->parametersRegex = RegexBuilderUtility::buildParametersRegex($this->syntax);
    }

    /**
     * @param string $text
     *
     * @return ParsedShortcode[]
     */
    public function parse($text)
    {
        preg_match_all($this->shortcodeRegex, $text, $matches, PREG_OFFSET_CAPTURE);

        // loop instead of array_map to pass the arguments explicitly
        $shortcodes = array();
        foreach($matches[0] as $match) {
            $offset = mb_strlen(substr($text, 0, $match[1]), 'utf-8');
            $shortcodes[] = $this->parseSingle($match[0], $offset);
        }

        return array_filter($shortcodes);
    }

    /**
     * @param string $text
     * @param int $offset
     *
     * @return ParsedShortcode
     */
    private function parseSingle($text, $offset)
    {
        preg_match($this->singleShortcodeRegex, $text, $matches, PREG_OFFSET_CAPTURE);

        /** @psalm-var array<string,array{0:string,1:int}> $matches */
        $name = $matches['name'][0];
        $parameters = isset($matches['parameters'][0]) ? $this->parseParameters($matches['parameters'][0]) : array();
        $bbCode = isset($matches['bbCode'][0]) && $matches['bbCode'][1] !== -1
            ? $this->extractValue($matches['bbCode'][0])
            : null;
        $content = isset($matches['content'][0]) && $matches['content'][1] !== -1 ? $matches['content'][0] : null;

        return new ParsedShortcode(new Shortcode($name, $parameters, $content, $bbCode), $text, $offset);
    }

    /**
     * @param string $text
     *
     * @psalm-return array<string,string|null>
     */
    private function parseParameters($text)
    {
        preg_match_all($this->parametersRegex, $text, $argsMatches);

        // loop because PHP 5.3 can't handle $this properly and I want separate methods
        $return = array();
        foreach ($argsMatches[1] as $item) {
            /** @psalm-var array{0:string,1:string} $parts */
            $parts = explode($this->syntax->getParameterValueSeparator(), $item, 2);
            $return[trim($parts[0])] = $this->parseValue(isset($parts[1]) ? $parts[1] : null);
        }

        return $return;
    }

    /**
     * @param string|null $value
     *
     * @return string|null
     */
    private function parseValue($value)
    {
        return null === $value ? null : $this->extractValue(trim($value));
    }

    /**
     * @param string $value
     *
     * @return string
     */
    private function extractValue($value)
    {
        $length = strlen($this->syntax->getParameterValueDelimiter());

        return $this->isDelimitedValue($value) ? substr($value, $length, -1 * $length) : $value;
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    private function isDelimitedValue($value)
    {
        return preg_match('/^'.$this->syntax->getParameterValueDelimiter().'/us', $value)
            && preg_match('/'.$this->syntax->getParameterValueDelimiter().'$/us', $value);
    }
}
