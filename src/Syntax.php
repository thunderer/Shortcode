<?php
namespace Thunder\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Syntax
    {
    private $open;
    private $close;
    private $slash;
    private $equals;
    private $string;

    public function __construct($open = '[', $close = ']', $slash = '/', $equals = '=', $string = '"')
        {
        $this->open = $open;
        $this->close = $close;
        $this->slash = $slash;
        $this->equals = $equals;
        $this->string = $string;
        }

    public static function createDefaults()
        {
        return new self('[', ']', '/', '=', '"');
        }

    public function getShortcodeRegex()
        {
        $open = $this->quote($this->getOpen());
        $slash = $this->quote($this->getSlash());
        $close = $this->quote($this->getClose());

        return '~('.$open.'([\w-]+)(\s+.+?)?'.$close.'(?:(.+?)'.$open.$slash.'(\2)'.$close.')?)~us';
        }

    public function getSingleShortcodeRegex()
        {
        $open = $this->quote($this->getOpen());
        $slash = $this->quote($this->getSlash());
        $close = $this->quote($this->getClose());

        return '~^('.$open.'([\w-]+)(\s+.+?)?'.$close.'(?:(.+?)'.$open.$slash.'(\2)'.$close.')?)$~us';
        }

    public function getArgumentsRegex()
        {
        $equals = $this->quote($this->getEquals());
        $string = $this->quote($this->getString());

        return '~(?:\s+(\w+(?:(?=\s|$)|'.$equals.'\w+|'.$equals.$string.'.+'.$string.')))~us';
        }

    private function quote($text)
        {
        return preg_replace('/(.)/us', '\\\\$0', $text);
        }

    public function getOpen()
        {
        return $this->open;
        }

    public function getClose()
        {
        return $this->close;
        }

    public function getSlash()
        {
        return $this->slash;
        }

    public function getEquals()
        {
        return $this->equals;
        }

    public function getString()
        {
        return $this->string;
        }
    }
