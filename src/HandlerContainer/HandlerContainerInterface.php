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
    public function add($name, $handler);

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
     * Returns handler for given shortcode name or default if it was set before.
     *
     * @param string $name Shortcode name
     *
     * @return callable
     */
    public function get($name);

    /**
     * Whether handler for given shortcode name was registered. Default handler
     * does not count as one.
     *
     * @param string $name Shortcode name
     *
     * @return bool
     */
    public function has($name);

    /**
     * Set default shortcode handler.
     *
     * @param callable $handler
     *
     * @return self
     */
    public function setDefault($handler);

    /**
     * Returns default shortcode handler.
     *
     * @return callable
     */
    public function getDefault();

    /**
     * Whether default shortcode handler was set.
     *
     * @return bool
     */
    public function hasDefault();
    }
