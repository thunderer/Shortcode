<?php
namespace Thunder\Shortcode\Parser;

use Thunder\Shortcode\Shortcode\ParsedShortcode;
use Thunder\Shortcode\Shortcode\Shortcode;
use Thunder\Shortcode\Syntax\CommonSyntax;
use Thunder\Shortcode\Syntax\SyntaxInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class RegularParser implements ParserInterface
{
    private $lexerRules;
    private $tokens;
    private $tokensCount;
    private $position;
    private $backtracks;
    private $syntax;

    const TOKEN_OPEN = 1;
    const TOKEN_CLOSE = 2;
    const TOKEN_MARKER = 3;
    const TOKEN_SEPARATOR = 4;
    const TOKEN_DELIMITER = 5;
    const TOKEN_STRING = 6;
    const TOKEN_WS = 7;

    public function __construct(SyntaxInterface $syntax = null)
    {
        $this->syntax = $syntax ?: new CommonSyntax();

        $quote = function($text) { return '~^('.preg_replace('/(.)/us', '\\\\$0', $text).')~us'; };

        $this->lexerRules = array(
            self::TOKEN_OPEN => $quote($this->syntax->getOpeningTag()),
            self::TOKEN_CLOSE => $quote($this->syntax->getClosingTag()),
            self::TOKEN_MARKER => $quote($this->syntax->getClosingTagMarker()),
            self::TOKEN_SEPARATOR => $quote($this->syntax->getParameterValueSeparator()),
            self::TOKEN_DELIMITER => $quote($this->syntax->getParameterValueDelimiter()),
            self::TOKEN_WS => '~^(\s+)~us',
            self::TOKEN_STRING => '~^([\w-]+|\\\\.|.)~us',
        );
    }

    /**
     * @param string $text
     *
     * @return ParsedShortcode[]
     */
    public function parse($text)
    {
        $this->tokens = $this->tokenize($text);
        $this->backtracks = array();
        $this->position = 0;
        $this->tokensCount = count($this->tokens);

        $shortcodes = array();
        while($this->position < $this->tokensCount) {
            while($this->position < $this->tokensCount && !$this->lookahead(self::TOKEN_OPEN)) {
                $this->position++;
            }
            foreach($this->shortcode(true) ?: array() as $shortcode) {
                $shortcodes[] = $shortcode;
            }
        }

        return $shortcodes;
    }

    private function getObject($name, $parameters, $bbCode, $offset, $content, $text)
    {
        return new ParsedShortcode(new Shortcode($name, $parameters, $content, $bbCode), $text, $offset);
    }

    /* --- RULES ----------------------------------------------------------- */

    private function shortcode($isRoot)
    {
        $name = null;
        $offset = null;

        $setName = function(array $token) use(&$name) { $name = $token[1]; };
        $setOffset = function(array $token) use(&$offset) { $offset = $token[2]; };

        $isRoot && $this->beginBacktrack();
        if(!$this->match(self::TOKEN_OPEN, $setOffset, true)) { return false; }
        if(!$this->match(self::TOKEN_STRING, $setName, false)) { return false; }
        if($this->lookahead(self::TOKEN_STRING, null)) { return false; }
        if(!preg_match_all('/^[a-zA-Z0-9-]+$/', $name, $matches)) { return false; }
        $this->match(self::TOKEN_WS);
        if(false === ($bbCode = $this->bbCode())) { return false; }
        if(false === ($parameters = $this->parameters())) { return false; }

        // self-closing
        if($this->match(self::TOKEN_MARKER, null, true)) {
            if(!$this->match(self::TOKEN_CLOSE)) { return false; }

            return array($this->getObject($name, $parameters, $bbCode, $offset, null, $this->getBacktrack()));
        }

        // just-closed or with-content
        if(!$this->match(self::TOKEN_CLOSE)) { return false; }
        $this->beginBacktrack();
        list($content, $shortcodes) = $this->content($name);
        if(false === $content) {
            $this->backtrackWithoutPositionAndReturn();
            $text = $this->backtrackWithoutPositionAndReturn();
            return array_merge(array($this->getObject($name, $parameters, $bbCode, $offset, null, $text)), $shortcodes);
        }
        array_pop($this->backtracks);
        if(!$this->close($name)) { return false; }

        return array($this->getObject($name, $parameters, $bbCode, $offset, $content, $this->getBacktrack()));
    }

    private function content($name)
    {
        $content = null;
        $shortcodes = array();
        $appendContent = function(array $token) use(&$content) { $content .= $token[1]; };

        while($this->position < $this->tokensCount) {
            while($this->match(array(self::TOKEN_STRING, self::TOKEN_WS), $appendContent)) {
                continue;
            }

            $this->beginBacktrack();
            $matchedShortcodes = $this->shortcode(false);
            if(false !== $matchedShortcodes) {
                $shortcodes = array_merge($shortcodes, $matchedShortcodes);
                continue;
            }
            $this->backtrack();

            $this->beginBacktrack();
            if(false !== $this->close($name)) {
                if(null === $content) { $content = ''; }
                $this->backtrack();
                $shortcodes = array();
                break;
            }
            $this->backtrack();
            if($this->position < $this->tokensCount) {
                $shortcodes = array();
                break;
            }

            $this->match(null, $appendContent);
        }

        return array($this->position < $this->tokensCount ? $content : false, $shortcodes);
    }

    private function close($openingName)
    {
        $closingName = null;
        $setName = function(array $token) use(&$closingName) { $closingName = $token[1]; };

        if(!$this->match(self::TOKEN_OPEN, null, true)) { return false; }
        if(!$this->match(self::TOKEN_MARKER, null, true)) { return false; }
        if(!$this->match(self::TOKEN_STRING, $setName, true)) { return false; }
        if(!$this->match(self::TOKEN_CLOSE)) { return false; }

        return $openingName === $closingName;
    }

    private function bbCode()
    {
        return $this->match(self::TOKEN_SEPARATOR, null, true) ? $this->value() : null;
    }

    private function parameters()
    {
        $parameters = array();
        $setName = function(array $token) use(&$name) { $name = $token[1]; };

        while(true) {
            $name = null;

            $this->match(self::TOKEN_WS);
            if($this->lookahead(array(self::TOKEN_MARKER, self::TOKEN_CLOSE))) { break; }
            if(!$this->match(self::TOKEN_STRING, $setName, true)) { return false; }
            if(!$this->match(self::TOKEN_SEPARATOR, null, true)) { $parameters[$name] = null; continue; }
            if(false === ($value = $this->value())) { return false; }
            $this->match(self::TOKEN_WS);

            $parameters[$name] = $value;
        }

        return $parameters;
    }

    private function value()
    {
        $value = '';
        $appendValue = function(array $token) use(&$value) { $value .= $token[1]; };

        if($this->match(self::TOKEN_DELIMITER)) {
            while($this->position < $this->tokensCount && !$this->lookahead(self::TOKEN_DELIMITER)) {
                $this->match(null, $appendValue);
            }

            return $this->match(self::TOKEN_DELIMITER) ? $value : false;
        }

        return $this->match(self::TOKEN_STRING, $appendValue) ? $value : false;
    }

    /* --- PARSER ---------------------------------------------------------- */

    public function backtracksState()
    {
        return array_map(function(array $b) { return implode('', array_column($b, 1)); }, $this->backtracks);
    }

    private function beginBacktrack()
    {
        $this->backtracks[] = array();
    }

    private function getSafeBacktrack()
    {
        // switch from array_map() to array_column() when dropping support for PHP <5.5
        return implode('', array_map(function(array $token) { return $token[1]; }, end($this->backtracks)));
    }

    private function getBacktrack()
    {
        // switch from array_map() to array_column() when dropping support for PHP <5.5
        return implode('', array_map(function(array $token) { return $token[1]; }, array_pop($this->backtracks)));
    }

    private function backtrackWithoutPositionAndReturn()
    {
        $tokens = array_pop($this->backtracks);
        $count = count($tokens);

        foreach($this->backtracks as &$backtrack) {
            // array_pop() in loop is much faster than array_slice() because
            // it operates directly on the passed array
            for($i = 0; $i < $count; $i++) {
                array_pop($backtrack);
            }
        }

        return implode('', array_map(function(array $token) { return $token[1]; }, $tokens));
    }

    private function backtrack()
    {
        $tokens = array_pop($this->backtracks);
        $count = count($tokens);
        $this->position -= $count;

        foreach($this->backtracks as &$backtrack) {
            // array_pop() in loop is much faster than array_slice() because
            // it operates directly on the passed array
            for($i = 0; $i < $count; $i++) {
                array_pop($backtrack);
            }
        }
    }

    private function lookahead($type, $callback = null)
    {
        if($this->position >= $this->tokensCount) {
            return false;
        }

        $type = (array)$type;
        $token = $this->tokens[$this->position];
        if(!empty($type) && !in_array($token[0], $type)) {
            return false;
        }

        /** @var $callback callable */
        $callback && $callback($token);

        return true;
    }

    private function match($type, $callbacks = null, $ws = false)
    {
        if($this->position >= $this->tokensCount) {
            return false;
        }

        $type = (array)$type;
        $token = $this->tokens[$this->position];
        if(!empty($type) && !in_array($token[0], $type)) {
            return false;
        }
        foreach($this->backtracks as &$backtrack) {
            $backtrack[] = $token;
        }

        $this->position++;
        foreach((array)$callbacks as $callback) {
            $callback($token);
        }

        $ws && $this->match(self::TOKEN_WS);

        return true;
    }

    /* --- LEXER ----------------------------------------------------------- */

    private function tokenize($text)
    {
        $tokens = array();
        // performance improvement: start generating tokens after first opening
        // tag position because it's impossible to find shortcode earlier
        $position = mb_strpos($text, $this->syntax->getOpeningTag());
        $text = mb_substr($text, $position);

        while(mb_strlen($text) > 0) {
            foreach($this->lexerRules as $token => $regex) {
                if(preg_match($regex, $text, $matches)) {
                    $tokens[] = array($token, $matches[0], $position);
                    $text = mb_substr($text, mb_strlen($matches[0]));
                    $position += mb_strlen($matches[0], 'utf-8');
                    break;
                }
            }
        }

        return $tokens;
    }
}
