<?php
namespace Thunder\Shortcode\Extractor;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ExtractorMatch
    {
    private $position;
    private $string;

    public function __construct($position, $string)
        {
        $this->position = $position;
        $this->string = $string;
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
