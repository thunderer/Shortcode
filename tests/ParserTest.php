<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Parser\RegularParser;
use Thunder\Shortcode\Parser\ParserInterface;
use Thunder\Shortcode\Parser\RegexParser;
use Thunder\Shortcode\Parser\WordpressParser;
use Thunder\Shortcode\Shortcode\ParsedShortcode;
use Thunder\Shortcode\Shortcode\Shortcode;
use Thunder\Shortcode\Syntax\CommonSyntax;
use Thunder\Shortcode\Syntax\Syntax;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ParserTest extends AbstractTestCase
{
    /**
     * @param ParserInterface $parser
     * @param string $code
     * @param ParsedShortcode[] $expected
     *
     * @dataProvider provideShortcodes
     */
    public function testParser(ParserInterface $parser, $code, array $expected)
    {
        $this->assertShortcodes($parser->parse($code), $expected);
    }

    private function assertShortcodes(array $actual, array $expected)
    {
        $count = count($actual);
        static::assertCount($count, $expected, 'counts');
        for ($i = 0; $i < $count; $i++) {
            static::assertSame($actual[$i]->getName(), $expected[$i]->getName(), 'name');
            static::assertSame($actual[$i]->getParameters(), $expected[$i]->getParameters(), 'parameters');
            static::assertSame($actual[$i]->getContent(), $expected[$i]->getContent(), 'content');
            static::assertSame($actual[$i]->getText(), $expected[$i]->getText(), 'text');
            static::assertSame($actual[$i]->getOffset(), $expected[$i]->getOffset(), 'offset');
            static::assertSame($actual[$i]->getBbCode(), $expected[$i]->getBbCode(), 'bbCode');
        }
    }

    public function provideShortcodes()
    {
        $s = new CommonSyntax();

        $tests = array(
            // invalid
            array($s, '', array()),
            array($s, '[]', array()),
            array($s, '![](image.jpg)', array()),
            array($s, 'x html([a. title][, alt][, classes]) x', array()),
            array($s, '[/y]', array()),
            array($s, '[sc', array()),
            array($s, '[sc / [/sc]', array()),
            array($s, '[sc arg="val', array()),

            // single shortcodes
            array($s, '[sc]', array(
                new ParsedShortcode(new Shortcode('sc', array(), null), '[sc]', 0),
            )),
            array($s, '[sc arg=val]', array(
                new ParsedShortcode(new Shortcode('sc', array('arg' => 'val'), null), '[sc arg=val]', 0),
            )),
            array($s, '[sc novalue arg="complex value"]', array(
                new ParsedShortcode(new Shortcode('sc', array('novalue' => null, 'arg' => 'complex value'), null), '[sc novalue arg="complex value"]', 0),
            )),
            array($s, '[sc x="ąćęłńóśżź ĄĆĘŁŃÓŚŻŹ"]', array(
                new ParsedShortcode(new Shortcode('sc', array('x' => 'ąćęłńóśżź ĄĆĘŁŃÓŚŻŹ'), null), '[sc x="ąćęłńóśżź ĄĆĘŁŃÓŚŻŹ"]', 0),
            )),
            array($s, '[sc x="multi'."\n".'line"]', array(
                new ParsedShortcode(new Shortcode('sc', array('x' => 'multi'."\n".'line'), null), '[sc x="multi'."\n".'line"]', 0),
            )),
            array($s, '[sc noval x="val" y]content[/sc]', array(
                new ParsedShortcode(new Shortcode('sc', array('noval' => null, 'x' => 'val', 'y' => null), 'content'), '[sc noval x="val" y]content[/sc]', 0),
            )),
            array($s, '[sc x="{..}"]', array(
                new ParsedShortcode(new Shortcode('sc', array('x' => '{..}'), null), '[sc x="{..}"]', 0),
            )),
            array($s, '[sc a="x y" b="x" c=""]', array(
                new ParsedShortcode(new Shortcode('sc', array('a' => 'x y', 'b' => 'x', 'c' => ''), null), '[sc a="x y" b="x" c=""]', 0),
            )),
            array($s, '[sc a="a \"\" b"]', array(
                new ParsedShortcode(new Shortcode('sc', array('a' => 'a \"\" b'), null), '[sc a="a \"\" b"]', 0),
            )),
            array($s, '[sc/]', array(
                new ParsedShortcode(new Shortcode('sc', array(), null), '[sc/]', 0),
            )),
            array($s, '[sc    /]', array(
                new ParsedShortcode(new Shortcode('sc', array(), null), '[sc    /]', 0),
            )),
            array($s, '[sc arg=val cmp="a b"/]', array(
                new ParsedShortcode(new Shortcode('sc', array('arg' => 'val', 'cmp' => 'a b'), null), '[sc arg=val cmp="a b"/]', 0),
            )),
            array($s, '[sc x y   /]', array(
                new ParsedShortcode(new Shortcode('sc', array('x' => null, 'y' => null), null), '[sc x y   /]', 0),
            )),
            array($s, '[sc  x="\ "   /]', array(
                new ParsedShortcode(new Shortcode('sc', array('x' => '\ '), null), '[sc  x="\ "   /]', 0),
            )),
            array($s, '[   sc   x =  "\ "   y =   value  z   /    ]', array(
                new ParsedShortcode(new Shortcode('sc', array('x' => '\ ', 'y' => 'value', 'z' => null), null), '[   sc   x =  "\ "   y =   value  z   /    ]', 0),
            )),
            array($s, '[ sc   x=  "\ "   y    =value   ] vv [ /  sc  ]', array(
                new ParsedShortcode(new Shortcode('sc', array('x' => '\ ', 'y' => 'value'), ' vv '), '[ sc   x=  "\ "   y    =value   ] vv [ /  sc  ]', 0),
            )),
            array($s, '[sc url="http://giggle.com/search" /]', array(
                new ParsedShortcode(new Shortcode('sc', array('url' => 'http://giggle.com/search'), null), '[sc url="http://giggle.com/search" /]', 0),
            )),

            // bbcode
            array($s, '[sc   =   "http://giggle.com/search" /]', array(
                new ParsedShortcode(new Shortcode('sc', array(), null, 'http://giggle.com/search'), '[sc   =   "http://giggle.com/search" /]', 0),
            )),

            // multiple shortcodes
            array($s, 'Lorem [ipsum] random [code-code arg=val] which is here', array(
                new ParsedShortcode(new Shortcode('ipsum', array(), null), '[ipsum]', 6),
                new ParsedShortcode(new Shortcode('code-code', array('arg' => 'val'), null), '[code-code arg=val]', 21),
            )),
            array($s, 'x [aa] x [aa] x', array(
                new ParsedShortcode(new Shortcode('aa', array(), null), '[aa]', 2),
                new ParsedShortcode(new Shortcode('aa', array(), null), '[aa]', 9),
            )),
            array($s, 'x [x]a[/x] x [x]a[/x] x', array(
                new ParsedShortcode(new Shortcode('x', array(), 'a'), '[x]a[/x]', 2),
                new ParsedShortcode(new Shortcode('x', array(), 'a'), '[x]a[/x]', 13),
            )),
            array($s, 'x [x x y=z a="b c"]a[/x] x [x x y=z a="b c"]a[/x] x', array(
                new ParsedShortcode(new Shortcode('x', array('x' => null, 'y' => 'z', 'a' => 'b c'), 'a'), '[x x y=z a="b c"]a[/x]', 2),
                new ParsedShortcode(new Shortcode('x', array('x' => null, 'y' => 'z', 'a' => 'b c'), 'a'), '[x x y=z a="b c"]a[/x]', 27),
            )),
            array($s, 'x [code /] y [code]z[/code] x [code] y [code/] a', array(
                new ParsedShortcode(new Shortcode('code', array(), null), '[code /]', 2),
                new ParsedShortcode(new Shortcode('code', array(), 'z'), '[code]z[/code]', 13),
                new ParsedShortcode(new Shortcode('code', array(), null), '[code]', 30),
                new ParsedShortcode(new Shortcode('code', array(), null), '[code/]', 39),
            )),
            array($s, 'x [code arg=val /] y [code cmp="xx"/] x [code x=y/] a', array(
                new ParsedShortcode(new Shortcode('code', array('arg' => 'val'), null), '[code arg=val /]', 2),
                new ParsedShortcode(new Shortcode('code', array('cmp' => 'xx'), null), '[code cmp="xx"/]', 21),
                new ParsedShortcode(new Shortcode('code', array('x' => 'y'), null), '[code x=y/]', 40),
            )),
            array($s, 'x [    code arg=val /]a[ code/]c[x    /    ] m [ y ] c [   /   y]', array(
                new ParsedShortcode(new Shortcode('code', array('arg' => 'val'), null), '[    code arg=val /]', 2),
                new ParsedShortcode(new Shortcode('code', array(), null), '[ code/]', 23),
                new ParsedShortcode(new Shortcode('x', array(), null), '[x    /    ]', 32),
                new ParsedShortcode(new Shortcode('y', array(), ' c '), '[ y ] c [   /   y]', 47),
            )),

            // other syntax
            array(new Syntax('[[', ']]', '//', '==', '""'), '[[code arg==""val oth""]]cont[[//code]]', array(
                new ParsedShortcode(new Shortcode('code', array('arg' => 'val oth'), 'cont'), '[[code arg==""val oth""]]cont[[//code]]', 0),
            )),
            array(new Syntax('^', '$', '&', '!!!', '@@'), '^code a!!!@@\"\"@@ b!!!@@x\"y@@ c$cnt^&code$', array(
                new ParsedShortcode(new Shortcode('code', array('a' => '\"\"', 'b' => 'x\"y', 'c' => null), 'cnt'), '^code a!!!@@\"\"@@ b!!!@@x\"y@@ c$cnt^&code$', 0),
            )),

            // UTF-8 sequences
            array($s, '’’’’[sc]’’[sc]', array(
                new ParsedShortcode(new Shortcode('sc', array(), null), '[sc]', 4),
                new ParsedShortcode(new Shortcode('sc', array(), null), '[sc]', 10),
            )),

            // performance
//            array($s, 'x [[aa]] y', array()),
            array($s, str_repeat('[a]', 20), array_map(function($offset) { // 20
                return new ParsedShortcode(new Shortcode('a', array(), null), '[a]', $offset);
            }, range(0, 57, 3))),
            array($s, '[b][a]x[a][/a][/a][/b] [b][a][a][/a]y[/a][/b]', array(
                new ParsedShortcode(new Shortcode('b', array(), '[a]x[a][/a][/a]'), '[b][a]x[a][/a][/a][/b]', 0),
                new ParsedShortcode(new Shortcode('b', array(), '[a][a][/a]y[/a]'), '[b][a][a][/a]y[/a][/b]', 23),
            )),
            array($s, '[b] [a][a][a] [/b] [b] [a][a][a] [/b]', array(
                new ParsedShortcode(new Shortcode('b', array(), ' [a][a][a] '), '[b] [a][a][a] [/b]', 0),
                new ParsedShortcode(new Shortcode('b', array(), ' [a][a][a] '), '[b] [a][a][a] [/b]', 19),
            )),
            array($s, '[name]random[/other]', array(
                new ParsedShortcode(new Shortcode('name', array(), null), '[name]', 0),
            )),
            array($s, '[0][1][2][3]', array(
                new ParsedShortcode(new Shortcode('0', array(), null), '[0]', 0),
                new ParsedShortcode(new Shortcode('1', array(), null), '[1]', 3),
                new ParsedShortcode(new Shortcode('2', array(), null), '[2]', 6),
                new ParsedShortcode(new Shortcode('3', array(), null), '[3]', 9),
            )),
            array($s, '[_][na_me][_name][name_][n_am_e][_n_]', array(
                new ParsedShortcode(new Shortcode('_', array(), null), '[_]', 0),
                new ParsedShortcode(new Shortcode('na_me', array(), null), '[na_me]', 3),
                new ParsedShortcode(new Shortcode('_name', array(), null), '[_name]', 10),
                new ParsedShortcode(new Shortcode('name_', array(), null), '[name_]', 17),
                new ParsedShortcode(new Shortcode('n_am_e', array(), null), '[n_am_e]', 24),
                new ParsedShortcode(new Shortcode('_n_', array(), null), '[_n_]', 32),
            )),
            array($s, '[x]/[/x] [x]"[/x] [x]=[/x] [x]][/x] [x] [/x] [x]x[/x]', array(
                new ParsedShortcode(new Shortcode('x', array(), '/'), '[x]/[/x]', 0),
                new ParsedShortcode(new Shortcode('x', array(), '"'), '[x]"[/x]', 9),
                new ParsedShortcode(new Shortcode('x', array(), '='), '[x]=[/x]', 18),
                new ParsedShortcode(new Shortcode('x', array(), ']'), '[x]][/x]', 27),
                new ParsedShortcode(new Shortcode('x', array(), ' '), '[x] [/x]', 36),
                new ParsedShortcode(new Shortcode('x', array(), 'x'), '[x]x[/x]', 45),
            )),
            array($s, '[a]0[/a]', array(
                new ParsedShortcode(new Shortcode('a', array(), '0'), '[a]0[/a]', 0),
            )),
            array($s, '[fa icon=fa-camera /] [fa icon=fa-camera extras=fa-4x /]', array(
                new ParsedShortcode(new Shortcode('fa', array('icon' => 'fa-camera'), null), '[fa icon=fa-camera /]', 0),
                new ParsedShortcode(new Shortcode('fa', array('icon' => 'fa-camera', 'extras' => 'fa-4x'), null), '[fa icon=fa-camera extras=fa-4x /]', 22),
            )),
            array($s, '[fa icon=fa-circle-o-notch extras=fa-spin,fa-3x /]', array(
                new ParsedShortcode(new Shortcode('fa', array('icon' => 'fa-circle-o-notch', 'extras' => 'fa-spin,fa-3x'), null), '[fa icon=fa-circle-o-notch extras=fa-spin,fa-3x /]', 0),
            )),
            array($s, '[z =]', array()),
            array($s, '[x=#F00 one=#F00 two="#F00"]', array(
                new ParsedShortcode(new Shortcode('x', array('one' => '#F00', 'two' => '#F00'), null, '#F00'), '[x=#F00 one=#F00 two="#F00"]', 0),
            )),
            array($s, '[*] [* xyz arg=val]', array(
                new ParsedShortcode(new Shortcode('*', array(), null, null), '[*]', 0),
                new ParsedShortcode(new Shortcode('*', array('xyz' => null, 'arg' => 'val'), null, null), '[* xyz arg=val]', 4),
            )),
            array($s, '[*=bb x=y]cnt[/*]', array(
                new ParsedShortcode(new Shortcode('*', array('x' => 'y'), 'cnt', 'bb'), '[*=bb x=y]cnt[/*]', 0),
            )),
            array($s, '[ [] ] [x] [ ] [/x] ] [] [ [y] ] [] [ [z] [/#] [/z] [ [] ] [/] [/y] ] [z] [ [/ [/] /] ] [/z]', array(
                new ParsedShortcode(new Shortcode('x', array(), ' [ ] ', null), '[x] [ ] [/x]', 7),
                new ParsedShortcode(new Shortcode('y', array(), ' ] [] [ [z] [/#] [/z] [ [] ] [/] ', null), '[y] ] [] [ [z] [/#] [/z] [ [] ] [/] [/y]', 27),
                new ParsedShortcode(new Shortcode('z', array(), ' [ [/ [/] /] ] ', null), '[z] [ [/ [/] /] ] [/z]', 70),
            )),
            // falsy string values
            array($s, '[a=0 b=0]0[/a]', array(
                new ParsedShortcode(new Shortcode('a', array('b' => '0'), '0', '0'), '[a=0 b=0]0[/a]', 0),
            )),
            array($s, '[x=/[/] [y a=/"//] [z=http://url/] [a=http://url ]', array(
                new ParsedShortcode(new Shortcode('x', array(), null, '/['), '[x=/[/]', 0),
                new ParsedShortcode(new Shortcode('y', array('a' => '/"/'), null, null), '[y a=/"//]', 8),
                new ParsedShortcode(new Shortcode('z', array(), null, 'http://url'), '[z=http://url/]', 19),
                new ParsedShortcode(new Shortcode('a', array(), null, 'http://url'), '[a=http://url ]', 35),
            )),
        );

        /**
         * WordPress can't handle:
         *   - incorrect shortcode opening tag (blindly matches everything
         *     between opening token and closing token)
         *   - spaces between shortcode open tag and its name ([  name]),
         *   - spaces around BBCode part ([name  = "bbcode"]),
         *   - escaped tokens anywhere in the arguments ([x arg=" \" "]),
         *   - configurable syntax (that's intended),
         *   - numbers in shortcode name.
         *
         * Tests cases from array above with identifiers in the array below must be skipped.
         */
        $wordpressSkip = array(3, 6, 16, 21, 22, 23, 25, 32, 33, 34, 46, 47, 49, 51, 52);
        $result = array();
        foreach($tests as $key => $test) {
            $syntax = array_shift($test);

            $result[] = array_merge(array(new RegexParser($syntax)), $test);
            $result[] = array_merge(array(new RegularParser($syntax)), $test);
            if(!in_array($key, $wordpressSkip, true)) {
                $result[] = array_merge(array(new WordpressParser()), $test);
            }
        }

        return $result;
    }

    public function testIssue77()
    {
        $parser = new RegularParser();

        $this->assertShortcodes($parser->parse('[a][x][/x][x k="v][/x][y]x[/y]'), array(
            new ParsedShortcode(new Shortcode('a', array(), null, null), '[a]', 0),
            new ParsedShortcode(new Shortcode('x', array(), '', null), '[x][/x]', 3),
            new ParsedShortcode(new Shortcode('y', array(), 'x', null), '[y]x[/y]', 22),
        ));
        $this->assertShortcodes($parser->parse('[a k="v][x][/x]'), array(
            new ParsedShortcode(new Shortcode('x', array(), '', null), '[x][/x]', 8),
        ));
    }

    public function testValueModeAggressive()
    {
        $parser = new RegularParser(new CommonSyntax());
        $parser->valueMode = RegularParser::VALUE_AGGRESSIVE;
        $parsed = $parser->parse('[x=/[/] [y a=/"//] [z=http://url/] [a=http://url ]');
        $tested = array(
            new ParsedShortcode(new Shortcode('x', array(), null, '/[/'), '[x=/[/]', 0),
            new ParsedShortcode(new Shortcode('y', array('a' => '/"//'), null, null), '[y a=/"//]', 8),
            new ParsedShortcode(new Shortcode('z', array(), null, 'http://url/'), '[z=http://url/]', 19),
            new ParsedShortcode(new Shortcode('a', array(), null, 'http://url'), '[a=http://url ]', 35),
        );

        $count = count($tested);
        static::assertCount($count, $parsed, 'counts');
        for ($i = 0; $i < $count; $i++) {
            static::assertSame($tested[$i]->getName(), $parsed[$i]->getName(), 'name');
            static::assertSame($tested[$i]->getParameters(), $parsed[$i]->getParameters(), 'parameters');
            static::assertSame($tested[$i]->getContent(), $parsed[$i]->getContent(), 'content');
            static::assertSame($tested[$i]->getText(), $parsed[$i]->getText(), 'text');
            static::assertSame($tested[$i]->getOffset(), $parsed[$i]->getOffset(), 'offset');
            static::assertSame($tested[$i]->getBbCode(), $parsed[$i]->getBbCode(), 'bbCode');
        }
    }

    public function testWordPress()
    {
        $parser = new WordpressParser();

        $this->testParser($parser, '[code arg="<html" oth=\'val\']', array(
            new ParsedShortcode(new Shortcode('code', array('arg' => '', 'oth' => 'val'), null), '[code arg="<html" oth=\'val\']', 0)
        ));
        $this->testParser($parser, '[code "xxx"]', array(
            new ParsedShortcode(new Shortcode('code', array('xxx' => null), null, null), '[code "xxx"]', 0)
        ));
        $this->testParser($parser, '[code="xxx"] [code=yyy-aaa]', array(
            new ParsedShortcode(new Shortcode('code', array('="xxx"' => null), null), '[code="xxx"]', 0),
            new ParsedShortcode(new Shortcode('code', array('=yyy-aaa' => null), null), '[code=yyy-aaa]', 13)
        ));

        $handlers = new HandlerContainer();
        $handlers->add('_', function() {});
        $handlers->add('na_me', function() {});
        $handlers->add('_n_', function() {});
        $this->testParser(WordpressParser::createFromHandlers($handlers), '[_][na_me][_name][name_][n_am_e][_n_]', array(
            new ParsedShortcode(new Shortcode('_', array(), null), '[_]', 0),
            new ParsedShortcode(new Shortcode('na_me', array(), null), '[na_me]', 3),
            new ParsedShortcode(new Shortcode('_n_', array(), null), '[_n_]', 32),
        ));
        $this->testParser(WordpressParser::createFromNames(array('_', 'na_me', '_n_')), '[_][na_me][_name][name_][n_am_e][_n_]', array(
            new ParsedShortcode(new Shortcode('_', array(), null), '[_]', 0),
            new ParsedShortcode(new Shortcode('na_me', array(), null), '[na_me]', 3),
            new ParsedShortcode(new Shortcode('_n_', array(), null), '[_n_]', 32),
        ));
    }

    public function testWordpressInvalidNamesException()
    {
        $this->willThrowException('InvalidArgumentException');
        WordpressParser::createFromNames(array('string', new \stdClass()));
    }

    public function testInstances()
    {
        static::assertInstanceOf('Thunder\Shortcode\Parser\WordPressParser', new WordpressParser());
        static::assertInstanceOf('Thunder\Shortcode\Parser\RegularParser', new RegularParser());
    }
}
