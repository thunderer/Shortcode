<?php
namespace Thunder\Shortcode\EventDispatcher;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
interface EventDispatcherInterface
{
    public function dispatch($name, $event);
}
