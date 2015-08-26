<?php
namespace Thunder\Shortcode\Handler;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class EmailHandler
{
    public function __invoke(ShortcodeInterface $shortcode)
    {
        $title = $shortcode->getParameter('title', '') ?: $shortcode->getContent();

        return '<a href="mailto:'.$shortcode->getContent().'">'.$title.'</a>';
    }
}
