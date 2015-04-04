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
    public function testObject($expected, $name, array $args, $content)
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
    }
