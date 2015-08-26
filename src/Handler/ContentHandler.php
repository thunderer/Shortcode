<?php
namespace Thunder\Shortcode\Handler;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ContentHandler
{
    public function __invoke(ShortcodeInterface $shortcode)
    {
        return $shortcode->getContent();
    }
}
