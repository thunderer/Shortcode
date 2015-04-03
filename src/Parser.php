<?php
namespace Thunder\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Parser
    {
    const SHORTCODE_REGEX = '/(\[(\w+)(\s+.+?)?\](?:(.+)\[\/(\2)\])?)/us';
    const ARGUMENTS_REGEX = '/(?:\s+(\w+(?:(?=\s|$)|=\w+|=".+")))/us';

    private $codes = array();

    public function __construct()
        {
        }

    public function addCode($name, callable $handler)
        {
        if($this->hasCode($name))
            {
            $msg = 'Code %s already exists!';
            throw new \RuntimeException(sprintf($msg, $name));
            }

        $this->codes[$name] = $handler;
        }

    public function parse($text)
        {
        return preg_replace_callback(self::SHORTCODE_REGEX, function(array $matches) {
            return $this->hasCode($matches[2])
                ? call_user_func_array($this->codes[$matches[2]], array(new Shortcode(
                    $matches[2],
                    isset($matches[3]) ? $this->parseParameters($matches[3]) : array(),
                    isset($matches[4]) ? $matches[4] : null
                    )))
                : $matches[0];
            }, $text);
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

    private function hasCode($name)
        {
        return array_key_exists($name, $this->codes);
        }
    }
