<?php
namespace Thunder\Shortcode\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Shortcode extends AbstractShortcode
    {
    public function withContent($content)
        {
        return new self($this->name, $this->parameters, $content);
        }
    }
