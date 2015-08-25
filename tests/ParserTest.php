<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\Parser\RegexParser;
use Thunder\Shortcode\Shortcode\ParsedShortcode;
use Thunder\Shortcode\Shortcode\ParsedShortcodeInterface;
use Thunder\Shortcode\Syntax\Syntax;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ParserTest extends \PHPUnit_Framework_TestCase
    {
    /**
     * @param string $code
     * @param ParsedShortcodeInterface[] $shortcodes
     *
     * @dataProvider provideShortcodes
     */
    public function testParser($code, array $shortcodes)
        {
        $parser = new RegexParser();
        $codes = $parser->parse($code);

        $count = count($shortcodes);
        $this->assertSame($count, count($codes));
        for($i = 0; $i < $count; $i++)
            {
            $this->assertSame($shortcodes[$i]->getName(), $codes[$i]->getName());
            $this->assertSame($shortcodes[$i]->getParameters(), $codes[$i]->getParameters());
            $this->assertSame($shortcodes[$i]->getContent(), $codes[$i]->getContent());
            $this->assertSame($shortcodes[$i]->getText(), $codes[$i]->getText());
            $this->assertSame($shortcodes[$i]->getPosition(), $codes[$i]->getPosition());
            }
        }

    public function provideShortcodes()
        {
        return array(
            array('[sc]', array(
                new ParsedShortcode('sc', array(), null, '[sc]', 0)),
                ),
            array('[sc]', array(
                new ParsedShortcode('sc', array(), null, '[sc]', 0)),
                ),
            array('[sc arg=val]', array(
                new ParsedShortcode('sc', array('arg' => 'val'), null, '[sc arg=val]', 0)),
                ),
            array('[sc novalue arg="complex value"]', array(
                new ParsedShortcode('sc', array('novalue' => null, 'arg' => 'complex value'), null, '[sc novalue arg="complex value"]', 0)),
                ),
            array('[sc x="ąćęłńóśżź ĄĆĘŁŃÓŚŻŹ"]', array(
                new ParsedShortcode('sc', array('x' => 'ąćęłńóśżź ĄĆĘŁŃÓŚŻŹ'), null, '[sc x="ąćęłńóśżź ĄĆĘŁŃÓŚŻŹ"]', 0)),
                ),
            array('[sc x="multi'."\n".'line"]', array(
                new ParsedShortcode('sc', array('x' => 'multi'."\n".'line'), null, '[sc x="multi'."\n".'line"]', 0)),
                ),
            array('[sc noval x="val" y]content[/sc]', array(
                new ParsedShortcode('sc', array('noval'=> null, 'x' => 'val', 'y' => null), 'content', '[sc noval x="val" y]content[/sc]', 0)),
                ),
            array('[sc x="{..}"]', array(
                new ParsedShortcode('sc', array('x' => '{..}'), null, '[sc x="{..}"]', 0)),
                ),
            array('[sc a="x y" b="x" c=""]', array(
                new ParsedShortcode('sc', array('a' => 'x y', 'b' => 'x', 'c' => ''), null, '[sc a="x y" b="x" c=""]', 0)),
                ),
            array('[sc a="a \"\" b"]', array(
                new ParsedShortcode('sc', array('a' => 'a \"\" b'), null, '[sc a="a \"\" b"]', 0)),
                ),
            array('[sc/]', array(
                new ParsedShortcode('sc', array(), null, '[sc/]', 0)),
                ),
            array('[sc    /]', array(
                new ParsedShortcode('sc', array(), null, '[sc    /]', 0)),
                ),
            array('[sc arg=val cmp="a b"/]', array(
                new ParsedShortcode('sc', array('arg' => 'val', 'cmp' => 'a b'), null, '[sc arg=val cmp="a b"/]', 0)),
                ),
            array('[sc x y   /]', array(
                new ParsedShortcode('sc', array('x' => null, 'y' => null), null, '[sc x y   /]', 0)),
                ),
            array('[sc x="\ "   /]', array(
                new ParsedShortcode('sc', array('x' => '\ '), null, '[sc x="\ "   /]', 0)),
                ),
            array('[   sc   x =  "\ "   y =   value  z   /    ]', array(
                new ParsedShortcode('sc', array('x' => '\ ', 'y' => 'value', 'z' => null), null, '[   sc   x =  "\ "   y =   value  z   /    ]', 0)),
                ),
            array('[ sc   x=  "\ "   y    =value   ] vv [ /  sc  ]', array(
                new ParsedShortcode('sc', array('x' => '\ ', 'y' => 'value'), ' vv ', '[ sc   x=  "\ "   y    =value   ] vv [ /  sc  ]', 0)),
                ),
            array('[sc url="http://giggle.com/search" /]', array(
                new ParsedShortcode('sc', array('url' => 'http://giggle.com/search'), null, '[sc url="http://giggle.com/search" /]', 0)),
                ),
            array('Lorem [ipsum] random [code-code arg=val] which is here', array(
                new ParsedShortcode('ipsum', array(), null, '[ipsum]', 6),
                new ParsedShortcode('code-code', array('arg' => 'val'), null, '[code-code arg=val]', 21),
                )),
            array('x [aa] x [aa] x', array(
                new ParsedShortcode('aa', array(), null, '[aa]', 2),
                new ParsedShortcode('aa', array(), null, '[aa]', 9),
                )),
            array('x [x]a[/x] x [x]a[/x] x', array(
                new ParsedShortcode('x', array(), 'a', '[x]a[/x]', 2),
                new ParsedShortcode('x', array(), 'a', '[x]a[/x]', 13),
                )),
            array('x [x x y=z a="b c"]a[/x] x [x x y=z a="b c"]a[/x] x', array(
                new ParsedShortcode('x', array('x' => null, 'y' => 'z', 'a' => 'b c'), 'a', '[x x y=z a="b c"]a[/x]', 2),
                new ParsedShortcode('x', array('x' => null, 'y' => 'z', 'a' => 'b c'), 'a', '[x x y=z a="b c"]a[/x]', 27),
                )),
            array('x [code /] y [code]z[/code] x [code] y [code/] a', array(
                new ParsedShortcode('code', array(), null, '[code /]', 2),
                new ParsedShortcode('code', array(), 'z', '[code]z[/code]', 13),
                new ParsedShortcode('code', array(), null, '[code]', 30),
                new ParsedShortcode('code', array(), null, '[code/]', 39),
                )),
            array('x [code arg=val /] y [code cmp="xx"/] x [code x=y/] a', array(
                new ParsedShortcode('code', array('arg' => 'val'), null, '[code arg=val /]', 2),
                new ParsedShortcode('code', array('cmp' => 'xx'), null, '[code cmp="xx"/]', 21),
                new ParsedShortcode('code', array('x' => 'y'), null, '[code x=y/]', 40),
                )),
            array('x [    code arg=val /]a[ code/]c[x    /    ] m [ y ] c [   /   y]', array(
                new ParsedShortcode('code', array('arg' => 'val'), null, '[    code arg=val /]', 2),
                new ParsedShortcode('code', array(), null, '[ code/]', 23),
                new ParsedShortcode('x', array(), null, '[x    /    ]', 32),
                new ParsedShortcode('y', array(), ' c ', '[ y ] c [   /   y]', 47),
                )),
            );
        }

    /**
     * @dataProvider provideInvalid
     */
    public function testParserInvalid($code)
        {
        $parser = new RegexParser();
        // $this->setExpectedException('RuntimeException');
        $this->assertEmpty($parser->parse($code));
        }

    public function provideInvalid()
        {
        return array(
            array(''),
            // array('[sc/][/sc]'),
            // array('[sc]x'),
            // array('[sc/]x'),
            array('[/y]'),
            // array('[sc x y   /]ddd[/sc]'),
            );
        }

    public function testWithDifferentSyntax()
        {
        $parser = new RegexParser(new Syntax('[[', ']]', '//', '==', '""'));

        $shortcode = $parser->parse('[[code arg==""val oth""]]cont[[//code]]');
        $this->assertSame('code', $shortcode[0]->getName());
        $this->assertCount(1, $shortcode[0]->getParameters());
        $this->assertSame('val oth', $shortcode[0]->getParameter('arg'));
        $this->assertSame('cont', $shortcode[0]->getContent());
        }

    public function testDifferentSyntaxEscapedQuotes()
        {
        $parser = new RegexParser(new Syntax('^', '$', '&', '!!!', '@@'));
        $shortcode = $parser->parse('^code a!!!@@\"\"@@ b!!!@@x\"y@@ c$cnt^&code$');

        $this->assertSame('code', $shortcode[0]->getName());
        $this->assertSame(array('a' => '\\"\\"', 'b' => 'x\"y', 'c' => null), $shortcode[0]->getParameters());
        $this->assertSame('cnt', $shortcode[0]->getContent());
        }
    }
