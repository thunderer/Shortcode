<?php
namespace Thunder\Shortcode\EventDispatcher;

use Thunder\Shortcode\EventContainer\EventContainerInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class EventDispatcher implements EventDispatcherInterface
{
    private $listeners;

    public function __construct(EventContainerInterface $events)
    {
        $this->listeners = $events;
    }

    public function dispatch($name, $event)
    {
        foreach($this->listeners->getListeners($name) as $listener) {
            $listener($event);
        }

        return $event;
    }
}
