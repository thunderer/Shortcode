<?php
namespace Thunder\Shortcode\Extractor;

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
     * @return ExtractorMatch[]
     */
    public function extract($text);
    }
