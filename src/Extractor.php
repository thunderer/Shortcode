<?php
namespace Thunder\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Extractor implements ExtractorInterface
    {
    const SHORTCODE_REGEX = '/(\[(\w+)(\s+.+?)?\](?:(.+?)\[\/(\2)\])?)/us';

    /**
     * @param string $text
     * @return Match[]
     */
    public function extract($text)
        {
        preg_match_all(self::SHORTCODE_REGEX, $text, $matches, PREG_OFFSET_CAPTURE);

        return array_map(function(array $matches) {
            return new Match($matches[1], $matches[0]);
            }, $matches[0]);
        }
    }
