<?php
namespace Thunder\Shortcode\Parser;

use Thunder\Shortcode\Shortcode\ParsedShortcode;
use Thunder\Shortcode\Shortcode\Shortcode;
use Thunder\Shortcode\Syntax\CommonSyntax;
use Thunder\Shortcode\Syntax\SyntaxInterface;

/**
 * HybridParser - a fast, robust shortcode parser.
 *
 * Strategy: a single PCRE pass lexes every individual shortcode tag (both
 * opening and closing) in C, then a linear stack-based pass resolves nesting
 * in PHP. This combines RegexParser's raw scanning speed with RegularParser's
 * robustness: the lexer regex understands quoted values and escapes, so an
 * unterminated quote like `[a k="v]` correctly fails to lex as a tag instead
 * of inventing a bogus parameter. Nesting, mismatched closing tags and
 * open-only shortcodes are then resolved exactly like the default parser.
 *
 * @author Andy Miller
 *
 * @psalm-type HybridNode = array{
 *     0: string, 1: string, 2: string|null, 3: int, 4: int, 5: int,
 *     6: int|null, 7: bool, 8: int|null, 9: int|null, 10: bool
 * }
 */
final class HybridParser implements ParserInterface
{
    /** @var non-empty-string */
    private $tagRegex;
    /** @var non-empty-string */
    private $paramRegex;
    /** @var non-empty-string */
    private $delimiter;
    /** @var positive-int */
    private $delimiterLength;

    /** @param SyntaxInterface|null $syntax */
    public function __construct($syntax = null)
    {
        if(null !== $syntax && false === $syntax instanceof SyntaxInterface) {
            throw new \LogicException('Parameter $syntax must be an instance of SyntaxInterface.');
        }

        $syntax = $syntax ?: new CommonSyntax();
        $this->delimiter = $syntax->getParameterValueDelimiter();
        $this->delimiterLength = strlen($this->delimiter);

        $openTag = preg_quote($syntax->getOpeningTag(), '~');
        $closeTag = preg_quote($syntax->getClosingTag(), '~');
        $marker = preg_quote($syntax->getClosingTagMarker(), '~');
        $separator = preg_quote($syntax->getParameterValueSeparator(), '~');
        $delimiter = preg_quote($this->delimiter, '~');

        $space = '\s*';
        $special = $openTag.'|'.$closeTag.'|'.$marker.'|'.$separator.'|'.$delimiter;
        $notSpecial = '(?!'.$special.')';
        // a single "string token": one escape sequence, or one maximal run of
        // non-special, non-whitespace characters (possessive so it never gives back)
        $stringToken = '(?:\\\\.|(?:'.$notSpecial.'[^\s\\\\])++)';
        // a value globs consecutive string tokens; atomic so the lexer commits like
        // RegularParser instead of backtracking into a different tokenization
        $stringRun = '(?>'.$stringToken.'+)';
        // a delimited value; the body is possessive so an escape sequence is never
        // given back to let the value re-close at an earlier (escaped) delimiter
        $quoted = $delimiter.'(?:\\\\.|(?!'.$delimiter.').)*+'.$delimiter;
        $value = '(?>'.$quoted.'|'.$stringRun.')';
        // shortcode name; must end on a token boundary so `[foo.bar]` is rejected wholesale
        $name = '[a-zA-Z0-9_*-]+';
        $boundary = '(?=\s|'.$special.'|$)';
        // a parameter name is a single string token, not a glued run
        $parameters = '(?<params>(?:'.$space.$stringToken.'(?:'.$space.$separator.$space.$value.')?)*+)';
        $bbCode = '(?:'.$separator.$space.'(?<bbCode>'.$value.')'.$space.')?+';

        $closingTagRule = $openTag.$space.$marker.$space.'(?<cname>'.$name.')'.$space.$closeTag;
        $openingTagRule = $openTag.$space.'(?<name>'.$name.')'.$boundary.$space.$bbCode.$parameters.$space.'(?<self>'.$marker.')?'.$space.$closeTag;

        $this->tagRegex = '~(?:'.$closingTagRule.'|'.$openingTagRule.')~us';
        $this->paramRegex = '~'.$space.'(?<pn>'.$stringToken.')(?:'.$space.$separator.$space.'(?<pv>'.$value.'))?~us';
    }

