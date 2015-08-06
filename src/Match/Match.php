<?php
namespace Thunder\Shortcode\Match;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Match implements MatchInterface
{
    /** @var int */
    private $position;

    /** @var string */
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
