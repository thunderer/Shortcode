<?php
namespace Thunder\Shortcode\Parser;

use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Shortcode\ParsedShortcode;
use Thunder\Shortcode\Shortcode\Shortcode;
use Thunder\Shortcode\Utility\RegexBuilderUtility;

/**
 * IMPORTANT NOTE: USE THIS PARSER **ONLY** IF YOU WANT THE FULL COMPATIBILITY
 * WITH WORDPRESS AND REPORT BUGS **ONLY** IF ITS BEHAVIOR IS DIFFERENT.
 *
 * This is a direct port of WordPress' shortcode parser with code copied from
 * its latest release (4.3.1 at the moment) adjusted to conform to this library.
 * Main regex was copied from get_shortcode_regex(), changed to handle all
 * shortcode names and folded into single string ($shortcodeRegex property),
 * method parseParameters() is a copy of function shortcode_parse_atts(). Code
 * was only structurally refactored for better readability. Read the comment
 * at the bottom of ParserTest::provideShortcodes() to understand the
 * limitations of this parser.
 *
 * @see https://core.trac.wordpress.org/browser/tags/4.3.1/src/wp-includes/shortcodes.php#L239
 * @see https://core.trac.wordpress.org/browser/tags/4.3.1/src/wp-includes/shortcodes.php#L448
 *
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class WordpressParser implements ParserInterface
{
    /** @var string */
    private static $shortcodeRegex = '/\\[(\\[?)(<NAMES>)(?![\\w-])([^\\]\\/]*(?:\\/(?!\\])[^\\]\\/]*)*?)(?:(\\/)\\]|\\](?:([^\\[]*+(?:\\[(?!\\/\\2\\])[^\\[]*+)*+)\\[\\/\\2\\])?)(\\]?)/s';
    /** @var string */
    private static $argumentsRegex = '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';

    /** @var string[] */
    private $names = array();

    public function __construct()
    {
    }

    /** @return self */
    public static function createFromHandlers(HandlerContainer $handlers)
    {
        return static::createFromNames($handlers->getNames());
    }

    /**
     * @param string[] $names
     *
     * @return self
     */
    public static function createFromNames(array $names)
    {
        foreach($names as $name) {
            /** @psalm-suppress DocblockTypeContradiction, RedundantConditionGivenDocblockType */
            if(false === is_string($name)) {
                throw new \InvalidArgumentException('Shortcode name must be a string!');
            }
        }

        $self = new self();
        $self->names = $names;

        return $self;
    }

    /**
     * @param string $text
     *
     * @return ParsedShortcode[]
     */
    public function parse($text)
    {
        $names = $this->names
            ? implode('|', array_map(function($name) { return preg_quote($name, '/'); }, $this->names))
            : RegexBuilderUtility::buildNameRegex();
        $regex = str_replace('<NAMES>', $names, static::$shortcodeRegex);
        preg_match_all($regex, $text, $matches, PREG_OFFSET_CAPTURE);

        $shortcodes = array();
        $count = count($matches[0]);
        for($i = 0; $i < $count; $i++) {
            $name = $matches[2][$i][0];
            $parameters = static::parseParameters($matches[3][$i][0]);
            $content = $matches[5][$i][1] !== -1 ? $matches[5][$i][0] : null;
            $match = $matches[0][$i][0];
            $offset = mb_strlen(substr($text, 0, $matches[0][$i][1]), 'utf-8');

            $shortcode = new Shortcode($name, $parameters, $content, null);
            $shortcodes[] = new ParsedShortcode($shortcode, $match, $offset);
        }

        return $shortcodes;
    }

    /**
     * @param string $text
     *
     * @psalm-return array<string,string|null>
     */
    private static function parseParameters($text)
    {
        $text = preg_replace('/[\x{00a0}\x{200b}]+/u', ' ', $text);

        if(!preg_match_all(static::$argumentsRegex, $text, $matches, PREG_SET_ORDER)) {
            return ltrim($text) ? array(ltrim($text) => null) : array();
        }

        $parameters = array();
        foreach($matches as $match) {
            if(!empty($match[1])) {
                $parameters[strtolower($match[1])] = stripcslashes($match[2]);
            } elseif(!empty($match[3])) {
                $parameters[strtolower($match[3])] = stripcslashes($match[4]);
            } elseif(!empty($match[5])) {
                $parameters[strtolower($match[5])] = stripcslashes($match[6]);
            } elseif(isset($match[7]) && $match[7] !== '') {
                $parameters[stripcslashes($match[7])] = null;
            } elseif(isset($match[8])) {
                $parameters[stripcslashes($match[8])] = null;
            }
        }

        foreach($parameters as $key => $value) {
            // NOTE: the `?: ''` fallback is the only change from the way WordPress parses shortcodes to satisfy Psalm's PossiblyNullArgument
            $value = $value ?: '';
            if(false !== strpos($value, '<') && 1 !== preg_match('/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value)) {
                $parameters[$key] = '';
            }
        }

        return $parameters;
    }
}
