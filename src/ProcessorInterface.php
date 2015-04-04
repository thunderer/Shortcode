<?php
namespace Thunder\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
interface ProcessorInterface
    {
    /**
     * Register shortcode callback handler
     *
     * @param string $name
     * @param callable $handler
     *
     * @return self
     */
    public function addHandler($name, callable $handler);

    /**
     * Process text using registered shortcode handlers
     *
     * @param string $text
     *
     * @return string
     */
    public function process($text);
    }
