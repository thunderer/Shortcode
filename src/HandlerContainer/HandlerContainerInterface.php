<?php
namespace Thunder\Shortcode\HandlerContainer;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
interface HandlerContainerInterface
{
    /**
     * Returns handler for given shortcode name or default if it was set before.
     * If no handler is found, returns null.
     *
     * @param string $name Shortcode name
     *
     * @return callable|null
     * @psalm-return (callable(ShortcodeInterface):string)|null
     */
    public function get($name);
}
