<?php
namespace Thunder\Shortcode;

class Shortcode
    {
    const SHORTCODE_REGEX = '/(\[(\w+)(\s+.+?)?\](?:(.+)\[\/(\2)\])?)/us';
    const ARGUMENTS_REGEX = '/(?:\s+(\w+(?:(?=\s|\]|$)|=\w+|=".+")))/us';

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
        $return = preg_replace_callback(self::SHORTCODE_REGEX, function(array $matches) {
            $name = $matches[2];
            $args = array();
            if(isset($matches[3]))
                {
                preg_match_all(self::ARGUMENTS_REGEX, $matches[3], $argsMatches);
                $args = array_reduce($argsMatches[1], function (array $state, $item)
                    {
                    $values = explode('=', $item, 2);
                    $value = isset($values[1]) ? $values[1] : null;
                    $value = $value && $value[0] == '"' && $value[strlen($value) - 1] == '"'
                        ? substr($value, 1, -1)
                        : (isset($value) ? $value : null);
                    $state[$values[0]] = $value;

                    return $state;
                    }, array());
                }
            $content = isset($matches[4]) ? $matches[4] : null;
            $closing = isset($matches[5]) ? $matches[5] : null;

            return $this->hasCode($name)
                ? call_user_func_array($this->getCode($name), array($name, $args, $content))
                : $matches[0];
            }, $text);

        return $return;
        }

    private function getCode($name)
        {
        return $this->codes[$name];
        }

    private function hasCode($name)
        {
        return array_key_exists($name, $this->codes);
        }
    }
