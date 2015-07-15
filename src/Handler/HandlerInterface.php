<?php
namespace Thunder\Shortcode\Handler;

use Thunder\Shortcode\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 * @deprecated Use classes with __invoke()
 */
interface HandlerInterface
    {
    /**
     * Checks if shortcode contains enough and valid data to be processed
     *
     * @param Shortcode $shortcode
     *
     * @return bool
     */
    public function isValid(Shortcode $shortcode);

    /**
     * Target callable for handling shortcode
     *
     * @param Shortcode $shortcode
     *
     * @return string
     */
    public function handle(Shortcode $shortcode);
    }