    /**
     * @param string $text
     *
     * @return ParsedShortcode[]
     */
    public function parse($text)
    {
        $count = preg_match_all($this->tagRegex, $text, $matches, PREG_OFFSET_CAPTURE);
        if(false === $count || preg_last_error() !== PREG_NO_ERROR) {
            throw new \RuntimeException(sprintf('PCRE failure `%s`.', preg_last_error()));
        }
        if(0 === $count) {
            return array();
        }

        // pure-ASCII text lets us treat byte offsets as character offsets directly
        $ascii = !preg_match('~[\x80-\xff]~', $text);
        $lastByte = 0;
        $lastChar = 0;

        /** @psalm-var list<HybridNode> $nodes */
        $nodes = array();
        /** @psalm-var list<int> $stack */
        $stack = array();
        $depth = 0;
        $closeNames = $matches['cname'];
        $names = $matches['name'];
        $selfMarkers = $matches['self'];
        $bbCodes = $matches['bbCode'];
        $paramStrings = $matches['params'];

        foreach($matches[0] as $index => $whole) {
            $byteStart = $whole[1];
            $byteEnd = $byteStart + strlen($whole[0]);

            if(isset($closeNames[$index][1]) && $closeNames[$index][1] !== -1) {
                // closing tag: match the innermost open node of the same name.
                // RegularParser rejects a closing name that is falsy in PHP (`'0'`)
                // via `if(!$closingName = ...)`, so we faithfully ignore it too.
                $closeName = $closeNames[$index][0];
                if('0' === $closeName) {
                    continue;
                }
                for($stackIndex = $depth - 1; $stackIndex >= 0; $stackIndex--) {
                    $nodeIndex = $stack[$stackIndex];
                    if($nodes[$nodeIndex][0] === $closeName) {
                        $nodes[$nodeIndex][7] = true;        // closed
                        $nodes[$nodeIndex][8] = $byteStart;  // closeStart
                        $nodes[$nodeIndex][9] = $byteEnd;     // closeEnd
                        $stack = array_slice($stack, 0, $stackIndex);
                        $depth = $stackIndex;
                        break;
                    }
                }
                continue;
            }

            // opening tag — char offset (byte offset is fine for pure-ASCII text)
            if($ascii) {
                $offset = $byteStart;
            } else {
                if($byteStart > $lastByte) {
                    /** @psalm-suppress PossiblyFalseArgument */
                    $lastChar += mb_strlen(substr($text, $lastByte, $byteStart - $lastByte), 'utf-8');
                    $lastByte = $byteStart;
                }
                $offset = $lastChar;
            }

            $selfClosing = isset($selfMarkers[$index][1]) && $selfMarkers[$index][1] !== -1;

            // node tuple: [0]name [1]paramsRaw [2]bbCodeRaw [3]offset [4]start
            //   [5]openEnd [6]parent [7]closed [8]closeStart [9]closeEnd [10]selfClosing
            // parameter/bbCode parsing is deferred to build() so absorbed nodes never pay for it
            $nodes[] = array(
                $names[$index][0],
                isset($paramStrings[$index][1]) && $paramStrings[$index][1] !== -1 ? $paramStrings[$index][0] : '',
                isset($bbCodes[$index][1]) && $bbCodes[$index][1] !== -1 ? $bbCodes[$index][0] : null,
                $offset,
                $byteStart,
                $byteEnd,
                $depth ? $stack[$depth - 1] : null,
                $selfClosing,
                $selfClosing ? $byteEnd : null,
                $selfClosing ? $byteEnd : null,
                $selfClosing,
            );

            if(false === $selfClosing) {
                $stack[$depth++] = count($nodes) - 1;
            }
        }

        return $this->build($nodes, $text);
    }

    /**
     * @psalm-param array<int, HybridNode> $nodes
     * @param string $text
     *
     * @return ParsedShortcode[]
     */
    private function build(array $nodes, $text)
    {
        $shortcodes = array();
        // A node is absorbed (part of a closed ancestor's content) iff its parent is
        // closed or itself absorbed. Parents always precede children, so a single
        // forward pass resolves this in O(1) per node instead of walking ancestors.
        /** @psalm-var array<int,bool> $absorbed */
        $absorbed = array();
        foreach($nodes as $index => $node) {
            $parent = $node[6];
            if(null !== $parent && ($nodes[$parent][7] || $absorbed[$parent])) {
                $absorbed[$index] = true;
                continue;
            }
            $absorbed[$index] = false;

            if($node[7]) {
                // a closed node always has integer close offsets (set on close or self-close)
                /** @psalm-suppress PossiblyNullOperand */
                $content = $node[10] ? null : substr($text, $node[5], $node[8] - $node[5]);
                /** @psalm-suppress PossiblyNullOperand */
                $shortcodeText = substr($text, $node[4], $node[9] - $node[4]);
            } else {
                $content = null;
                $shortcodeText = substr($text, $node[4], $node[5] - $node[4]);
            }

            $parameters = '' === $node[1] ? array() : $this->parseParameters($node[1]);
            $bbCode = null === $node[2] ? null : $this->extractValue($node[2]);

            /** @psalm-suppress PossiblyFalseArgument */
            $shortcode = new Shortcode($node[0], $parameters, $content, $bbCode);
            /** @psalm-suppress PossiblyFalseArgument */
            $shortcodes[] = new ParsedShortcode($shortcode, $shortcodeText, $node[3]);
        }

        return $shortcodes;
    }

    /**
     * @param string $text
     *
     * @psalm-return array<string,string|null>
     */
    private function parseParameters($text)
    {
        if('' === $text || false === preg_match_all($this->paramRegex, $text, $matches, PREG_SET_ORDER)) {
            return array();
        }

        $parameters = array();
        foreach($matches as $match) {
            if(!isset($match['pn']) || '' === $match['pn']) {
                continue;
            }
            $hasValue = isset($match['pv']) && '' !== $match['pv'];
            $parameters[$match['pn']] = $hasValue ? $this->extractValue($match['pv']) : null;
        }

        return $parameters;
    }

    /**
     * @param string $value
     *
     * @return string
     * @psalm-suppress InvalidFalsableReturnType
     */
    private function extractValue($value)
    {
        $length = $this->delimiterLength;
        if(strlen($value) >= 2 * $length
            && strncmp($value, $this->delimiter, $length) === 0
            && substr($value, -$length) === $this->delimiter) {
            /** @psalm-suppress FalsableReturnStatement */
            return substr($value, $length, -$length);
        }

        return $value;
    }
}
