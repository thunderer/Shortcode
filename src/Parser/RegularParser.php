<?php
namespace Thunder\Shortcode\Parser;

use Thunder\Shortcode\Shortcode\ParsedShortcode;
use Thunder\Shortcode\Shortcode\Shortcode;
use Thunder\Shortcode\Syntax\CommonSyntax;
use Thunder\Shortcode\Syntax\SyntaxInterface;
use Thunder\Shortcode\Utility\RegexBuilderUtility;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class RegularParser implements ParserInterface
{
    private $lexerRegex;
    private $tokens;
    private $tokensCount;
    private $position;
    /** @var int[] */
    private $backtracks;
    private $lastBacktrack;

    const TOKEN_OPEN = 1;
    const TOKEN_CLOSE = 2;
    const TOKEN_MARKER = 3;
    const TOKEN_SEPARATOR = 4;
    const TOKEN_DELIMITER = 5;
    const TOKEN_STRING = 6;
    const TOKEN_WS = 7;

    public function __construct(SyntaxInterface $syntax = null)
    {
        $this->lexerRegex = $this->getTokenizerRegex($syntax ?: new CommonSyntax());
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
        $this->lastBacktrack = 0;
        $this->position = 0;
        $this->tokensCount = \count($this->tokens);

        $shortcodes = array();
        while($this->position < $this->tokensCount) {
            while($this->position < $this->tokensCount && false === $this->lookahead(self::TOKEN_OPEN)) {
                $this->position++;
            }
            $names = array();
            $this->beginBacktrack();
            $matches = $this->shortcode($names);
            if(\is_array($matches)) {
                foreach($matches as $shortcode) {
                    $shortcodes[] = $shortcode;
                }
            }
        }

        return $shortcodes;
    }

    private function getObject($name, $parameters, $bbCode, $offset, $content, $text)
    {
        return new ParsedShortcode(new Shortcode($name, $parameters, $content, $bbCode), $text, $offset);
    }

    /* --- RULES ----------------------------------------------------------- */

    private function shortcode(array &$names)
    {
        $name = null;
        $offset = null;

        $setName = function(array $token) use(&$name) { $name = $token[1]; };
        $setOffset = function(array $token) use(&$offset) { $offset = $token[2]; };

        if(!$this->match(self::TOKEN_OPEN, $setOffset, true)) { return false; }
        if(!$this->match(self::TOKEN_STRING, $setName, false)) { return false; }
        if($this->lookahead(self::TOKEN_STRING)) { return false; }
        if(!preg_match_all('~^'.RegexBuilderUtility::buildNameRegex().'$~us', $name, $matches)) { return false; }
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
        $names[] = $name;
        list($content, $shortcodes, $closingName) = $this->content($names);
        if(null !== $closingName && $closingName !== $name) {
            array_pop($names);
            array_pop($this->backtracks);
            array_pop($this->backtracks);

            return $closingName;
        }
        if(false === $content || $closingName !== $name) {
            $this->backtrack(false);
            $text = $this->backtrack(false);

            return array_merge(array($this->getObject($name, $parameters, $bbCode, $offset, null, $text)), $shortcodes);
        }
        $content = $this->getBacktrack();
        if(!$this->close($names)) { return false; }

        return array($this->getObject($name, $parameters, $bbCode, $offset, $content, $this->getBacktrack()));
    }

    private function content(array &$names)
    {
        $content = null;
        $shortcodes = array();
        $closingName = null;
        $appendContent = function(array $token) use(&$content) { $content .= $token[1]; };

        while($this->position < $this->tokensCount) {
            while($this->position < $this->tokensCount && false === $this->lookahead(self::TOKEN_OPEN)) {
                $this->match(null, $appendContent, true);
            }

            $this->beginBacktrack();
            $matchedShortcodes = $this->shortcode($names);
            if(\is_string($matchedShortcodes)) {
                $closingName = $matchedShortcodes;
                break;
            }
            if(\is_array($matchedShortcodes)) {
                foreach($matchedShortcodes as $matchedShortcode) {
                    $shortcodes[] = $matchedShortcode;
                }
                continue;
            }
            $this->backtrack();

            $this->beginBacktrack();
            if(false !== ($closingName = $this->close($names))) {
                if(null === $content) { $content = ''; }
                $this->backtrack();
                $shortcodes = array();
                break;
            }
            $closingName = null;
            $this->backtrack();

            $this->match(null, $appendContent);
        }

        return array($this->position < $this->tokensCount ? $content : false, $shortcodes, $closingName);
    }

    private function close(array &$names)
    {
        $closingName = null;
        $setName = function(array $token) use(&$closingName) { $closingName = $token[1]; };

        if(!$this->match(self::TOKEN_OPEN, null, true)) { return false; }
        if(!$this->match(self::TOKEN_MARKER, null, true)) { return false; }
        if(!$this->match(self::TOKEN_STRING, $setName, true)) { return false; }
        if(!$this->match(self::TOKEN_CLOSE)) { return false; }

        return \in_array($closingName, $names, true) ? $closingName : false;
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
            if($this->lookahead(self::TOKEN_MARKER) || $this->lookahead(self::TOKEN_CLOSE)) { break; }
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
            while($this->position < $this->tokensCount && false === $this->lookahead(self::TOKEN_DELIMITER)) {
                $this->match(null, $appendValue);
            }

            return $this->match(self::TOKEN_DELIMITER) ? $value : false;
        }

        if($this->match(self::TOKEN_STRING, $appendValue)) {
            while($this->match(self::TOKEN_STRING, $appendValue)) {
                continue;
            }

            return $value;
        }

        return false;
    }

    /* --- PARSER ---------------------------------------------------------- */

    private function beginBacktrack()
    {
        $this->backtracks[] = $this->position;
        $this->lastBacktrack = $this->position;
    }

    private function getBacktrack()
    {
        $position = array_pop($this->backtracks);
        $backtrack = '';
        for($i = $position; $i < $this->position; $i++) {
            $backtrack .= $this->tokens[$i][1];
        }

        return $backtrack;
    }

    private function backtrack($modifyPosition = true)
    {
        $position = array_pop($this->backtracks);
        if($modifyPosition) {
            $this->position = $position;
        }

        $backtrack = '';
        for($i = $position; $i < $this->lastBacktrack; $i++) {
            $backtrack .= $this->tokens[$i][1];
        }
        $this->lastBacktrack = $position;

        return $backtrack;
    }

    private function lookahead($type)
    {
        return $this->position < $this->tokensCount && (empty($type) || $this->tokens[$this->position][0] === $type);
    }

    private function match($type, $callback = null, $ws = false)
    {
        if($this->position >= $this->tokensCount) {
            return false;
        }

        $token = $this->tokens[$this->position];
        if(!empty($type) && $token[0] !== $type) {
            return false;
        }

        /** @var callable $callback */
        $callback && $callback($token);
        $this->position++;

        if($ws && $this->position < $this->tokensCount && $this->tokens[$this->position][0] === self::TOKEN_WS) {
            $this->position++;
        }

        return true;
    }

    /* --- LEXER ----------------------------------------------------------- */

    private function tokenize($text)
    {
        preg_match_all($this->lexerRegex, $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        $tokens = array();
        $position = 0;

        foreach($matches as $match) {
            switch(true) {
                case -1 !== $match['open'][1]: { $token = $match['open'][0]; $type = self::TOKEN_OPEN; break; }
                case -1 !== $match['close'][1]: { $token = $match['close'][0]; $type = self::TOKEN_CLOSE; break; }
                case -1 !== $match['marker'][1]: { $token = $match['marker'][0]; $type = self::TOKEN_MARKER; break; }
                case -1 !== $match['separator'][1]: { $token = $match['separator'][0]; $type = self::TOKEN_SEPARATOR; break; }
                case -1 !== $match['delimiter'][1]: { $token = $match['delimiter'][0]; $type = self::TOKEN_DELIMITER; break; }
                case -1 !== $match['ws'][1]: { $token = $match['ws'][0]; $type = self::TOKEN_WS; break; }
                default: { $token = $match['string'][0]; $type = self::TOKEN_STRING; }
            }
            $tokens[] = array($type, $token, $position);
            $position += mb_strlen($token, 'utf-8');
        }

        return $tokens;
    }

    private function getTokenizerRegex(SyntaxInterface $syntax)
    {
        $group = function($text, $group) {
            return '(?<'.$group.'>'.preg_replace('/(.)/us', '\\\\$0', $text).')';
        };
        $quote = function($text) {
            return preg_replace('/(.)/us', '\\\\$0', $text);
        };

        $symbols = array_map($quote, [
            $syntax->getOpeningTag(),
            $syntax->getClosingTag(),
            $syntax->getClosingTagMarker(),
            $syntax->getParameterValueSeparator(),
            $syntax->getParameterValueDelimiter(),
        ]);
        $rules = array(
            $group($syntax->getOpeningTag(), 'open'),
            $group($syntax->getClosingTag(), 'close'),
            $group($syntax->getClosingTagMarker(), 'marker'),
            $group($syntax->getParameterValueSeparator(), 'separator'),
            $group($syntax->getParameterValueDelimiter(), 'delimiter'),
            '(?<ws>\s+)',
            '(?<string>(?:(?!'.implode('|', $symbols).'|\s)(?:\\\\.|.))+)',
        );

        return '~('.implode('|', $rules).')~us';
    }
}
