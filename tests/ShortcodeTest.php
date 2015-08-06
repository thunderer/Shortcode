<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\Extractor\RegexExtractor;
use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Parser\RegexParser;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Serializer\TextSerializer;
use Thunder\Shortcode\Shortcode\ProcessedShortcode;
use Thunder\Shortcode\Shortcode\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ShortcodeTest extends \PHPUnit_Framework_TestCase
    {
    /**
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
        $this->assertSame('arg', $s->getParameterAt(0));
        $this->assertTrue($s->hasParameters());
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

        $this->assertNotSame($shortcode, $shortcode->withContent('x'));
        }

    public function testExceptionOnMissingParameterWithNoDefaultValue()
        {
        $shortcode = new Shortcode('name', array(), null);
        $this->setExpectedException('RuntimeException');
        $shortcode->getParameter('invalid');
        }

    public function testProcessedShortcode()
        {
        $processor = new Processor(new RegexExtractor(), new RegexParser(), new HandlerContainer());
        $shortcode = new Shortcode('code', array('arg' => 'val'), 'content');
        $processed = new ProcessedShortcode($shortcode, null, 20, 10, ' [code] ', 1, '[code]', 1, 0, $processor);

        $this->assertSame('code', $processed->getName());
        $this->assertSame(array('arg' => 'val'), $processed->getParameters());
        $this->assertSame('content', $processed->getContent());

        $this->assertSame(20, $processed->getPosition());
        $this->assertSame(10, $processed->getNamePosition());
        $this->assertSame(' [code] ', $processed->getText());
        $this->assertSame(1, $processed->getTextPosition());
        $this->assertSame('[code]', $processed->getTextMatch());
        $this->assertSame(1, $processed->getIterationNumber());
        $this->assertSame(0, $processed->getRecursionLevel());
        $this->assertSame(null, $processed->getParent());
        $this->assertSame($processor, $processed->getProcessor());
        }
    }
