<?php
namespace Thunder\Shortcode\HandlerContainer;

interface HandlerContainerInterface
    {
    /**
     * Registers handler for given shortcode name.
     *
     * @param string $name
     * @param callable $handler
     *
     * @return self
     */
    public function addHandler($name, $handler);

    /**
     * Registers handler alias for given shortcode name, which means that
     * handler for target name will be called when alias is found.
     *
     * @param string $alias Alias shortcode name
     * @param string $name Aliased shortcode name
     *
     * @return self
     */
    public function addAlias($alias, $name);

    /**
     * Default library behavior is to ignore and return matches of shortcodes
     * without handler just like they were found. With this callable being set,
     * all matched shortcodes without registered handler will be passed to it.
     *
     * @param callable $handler Handler for shortcodes without registered name handler
     */
    public function setDefault($handler);

    /**
     * Returns handler for given shortcode name or default if it was set before.
     *
     * @param string $name Shortcode name
     *
     * @return callable
     */
    public function getHandler($name);

    /**
     * Whether handler for given shortcode name was registered. Default handler
     * does not count as one.
     *
     * @param string $name Shortcode name
     *
     * @return bool
     */
    public function hasHandler($name);
    }
