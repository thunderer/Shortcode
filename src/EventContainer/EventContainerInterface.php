<?php
namespace Thunder\Shortcode\EventContainer;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
interface EventContainerInterface
{
    /**
     * @param string $event
     *
     * @return callable[]
     */
    public function getListeners($event);
}
