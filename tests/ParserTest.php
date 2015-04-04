<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\Parser;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ParserTest extends \PHPUnit_Framework_TestCase
    {
    /**
     * @param string $code
     * @param string $name
     * @param array $args
     * @param string $content
     *
     * @dataProvider provideShortcodes
     */
    public function testShortcode($code, $name, array $args, $content)
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
            );
        }

    public function testExceptionInvalidShortcode()
        {
        $parser = new Parser();
        $this->setExpectedException('RuntimeException');
        $parser->parse('');
        }
    }
