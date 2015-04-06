<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\Serializer\TextSerializer;
use Thunder\Shortcode\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ShortcodeTest extends \PHPUnit_Framework_TestCase
    {
    /**
     * @param string $expected
     * @param string $name
     * @param array $args
     * @param string $content
     *
     * @dataProvider provideShortcodes
     */
    public function testShortcode($expected, $name, array $args, $content)
        {
        $s = new Shortcode($name, $args, $content);
        $textSerializer = new TextSerializer();

        $this->assertSame($name, $s->getName());
        $this->assertSame($args, $s->getParameters());
        $this->assertSame($content, $s->getContent());
        $this->assertSame($expected, $textSerializer->serialize($s));
        }

    public function provideShortcodes()
        {
        return array(
            array('[x arg=val]', 'x', array('arg' => 'val'), null),
            array('[x arg=val][/x]', 'x', array('arg' => 'val'), ''),
            array('[x arg=val]inner[/x]', 'x', array('arg' => 'val'), 'inner'),
            array('[x arg="val val"]inner[/x]', 'x', array('arg' => 'val val'), 'inner'),
            );
        }

    public function testObject()
        {
        $shortcode = new Shortcode('random', array(
            'arg' => 'value',
            'none' => null,
            ), 'something');

        $this->assertTrue($shortcode->hasParameter('arg'));
        $this->assertFalse($shortcode->hasParameter('invalid'));
        $this->assertSame(null, $shortcode->getParameter('none'));
        $this->assertSame('value', $shortcode->getParameter('arg'));
        $this->assertSame('', $shortcode->getParameter('invalid', ''));
        $this->assertSame(42, $shortcode->getParameter('invalid', 42));
        }

    public function testExceptionOnMissingParameterWithNoDefaultValue()
        {
        $shortcode = new Shortcode('name', array(), null);
        $this->setExpectedException('RuntimeException');
        $shortcode->getParameter('invalid');
        }
    }
