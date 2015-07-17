<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\Extractor\RegexExtractor;
use Thunder\Shortcode\Extractor\ExtractorMatch;
use Thunder\Shortcode\Syntax\Syntax;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ExtractorTest extends \PHPUnit_Framework_TestCase
    {
    /**
     * @param string $text
     * @param ExtractorMatch[] $matches
     *
     * @dataProvider provideTexts
     */
    public function testShortcode($text, array $matches)
        {
        $extractor = new RegexExtractor();
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
                new ExtractorMatch(6, '[ipsum]'),
                new ExtractorMatch(21, '[code-code arg=val]'),
                )),
            array('x [aa] x [aa] x', array(
                new ExtractorMatch(2, '[aa]'),
                new ExtractorMatch(9, '[aa]'),
                )),
            array('x [x]a[/x] x [x]a[/x] x', array(
                new ExtractorMatch(2, '[x]a[/x]'),
                new ExtractorMatch(13, '[x]a[/x]'),
                )),
            array('x [x x y=z a="b c"]a[/x] x [x x y=z a="b c"]a[/x] x', array(
                new ExtractorMatch(2, '[x x y=z a="b c"]a[/x]'),
                new ExtractorMatch(27, '[x x y=z a="b c"]a[/x]'),
                )),
            array('x [code /] y [code]z[/code] x [code] y [code/] a', array(
                new ExtractorMatch(2, '[code /]'),
                new ExtractorMatch(13, '[code]z[/code]'),
                new ExtractorMatch(30, '[code]'),
                new ExtractorMatch(39, '[code/]'),
                )),
            array('x [code arg=val /] y [code cmp="xx"/] x [code x=y/] a', array(
                new ExtractorMatch(2, '[code arg=val /]'),
                new ExtractorMatch(21, '[code cmp="xx"/]'),
                new ExtractorMatch(40, '[code x=y/]'),
                )),
            array('x [    code arg=val /]a[ code/]c[x    /    ] m [ y ] c [   /   y]', array(
                new ExtractorMatch(2, '[    code arg=val /]'),
                new ExtractorMatch(23, '[ code/]'),
                new ExtractorMatch(32, '[x    /    ]'),
                new ExtractorMatch(47, '[ y ] c [   /   y]'),
                )),
            );
        }

    public function testWithDifferentSyntax()
        {
        $extractor = new RegexExtractor(new Syntax('[[', ']]', '//', '==', '""'));

        $matches = $extractor->extract('so[[m]]ething [[code]] othe[[r]]various[[//r]]');
        $this->assertCount(3, $matches);
        $this->assertSame('[[r]]various[[//r]]', $matches[2]->getString());

        $matches = $extractor->extract('x [[code arg==""val oth""]]cont[[//code]] y');
        $this->assertCount(1, $matches);
        }
    }
