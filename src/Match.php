<?php
namespace Thunder\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Match
    {
    private $position;
    private $string;

    public function __construct($position, $string)
        {
        $this->position = $position;
        $this->string = $string;
        }

    public function getLength()
        {
        return mb_strlen($this->string);
        }

    public function getPosition()
        {
        return $this->position;
        }

    public function getString()
        {
        return $this->string;
        }
    }
