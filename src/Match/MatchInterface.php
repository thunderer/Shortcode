<?php
namespace Thunder\Shortcode\Match;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
interface MatchInterface
    {
    /**
     * Returns position of matched string within text
     *
     * @return int
     */
    public function getPosition();

    /**
     * Returns exact string match
     *
     * @return string
     */
    public function getString();
    }
