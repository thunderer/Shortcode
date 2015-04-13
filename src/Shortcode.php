<?php
namespace Thunder\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Shortcode
    {
    private $name;
    private $parameters;
    private $content;

    public function __construct($name, array $arguments, $content)
        {
        $this->name = $name;
        $this->parameters = $arguments;
        $this->content = $content;
        }

    public function hasContent()
        {
        return $this->content !== null;
        }

    public function getName()
        {
        return $this->name;
        }

    public function getParameters()
        {
        return $this->parameters;
        }

    public function hasParameter($name)
        {
        return array_key_exists($name, $this->parameters);
        }

    public function hasParameters()
        {
        return (bool)$this->parameters;
        }

    /**
     * Returns parameter value using its name, throws exception when there was
     * no parameter with given name and no default value was set
     *
     * @param $name
     * @param null $default
     *
     * @throws \RuntimeException
     *
     * @return null
     */
    public function getParameter($name, $default = null)
        {
        if($this->hasParameter($name))
            {
            return $this->parameters[$name];
            }
        if(null !== $default)
            {
            return $default;
            }

        $msg = 'Shortcode parameter %s not found and no default value was set!';
        throw new \RuntimeException(sprintf($msg, $name));
        }

    /**
     * Returns name for position-based parameter, null if there was no parameter
     * at given position
     *
     * @param $index
     *
     * @return string|null
     */
    public function getParameterAt($index)
        {
        $keys = array_keys($this->parameters);

        return array_key_exists($index, $keys) ? $keys[$index] : null;
        }

    /**
     * Returns shortcode content (data between opening and closing tag). Returns
     * empty string if content was empty or null if shortcode has no closing tag
     *
     * @return string|null
     */
    public function getContent()
        {
        return $this->content;
        }
    }
