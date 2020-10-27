<?php
namespace Thunder\Shortcode\EventContainer;

use Thunder\Shortcode\Events;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class EventContainer implements EventContainerInterface
{
    /** @psalm-var array<string,list<callable>> */
    private $listeners = array();

    public function __construct()
    {
    }

    /**
     * @param string $event
     * @param callable $handler
     *
     * @return void
     */
    public function addListener($event, $handler)
    {
        if(!\in_array($event, Events::getEvents(), true)) {
            throw new \InvalidArgumentException(sprintf('Unsupported event %s!', $event));
        }

        if(!array_key_exists($event, $this->listeners)) {
            $this->listeners[$event] = array();
        }

        $this->listeners[$event][] = $handler;
    }

    /**
     * @param string $event
     *
     * @psalm-return list<callable>
     */
    public function getListeners($event)
    {
        return $this->hasEvent($event) ? $this->listeners[$event] : array();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    private function hasEvent($name)
    {
        return array_key_exists($name, $this->listeners);
    }
}
