<?php
namespace Thunder\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Parser implements ParserInterface
    {
    const SHORTCODE_REGEX = '/^(\[(\w+)(\s+.+?)?\](?:(.+?)\[\/(\2)\])?)$/us';
    const ARGUMENTS_REGEX = '/(?:\s+(\w+(?:(?=\s|$)|=\w+|=".+")))/us';

    public function parse($text)
        {
        $count = preg_match(self::SHORTCODE_REGEX, $text, $matches);

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
        preg_match_all(self::ARGUMENTS_REGEX, $text, $argsMatches);

        return array_reduce($argsMatches[1], function(array $state, $item) {
            $values = explode('=', $item, 2);
            $value = isset($values[1]) ? $values[1] : null;
            $value = $value && $value[0] == '"' && $value[strlen($value) - 1] == '"'
                ? substr($value, 1, -1)
                : (isset($value) ? $value : null);
            $state[$values[0]] = $value;

            return $state;
            }, array());
        }
    }
