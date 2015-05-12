<?php
namespace Thunder\Shortcode\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
class Shortcode implements ShortcodeInterface
    {
    private $name;
    private $parameters;
    private $content;

    public function __construct($name, array $parameters, $content)
        {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->content = $content;
        }

    public function withContent($content)
        {
        return new self($this->name, $this->parameters, $content);
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

    public function getParameterAt($index)
        {
        $keys = array_keys($this->parameters);

        return array_key_exists($index, $keys) ? $keys[$index] : null;
        }

    public function getContent()
        {
        return $this->content;
        }
    }
