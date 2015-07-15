<?php
namespace Thunder\Shortcode\Utility;

use Thunder\Shortcode\Syntax\SyntaxInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 * @internal Utility for regex-related parts of this library
 */
final class RegexBuilderUtility
    {
    public static function buildShortcodeRegex(SyntaxInterface $syntax)
        {
        return '~('.static::createShortcodeRegexContent($syntax).')~us';
        }

    public static function buildSingleShortcodeRegex(SyntaxInterface $syntax)
        {
        return '~(\A'.static::createShortcodeRegexContent($syntax).'\Z)~us';
        }

    public static function buildArgumentsRegex(SyntaxInterface $syntax)
        {
        $equals = static::quote($syntax->getParameterValueSeparator());
        $string = static::quote($syntax->getParameterValueDelimiter());

        $ws = '\s*';
        // lookahead test for either space or end of string
        $empty = '(?=\s|$)';
        // equals sign and alphanumeric value
        $simple = $ws.$equals.$ws.'[^\s]+';
        // equals sign and value without unescaped string delimiters enclosed in them
        $complex = $ws.$equals.$ws.$string.'([^'.$string.'\\\\]*(?:\\\\.[^'.$string.'\\\\]*)*?)'.$string;

        return '~(?:\s*(\w+(?:'.$complex.'|'.$simple.'|'.$empty.')))~us';
        }

    private static function createShortcodeRegexContent(SyntaxInterface $syntax)
        {
        $open = static::quote($syntax->getOpeningTag());
        $slash = static::quote($syntax->getClosingTagMarker());
        $close = static::quote($syntax->getClosingTag());
        $equals = static::quote($syntax->getParameterValueSeparator());
        $string = static::quote($syntax->getParameterValueDelimiter());

        $ws = '\s*';

        // lookahead test for space, closing tag, self-closing tag or end of string
        $empty = '(?=\s|'.$close.'|'.$slash.$ws.$close.'|$)';
        // equals sign and alphanumeric value
        $simple = $ws.$equals.$ws.'(?:[^\s'.$close.']+)';
        // equals sign and value without unescaped string delimiters enclosed in them
        $complex = $ws.$equals.$ws.$string.'(?:[^'.$string.'\\\\]*(?:\\\\.[^'.$string.'\\\\]*)*)'.$string;
        // complete parameters matching regex
        $parameters = '((?:\s*(?:\w+(?:'.$complex.'|'.$simple.'|'.$empty.')))*)';

        // alphanumeric characters and dash
        $name = '([\w-]+)';
        // non-greedy match for any characters
        $content = '(.*?)';

        // equal beginning for each variant: open tag, name and parameters
        $common = $open.$ws.$name.$parameters.$ws;
        // closing tag variants: just closing tag, self closing tag or content
        // and closing block with backreference name validation
        $justClosed = $close;
        $selfClosed  = $slash.$ws.$close;
        $withContent = $close.$content.$open.$ws.$slash.$ws.'(\2)'.$ws.$close;

        return '(?:'.$common.'(?:'.$withContent.'|'.$justClosed.'|'.$selfClosed.'))';
        }

    private static function quote($text)
        {
        return preg_replace('/(.)/us', '\\\\$0', $text);
        }
    }
