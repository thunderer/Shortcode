<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\Extractor\ExtractorMatch;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class MatchTest extends \PHPUnit_Framework_TestCase
    {
    public function testMatch()
        {
        $match = new ExtractorMatch(4, 'match');

        $this->assertSame(4, $match->getPosition());
        $this->assertSame('match', $match->getString());
        }
    }
