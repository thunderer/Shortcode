<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\Parser;
use Thunder\Shortcode\Syntax;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideShortcodes
     */
    public function testParser($code, $name, array $args, $content)
    {
        $parser = new Parser();
        $shortcode = $parser->parse($code);

        $this->assertSame($name, $shortcode->getName());
        $this->assertSame($args, $shortcode->getParameters());
        $this->assertSame($content, $shortcode->getContent());
    }

    public function provideShortcodes()
    {
        return array(
            array('[sc]', 'sc', array(), null),
            array('[sc]', 'sc', array(), null),
            array('[sc arg=val]', 'sc', array('arg' => 'val'), null),
            array('[sc novalue arg="complex value"]', 'sc', array('novalue' => null, 'arg' => 'complex value'), null),
            array('[sc x="ąćęłńóśżź ĄĆĘŁŃÓŚŻŹ"]', 'sc', array('x' => 'ąćęłńóśżź ĄĆĘŁŃÓŚŻŹ'), null),
            array('[sc x="multi'."\n".'line"]', 'sc', array('x' => 'multi'."\n".'line'), null),
            array('[sc noval x="val" y]content[/sc]', 'sc', array('noval'=> null, 'x' => 'val', 'y' => null), 'content'),
            array('[sc x="{..}"]', 'sc', array('x' => '{..}'), null),
            array('[sc a="x y" b="x" c=""]', 'sc', array('a' => 'x y', 'b' => 'x', 'c' => ''), null),
            array('[sc a="a \"\" b"]', 'sc', array('a' => 'a \"\" b'), null),
            );
    }

    public function testExceptionInvalidShortcode()
    {
        $parser = new Parser();
        $this->setExpectedException('RuntimeException');
        $parser->parse('');
    }

    public function testWithDifferentSyntax()
    {
        $parser = new Parser(new Syntax('[[', ']]', '//', '==', '""'));

        $shortcode = $parser->parse('[[code arg==""val oth""]]cont[[//code]]');
        $this->assertSame('code', $shortcode->getName());
        $this->assertCount(1, $shortcode->getParameters());
        $this->assertSame('val oth', $shortcode->getParameter('arg'));
        $this->assertSame('cont', $shortcode->getContent());
    }

    public function testDifferentSyntaxEscapedQuotes()
    {
        $parser = new Parser(new Syntax('^', '$', '&', '!!!', '@@'));
        $shortcode = $parser->parse('^code a!!!@@\"\"@@ b!!!@@x\"y@@ c$cnt^&code$');

        $this->assertSame('code', $shortcode->getName());
        $this->assertSame(array('a' => '\\"\\"', 'b' => 'x\"y', 'c' => null), $shortcode->getParameters());
        $this->assertSame('cnt', $shortcode->getContent());
    }
}
