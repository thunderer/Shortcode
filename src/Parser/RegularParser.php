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
    /** @var \SplStack */
    private $tokens;
    private $backtracks;

    const TOKEN_OPEN = 1;
    const TOKEN_CLOSE = 2;
    const TOKEN_MARKER = 3;
    const TOKEN_SEPARATOR = 4;
    const TOKEN_DELIMITER = 5;
    const TOKEN_STRING = 6;
    const TOKEN_WS = 7;

    public function __construct(SyntaxInterface $syntax = null)
    {
        $syntax = $syntax ?: new CommonSyntax();

        $quote = function($text) { return '~^('.preg_replace('/(.)/us', '\\\\$0', $text).')~us'; };

        $this->lexerRules = array(
            self::TOKEN_OPEN => $quote($syntax->getOpeningTag()),
            self::TOKEN_CLOSE => $quote($syntax->getClosingTag()),
            self::TOKEN_MARKER => $quote($syntax->getClosingTagMarker()),
            self::TOKEN_SEPARATOR => $quote($syntax->getParameterValueSeparator()),
            self::TOKEN_DELIMITER => $quote($syntax->getParameterValueDelimiter()),
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

        $shortcodes = array();
        while(false === $this->isEof()) {
            while(!$this->isEof() && !$this->lookahead(self::TOKEN_OPEN)) {
                $this->tokens->pop();
            }
            if($shortcode = $this->shortcode(true)) {
                $shortcodes[] = $shortcode;
            }
        }

        return $shortcodes;
    }

    private function getObject($name, $parameters, $bbCode, $offset, $content)
    {
        return new ParsedShortcode(new Shortcode($name, $parameters, $content, $bbCode), $this->getBacktrack(), $offset);
    }

    /* --- RULES ----------------------------------------------------------- */

    private function shortcode($isRoot)
    {
        $name = null;
        $offset = null;

        $setName = function(array $token) use(&$name) { $name = $token[1]; };
        $setOffset = function(array $token) use(&$offset) { $offset = $token[2]; };

        !$isRoot ?: $this->beginBacktrack();
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

            return $isRoot ? $this->getObject($name, $parameters, $bbCode, $offset, null) : null;
        }

        // just-closed or with-content
        if(!$this->match(self::TOKEN_CLOSE)) { return false; }
        $this->beginBacktrack();
        if(false === ($content = $this->content($name))) {
            $this->backtrack();

            return $isRoot ? $this->getObject($name, $parameters, $bbCode, $offset, null) : null;
        }
        $this->discardBacktrack();
        if(!$this->close($name)) { return false; }

        return $isRoot ? $this->getObject($name, $parameters, $bbCode, $offset, $content) : null;
    }

    private function content($name)
    {
        $content = null;
        $appendContent = function(array $token) use(&$content) { $content .= $token[1]; };

        while(!$this->isEof()) {
            while($this->match(array(self::TOKEN_STRING, self::TOKEN_WS), $appendContent)) {
                continue;
            }

            $this->beginBacktrack();
            if(false !== $this->shortcode(false)) {
                $content .= $this->getBacktrack();
                continue;
            }
            $this->backtrack();

            $this->beginBacktrack();
            if(false !== $this->close($name)) {
                if(null === $content) { $content = ''; }
                $this->backtrack();
                break;
            }
            $this->backtrack();

            $this->match(null, $appendContent);
        }

        return $this->isEof() ? false : $content;
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
            while(!$this->isEof() && !$this->lookahead(self::TOKEN_DELIMITER)) {
                $this->match(null, $appendValue);
            }

            return $this->match(self::TOKEN_DELIMITER) ? $value : false;
        }

        return $this->match(self::TOKEN_STRING, $appendValue) ? $value : false;
    }

    /* --- PARSER ---------------------------------------------------------- */

    private function discardBacktrack()
    {
        return array_pop($this->backtracks);
    }

    private function beginBacktrack()
    {
        array_push($this->backtracks, array());
    }

    private function getBacktrack()
    {
        return implode('', array_map(function(array $token) { return $token[1]; }, $this->discardBacktrack()));
    }

    private function backtrack()
    {
        foreach(array_reverse($this->discardBacktrack()) as $token) {
            $this->tokens->push($token);

            foreach($this->backtracks as &$backtrack) {
                array_pop($backtrack);
            }
        }
    }

    private function isEof()
    {
        return $this->tokens->isEmpty();
    }

    private function lookahead($type, $callback = null)
    {
        if($this->isEof()) {
            return false;
        }

        $type = (array)$type;
        $token = $this->tokens->top();
        if(!empty($type) && !in_array($token[0], $type)) {
            return false;
        }

        /** @var $callback callable */
        !$callback ?: $callback($token);

        return true;
    }

    private function match($type, $callbacks = null, $ws = false)
    {
        if($this->isEof()) {
            return false;
        }

        $type = (array)$type;
        $token = $this->tokens->top();
        if(!empty($type) && !in_array($token[0], $type)) {
            return false;
        }
        foreach($this->backtracks as &$backtrack) {
            array_push($backtrack, $token);
        }

        $this->tokens->pop();
        foreach((array)$callbacks as $callback) {
            $callback($token);
        }

        !$ws ?: $this->match(self::TOKEN_WS);

        return true;
    }

    /* --- LEXER ----------------------------------------------------------- */

    private function tokenize($text)
    {
        $tokens = new \SplStack();
        $position = 0;

        while(mb_strlen($text) > 0) {
            foreach($this->lexerRules as $token => $regex) {
                if(preg_match($regex, $text, $matches)) {
                    $tokens->unshift(array($token, $matches[0], $position));
                    $text = mb_substr($text, mb_strlen($matches[0]));
                    $position += mb_strlen($matches[0]);
                    break;
                }
            }
        }

        return $tokens;
    }
}
