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
    private $nameRegex;
    private $tokens;
    private $tokensCount;
    private $position;
    /** @var int[] */
    private $backtracks;
    private $lastBacktrack;
    private $tokenMap;

    const TOKEN_OPEN = 1;
    const TOKEN_CLOSE = 2;
    const TOKEN_MARKER = 3;
    const TOKEN_SEPARATOR = 4;
    const TOKEN_DELIMITER = 5;
    const TOKEN_STRING = 6;
    const TOKEN_WS = 7;

    public function __construct(SyntaxInterface $syntax = null)
    {
        $this->lexerRegex = $this->prepareLexer($syntax ?: new CommonSyntax());
        $this->nameRegex = '~^'.RegexBuilderUtility::buildNameRegex().'$~us';
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
        if(!$this->match(self::TOKEN_OPEN, false)) { return false; }
        $offset = $this->tokens[$this->position - 1][2];
        $this->match(self::TOKEN_WS, false);
        if('' === $name = $this->match(self::TOKEN_STRING, false)) { return false; }
        if($this->lookahead(self::TOKEN_STRING)) { return false; }
        if(1 !== preg_match($this->nameRegex, $name, $matches)) { return false; }
        $this->match(self::TOKEN_WS, false);
        // bbCode
        $bbCode = $this->match(self::TOKEN_SEPARATOR, true) ? $this->value() : null;
        if(false === $bbCode) { return false; }
        // parameters
        if(false === ($parameters = $this->parameters())) { return false; }

        // self-closing
        if($this->match(self::TOKEN_MARKER, true)) {
            if(!$this->match(self::TOKEN_CLOSE, false)) { return false; }

            return array($this->getObject($name, $parameters, $bbCode, $offset, null, $this->getBacktrack()));
        }

        // just-closed or with-content
        if(!$this->match(self::TOKEN_CLOSE, false)) { return false; }
        $this->beginBacktrack();
        $names[] = $name;

        // begin inlined content()
        $content = '';
        $shortcodes = array();
        $closingName = null;

        while($this->position < $this->tokensCount) {
            while($this->position < $this->tokensCount && false === $this->lookahead(self::TOKEN_OPEN)) {
                $content .= $this->match(null, true);
            }

            $this->beginBacktrack();
            $contentMatchedShortcodes = $this->shortcode($names);
            if(\is_string($contentMatchedShortcodes)) {
                $closingName = $contentMatchedShortcodes;
                break;
            }
            if(\is_array($contentMatchedShortcodes)) {
                foreach($contentMatchedShortcodes as $matchedShortcode) {
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

            $content .= $this->match(null, false);
        }
        $content = $this->position < $this->tokensCount ? $content : false;
        // end inlined content()

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

    private function close(array &$names)
    {
        if(!$this->match(self::TOKEN_OPEN, true)) { return false; }
        if(!$this->match(self::TOKEN_MARKER, true)) { return false; }
        if(!$closingName = $this->match(self::TOKEN_STRING, true)) { return false; }
        if(!$this->match(self::TOKEN_CLOSE, false)) { return false; }

        return \in_array($closingName, $names, true) ? $closingName : false;
    }

    private function parameters()
    {
        $parameters = array();

        while(true) {
            $this->match(self::TOKEN_WS, false);
            if($this->lookahead(self::TOKEN_MARKER) || $this->lookahead(self::TOKEN_CLOSE)) { break; }
            if(!$name = $this->match(self::TOKEN_STRING, true)) { return false; }
            if(!$this->match(self::TOKEN_SEPARATOR, true)) { $parameters[$name] = null; continue; }
            if(false === ($value = $this->value())) { return false; }
            $this->match(self::TOKEN_WS, false);

            $parameters[$name] = $value;
        }

        return $parameters;
    }

    private function value()
    {
        $value = '';

        if($this->match(self::TOKEN_DELIMITER, false)) {
            while($this->position < $this->tokensCount && false === $this->lookahead(self::TOKEN_DELIMITER)) {
                $value .= $this->match(null, false);
            }

            return $this->match(self::TOKEN_DELIMITER, false) ? $value : false;
        }

        if($tmp = $this->match(self::TOKEN_STRING, false)) {
            $value .= $tmp;
            while($tmp = $this->match(self::TOKEN_STRING, false)) {
                $value .= $tmp;
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
        return $this->position < $this->tokensCount && $this->tokens[$this->position][0] === $type;
    }

    private function match($type, $ws)
    {
        if($this->position >= $this->tokensCount) {
            return '';
        }

        $token = $this->tokens[$this->position];
        if(!empty($type) && $token[0] !== $type) {
            return '';
        }

        $this->position++;
        if($ws && $this->position < $this->tokensCount && $this->tokens[$this->position][0] === self::TOKEN_WS) {
            $this->position++;
        }

        return $token[1];
    }

    /* --- LEXER ----------------------------------------------------------- */

    private function tokenize($text)
    {
        preg_match_all($this->lexerRegex, $text, $matches, PREG_OFFSET_CAPTURE);
        if(preg_last_error() !== PREG_NO_ERROR) {
            throw new \RuntimeException(sprintf('PCRE failure `%s`.', preg_last_error()));
        }

        $tokens = array();
        $position = 0;
        foreach($matches[0] as $match) {
            $type = isset($this->tokenMap[$match[0]])
                ? $this->tokenMap[$match[0]]
                : (ctype_space($match[0]) ? self::TOKEN_WS : self::TOKEN_STRING);
            $tokens[] = array($type, $match[0], $position);
            $position += mb_strlen($match[0], 'utf-8');
        }

        return $tokens;
    }

    private function prepareLexer(SyntaxInterface $syntax)
    {
        $this->tokenMap = array(
            $syntax->getOpeningTag() => self::TOKEN_OPEN,
            $syntax->getClosingTag() => self::TOKEN_CLOSE,
            $syntax->getClosingTagMarker() => self::TOKEN_MARKER,
            $syntax->getParameterValueSeparator() => self::TOKEN_SEPARATOR,
            $syntax->getParameterValueDelimiter() => self::TOKEN_DELIMITER,
        );

        $quote = function($text) {
            return preg_replace('/(.)/us', '\\\\$0', $text);
        };
        $symbols = array_map($quote, array_keys($this->tokenMap));

        return '~('.implode('|', $symbols).'|\s+|\\\\.|[\w-]+|.)~us';
    }
}
