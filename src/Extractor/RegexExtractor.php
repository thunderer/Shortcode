<?php
namespace Thunder\Shortcode\Extractor;

use Thunder\Shortcode\ExtractorInterface;
use Thunder\Shortcode\Match;
use Thunder\Shortcode\Syntax;
use Thunder\Shortcode\Utility\RegexBuilderUtility;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class RegexExtractor implements ExtractorInterface
    {
    private $regex;

    public function __construct(Syntax $syntax = null)
        {
        $this->regex = RegexBuilderUtility::buildShortcodeRegex($syntax ?: new Syntax());
        }

    /**
     * @param string $text
     * @return Match[]
     */
    public function extract($text)
        {
        preg_match_all($this->regex, $text, $matches, PREG_OFFSET_CAPTURE);

        return array_map(function(array $matches) {
            return new Match($matches[1], $matches[0]);
            }, $matches[0]);
        }
    }
