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

    public function getContent()
        {
        return $this->content;
        }
    }
