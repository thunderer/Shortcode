<?php
namespace Thunder\Shortcode\Handler;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class UrlHandler
{
    public function __invoke(ShortcodeInterface $shortcode)
    {
        $title = $shortcode->getParameter('title', '') ?: $shortcode->getContent();

        return '<a href="'.$shortcode->getContent().'">'.$title.'</a>';
    }
}
