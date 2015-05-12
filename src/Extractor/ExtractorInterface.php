<?php
namespace Thunder\Shortcode\Extractor;

use Thunder\Shortcode\Match;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
interface ExtractorInterface
    {
    /**
     * Extract shortcode string matches with their offsets for further analysis
     *
     * @param string $text Text to extract from
     *
     * @return Match[]
     */
    public function extract($text);
    }
