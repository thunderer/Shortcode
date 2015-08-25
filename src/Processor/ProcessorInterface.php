<?php
namespace Thunder\Shortcode\Processor;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
interface ProcessorInterface
{
    /**
     * Process text using registered shortcode handlers
     *
     * @param string $text
     *
     * @return string
     */
    public function process($text);
}
