<?php
namespace Thunder\Shortcode\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Shortcode extends AbstractShortcode implements ShortcodeInterface
    {
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
    }
