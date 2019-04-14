<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Parser\RegexParser;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Processor\ProcessorContext;
use Thunder\Shortcode\Serializer\TextSerializer;
use Thunder\Shortcode\Shortcode\ParsedShortcode;
use Thunder\Shortcode\Shortcode\ProcessedShortcode;
use Thunder\Shortcode\Shortcode\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ShortcodeTest extends AbstractTestCase
{
    /**
     * @dataProvider provideShortcodes
     */
    public function testShortcode($expected, $name, array $args, $content)
    {
        $s = new Shortcode($name, $args, $content);
        $textSerializer = new TextSerializer();

        static::assertSame($name, $s->getName());
        static::assertSame($args, $s->getParameters());
        static::assertSame($content, $s->getContent());
        static::assertSame($expected, $textSerializer->serialize($s));
        static::assertSame('arg', $s->getParameterAt(0));
        static::assertTrue($s->hasParameters());
    }

    public function provideShortcodes()
    {
        return array(
            array('[x arg=val /]', 'x', array('arg' => 'val'), null),
            array('[x arg=val][/x]', 'x', array('arg' => 'val'), ''),
            array('[x arg=val]inner[/x]', 'x', array('arg' => 'val'), 'inner'),
            array('[x arg="val val"]inner[/x]', 'x', array('arg' => 'val val'), 'inner'),
        );
    }

    public function testObject()
    {
        $shortcode = new Shortcode('random', array('arg' => 'value', 'none' => null), 'something');

        static::assertTrue($shortcode->hasParameter('arg'));
        static::assertFalse($shortcode->hasParameter('invalid'));
        static::assertNull($shortcode->getParameter('none'));
        static::assertSame('value', $shortcode->getParameter('arg'));
        static::assertSame('', $shortcode->getParameter('invalid', ''));
        static::assertSame(42, $shortcode->getParameter('invalid', 42));

        static::assertNotSame($shortcode, $shortcode->withContent('x'));
    }

    public function testProcessedShortcode()
    {
        $processor = new Processor(new RegexParser(), new HandlerContainer());

        $context = new ProcessorContext();
        $context->shortcode = new Shortcode('code', array('arg' => 'val'), 'content');
        $context->processor = $processor;
        $context->position = 20;
        $context->namePosition = array('code' => 10);
        $context->text = ' [code] ';
        $context->shortcodeText = '[code]';
        $context->offset = 1;
        $context->iterationNumber = 1;
        $context->recursionLevel = 0;
        $context->parent = null;

        $processed = ProcessedShortcode::createFromContext($context);

        static::assertSame('code', $processed->getName());
        static::assertSame(array('arg' => 'val'), $processed->getParameters());
        static::assertSame('content', $processed->getContent());

        static::assertSame(20, $processed->getPosition());
        static::assertSame(10, $processed->getNamePosition());
        static::assertSame(' [code] ', $processed->getText());
        static::assertSame(1, $processed->getOffset());
        static::assertSame('[code]', $processed->getShortcodeText());
        static::assertSame(1, $processed->getIterationNumber());
        static::assertSame(0, $processed->getRecursionLevel());
        static::assertSame(null, $processed->getParent());
        static::assertSame($processor, $processed->getProcessor());
    }

    public function testProcessedShortcodeParents()
    {
        $context = new ProcessorContext();
        $context->shortcode = new Shortcode('p1', array(), null);
        $context->parent = null;
        $context->namePosition = array('p1' => 0, 'p2' => 0, 'p3' => 0);
        $p1 = ProcessedShortcode::createFromContext($context);
        $context->shortcode = new Shortcode('p2', array(), null);
        $context->parent = $p1;
        $p2 = ProcessedShortcode::createFromContext($context);
        $context->shortcode = new Shortcode('p3', array(), null);
        $context->parent = $p2;
        $p3 = ProcessedShortcode::createFromContext($context);

        static::assertSame('p3', $p3->getName());
        static::assertSame('p2', $p3->getParent()->getName());
        static::assertSame('p1', $p3->getParent()->getParent()->getName());
        static::assertFalse($p1->hasAncestor('p3'));
        static::assertFalse($p1->hasAncestor('p1'));
        static::assertTrue($p2->hasAncestor('p1'));
        static::assertFalse($p2->hasAncestor('p3'));
        static::assertTrue($p3->hasAncestor('p1'));
        static::assertTrue($p3->hasAncestor('p2'));
        static::assertFalse($p3->hasAncestor('p4'));
    }

    public function testParsedShortcode()
    {
        $shortcode = new ParsedShortcode(new Shortcode('name', array('arg' => 'val'), 'content'), 'text', 12);

        static::assertSame('name', $shortcode->getName());
        static::assertSame(array('arg' => 'val'), $shortcode->getParameters());
        static::assertSame('content', $shortcode->getContent());
        static::assertSame('text', $shortcode->getText());
        static::assertSame(12, $shortcode->getOffset());
        static::assertTrue($shortcode->hasContent());

        static::assertFalse($shortcode->withContent(null)->hasContent());
        static::assertSame('another', $shortcode->withContent('another')->getContent());
    }

    public function testShortcodeEmptyNameException()
    {
        $this->willThrowException('InvalidArgumentException');
        new Shortcode('', array(), null);
    }
}
