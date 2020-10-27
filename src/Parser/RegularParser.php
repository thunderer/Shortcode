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
    /** @var string */
    private $lexerRegex;
    /** @var string */
    private $nameRegex;
    /** @psalm-var list<array{0:int,1:string,2:int}> */
    private $tokens = array();
    /** @var int */
    private $tokensCount = 0;
    /** @var int */
    private $position = 0;
    /** @var int[] */
    private $backtracks = array();
    /** @var int */
    private $lastBacktrack = 0;

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
        $nestingLevel = ini_set('xdebug.max_nesting_level', '-1');
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
            if(false === $matches) {
                $this->backtrack();
                $this->match(null, true);
                continue;
            }
            if(\is_array($matches)) {
                foreach($matches as $shortcode) {
                    $shortcodes[] = $shortcode;
                }
            }
        }
        ini_set('xdebug.max_nesting_level', $nestingLevel);

        return $shortcodes;
    }

    /**
     * @param string $name
     * @psalm-param array<string,string|null> $parameters
     * @param string|null $bbCode
     * @param int $offset
     * @param string|null $content
     * @param string $text
     *
     * @return ParsedShortcode
     */
    private function getObject($name, $parameters, $bbCode, $offset, $content, $text)
    {
        return new ParsedShortcode(new Shortcode($name, $parameters, $content, $bbCode), $text, $offset);
    }

    /* --- RULES ----------------------------------------------------------- */

    /**
     * @param string[] $names
     * @psalm-param list<string> $names
     * FIXME: investigate the reason Psalm complains about references
     * @psalm-suppress ReferenceConstraintViolation
     *
     * @return ParsedShortcode[]|string|false
     */
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
            /** @psalm-suppress MixedArgumentTypeCoercion */
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
            array_pop($names);

            return array_merge(array($this->getObject($name, $parameters, $bbCode, $offset, null, $text)), $shortcodes);
        }
        $content = $this->getBacktrack();
        if(!$this->close($names)) { return false; }
        array_pop($names);

        return array($this->getObject($name, $parameters, $bbCode, $offset, $content, $this->getBacktrack()));
    }

    /**
     * @param string[] $names
     *
     * @return string|false
     */
    private function close(array &$names)
    {
        if(!$this->match(self::TOKEN_OPEN, true)) { return false; }
        if(!$this->match(self::TOKEN_MARKER, true)) { return false; }
        if(!$closingName = $this->match(self::TOKEN_STRING, true)) { return false; }
        if(!$this->match(self::TOKEN_CLOSE, false)) { return false; }

        return \in_array($closingName, $names, true) ? $closingName : false;
    }

    /** @psalm-return array<string,string|null>|false */
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

    /** @return false|string */
    private function value()
    {
        $value = '';

        if($this->match(self::TOKEN_DELIMITER, false)) {
            while($this->position < $this->tokensCount && false === $this->lookahead(self::TOKEN_DELIMITER)) {
                $value .= $this->match(null, false);
            }

            return $this->match(self::TOKEN_DELIMITER, false) ? $value : false;
        }

        if('' !== $tmp = $this->match(self::TOKEN_STRING, false)) {
            $value .= $tmp;
            while('' !== $tmp = $this->match(self::TOKEN_STRING, false)) {
                $value .= $tmp;
            }

            return $value;
        }

        return false;
    }

    /* --- PARSER ---------------------------------------------------------- */

    /** @return void */
    private function beginBacktrack()
    {
        $this->backtracks[] = $this->position;
        $this->lastBacktrack = $this->position;
    }

    /** @return string */
    private function getBacktrack()
    {
        $position = array_pop($this->backtracks);
        $backtrack = '';
        for($i = $position; $i < $this->position; $i++) {
            $backtrack .= $this->tokens[$i][1];
        }

        return $backtrack;
    }

    /**
     * @param bool $modifyPosition
     *
     * @return string
     */
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

    /**
     * @param int $type
     *
     * @return bool
     */
    private function lookahead($type)
    {
        return $this->position < $this->tokensCount && $this->tokens[$this->position][0] === $type;
    }

    /**
     * @param int|null $type
     * @param bool $ws
     *
     * @return string
     */
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

    /**
     * @param string $text
     *
     * @psalm-return list<array{0:int,1:string,2:int}>
     */
    private function tokenize($text)
    {
        $count = preg_match_all($this->lexerRegex, $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        if(false === $count || preg_last_error() !== PREG_NO_ERROR) {
            throw new \RuntimeException(sprintf('PCRE failure `%s`.', preg_last_error()));
        }

        $tokens = array();
        $position = 0;

        foreach($matches as $match) {
            switch(true) {
                case -1 !== $match['string'][1]: { $token = $match['string'][0]; $type = self::TOKEN_STRING; break; }
                case -1 !== $match['ws'][1]: { $token = $match['ws'][0]; $type = self::TOKEN_WS; break; }
                case -1 !== $match['marker'][1]: { $token = $match['marker'][0]; $type = self::TOKEN_MARKER; break; }
                case -1 !== $match['delimiter'][1]: { $token = $match['delimiter'][0]; $type = self::TOKEN_DELIMITER; break; }
                case -1 !== $match['separator'][1]: { $token = $match['separator'][0]; $type = self::TOKEN_SEPARATOR; break; }
                case -1 !== $match['open'][1]: { $token = $match['open'][0]; $type = self::TOKEN_OPEN; break; }
                case -1 !== $match['close'][1]: { $token = $match['close'][0]; $type = self::TOKEN_CLOSE; break; }
                default: { throw new \RuntimeException(sprintf('Invalid token.')); }
            }
            $tokens[] = array($type, $token, $position);
            $position += mb_strlen($token, 'utf-8');
        }

        return $tokens;
    }

    /** @return string */
    private function prepareLexer(SyntaxInterface $syntax)
    {
        // FIXME: for some reason Psalm does not understand the `@psalm-var callable() $var` annotation
        /** @psalm-suppress MissingClosureParamType, MissingClosureReturnType */
        $group = function($text, $group) {
            return '(?<'.(string)$group.'>'.preg_replace('/(.)/us', '\\\\$0', (string)$text).')';
        };
        /** @psalm-suppress MissingClosureParamType, MissingClosureReturnType */
        $quote = function($text) {
            return preg_replace('/(.)/us', '\\\\$0', (string)$text);
        };

        $rules = array(
            '(?<string>\\\\.|(?:(?!'.implode('|', array(
                $quote($syntax->getOpeningTag()),
                $quote($syntax->getClosingTag()),
                $quote($syntax->getClosingTagMarker()),
                $quote($syntax->getParameterValueSeparator()),
                $quote($syntax->getParameterValueDelimiter()),
                '\s+',
            )).').)+)',
            '(?<ws>\s+)',
            $group($syntax->getClosingTagMarker(), 'marker'),
            $group($syntax->getParameterValueDelimiter(), 'delimiter'),
            $group($syntax->getParameterValueSeparator(), 'separator'),
            $group($syntax->getOpeningTag(), 'open'),
            $group($syntax->getClosingTag(), 'close'),
        );

        return '~('.implode('|', $rules).')~us';
    }
}
