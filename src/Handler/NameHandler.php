<?php
namespace Thunder\Shortcode\Handler;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class NameHandler
{
    public function __invoke(ShortcodeInterface $shortcode)
    {
        return $shortcode->getName();
    }
}
