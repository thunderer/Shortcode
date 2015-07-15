<?php
namespace Thunder\Shortcode\Handler;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 * @deprecated Use classes with __invoke()
 */
interface HandlerInterface
    {
    /**
     * Checks if shortcode contains enough and valid data to be processed
     *
     * @param ShortcodeInterface $shortcode
     *
     * @return bool
     */
    public function isValid(ShortcodeInterface $shortcode);

    /**
     * Target callable for handling shortcode
     *
     * @param ShortcodeInterface $shortcode
     *
     * @return string
     */
    public function handle(ShortcodeInterface $shortcode);
    }
