<?php
namespace Thunder\Shortcode\Handler;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class NullHandler
{
    public function __invoke(ShortcodeInterface $shortcode)
    {
        return null;
    }
}
