<?php
namespace Thunder\Shortcode\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
interface ShortcodeInterface
    {
    /**
     * Returns new instance of given shortcode with changed content
     *
     * @param string $content
     *
     * @return self
     */
    public function withContent($content);

    /**
     * Whether current shortcode has content (was not self-closing)
     *
     * @return bool
     */
    public function hasContent();

    /**
     * Returns shortcode name
     *
     * @return string
     */
    public function getName();

    /**
     * Returns associative array(name => value) of shortcode parameters
     *
     * @return array
     */
    public function getParameters();

    /**
     * Whether current shortcode contained parameter with given name
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasParameter($name);

    /**
     * Whether current shortcode has any parameters
     *
     * @return bool
     */
    public function hasParameters();

    /**
     * Returns parameter value using its name, will return null for parameter
     * without value
     *
     * @param string $name
     * @param null $default
     *
     * @return mixed
     */
    public function getParameter($name, $default = null);

    /**
     * Returns name for position-based parameter, null if there was no parameter
     * at given position
     *
     * @param $index
     *
     * @return string|null
     */
    public function getParameterAt($index);

    /**
     * Returns shortcode content (data between opening and closing tag). Returns
     * empty string if content was empty or null if shortcode has no closing tag
     *
     * @return string|null
     */
    public function getContent();
    }
