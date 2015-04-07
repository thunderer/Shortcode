<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\Extractor;
use Thunder\Shortcode\Match;
use Thunder\Shortcode\Syntax;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ExtractorTest extends \PHPUnit_Framework_TestCase
    {
    /**
     * @param string $text
     * @param Match[] $matches
     *
     * @dataProvider provideTexts
     */
    public function testShortcode($text, array $matches)
        {
        $extractor = new Extractor();
        $foundMatches = $extractor->extract($text);

        $matchesCount = count($matches);
        $this->assertSame($matchesCount, count($foundMatches));
        for($i = 0; $i < $matchesCount; $i++)
            {
            $this->assertSame($matches[$i]->getPosition(), $foundMatches[$i]->getPosition());
            $this->assertSame($matches[$i]->getString(), $foundMatches[$i]->getString());
            }
        }

    public function provideTexts()
        {
        return array(
            array('Lorem [ipsum] random [code-code arg=val] which is here', array(
                new Match(6, '[ipsum]'),
                new Match(21, '[code-code arg=val]'),
                )),
            array('x [aa] x [aa] x', array(
                new Match(2, '[aa]'),
                new Match(9, '[aa]'),
                )),
            array('x [x]a[/x] x [x]a[/x] x', array(
                new Match(2, '[x]a[/x]'),
                new Match(13, '[x]a[/x]'),
                )),
            array('x [x x y=z a="b c"]a[/x] x [x x y=z a="b c"]a[/x] x', array(
                new Match(2, '[x x y=z a="b c"]a[/x]'),
                new Match(27, '[x x y=z a="b c"]a[/x]'),
                )),
            );
        }

    public function testWithDifferentSyntax()
        {
        $extractor = new Extractor(new Syntax('[[', ']]', '//', '==', '""'));

        $matches = $extractor->extract('so[[m]]ething [[code]] othe[[r]]various[[//r]]');
        $this->assertCount(3, $matches);
        $this->assertSame('[[r]]various[[//r]]', $matches[2]->getString());

        $matches = $extractor->extract('x [[code arg==""val oth""]]cont[[//code]] y');
        $this->assertCount(1, $matches);
        }
    }
