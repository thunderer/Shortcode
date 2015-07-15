<?php
namespace Thunder\Shortcode\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Shortcode extends AbstractShortcode
    {
    public function __construct($name, array $parameters, $content)
        {
        parent::__construct($name, $parameters, $content);
        }

    public function withContent($content)
        {
        return new self($this->name, $this->parameters, $content);
        }
    }
