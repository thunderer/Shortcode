<?php
namespace Thunder\Shortcode\Tests;


use Thunder\Shortcode\Extractor\RegexExtractor;
use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Parser\RegexParser;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Shortcode\ProcessedShortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;
use Thunder\Shortcode\Tests\Fake\ReverseShortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ProcessorTest extends \PHPUnit_Framework_TestCase
    {
    private function getProcessor()
        {
        $handlers = new HandlerContainer();
        $handlers
            ->addHandler('name', function(ShortcodeInterface $s) { return $s->getName(); })
            ->addHandler('content', function(ShortcodeInterface $s) { return $s->getContent(); })
            ->addHandler('reverse', new ReverseShortcode())
            ->addAlias('c', 'content')
            ->addAlias('n', 'name');

        return new Processor(new RegexExtractor(), new RegexParser(), $handlers);
        }

    /**
     * @dataProvider provideTexts
     */
    public function testProcessor($text, $result)
        {
        $this->assertSame($result, $this->getProcessor()->process($text));
        }

    public function provideTexts()
        {
        return array(
            array('[name]', 'name'),
            array('[content]random[/content]', 'random'),
            array('[name]random[/other]', 'namerandom[/other]'),
            array('[name][other]random[/other]', 'name[other]random[/other]'),
            array('[content]random-[name]-random[/content]', 'random-name-random'),
            array('random [content]other[/content] various', 'random other various'),
            array('x [content]a-[name]-b[/content] y', 'x a-name-b y'),
            array('x [c]a-[n][/n]-b[/c] y', 'x a-n-b y'),
            array('x [content]a-[c]v[/c]-b[/content] y', 'x a-v-b y'),
            // array('x [html b]bold[/html] y [html code]code[/html] z', 'x <b>bold</b> y <code>code</code> z'),
            array('x [html]bold[/html] z', 'x [html]bold[/html] z'),
            array('x [reverse]abc xyz[/reverse] z', 'x zyx cba z'),
            );
        }

    public function testProcessorParentContext()
        {
        $handlers = new HandlerContainer();
        $handlers->addHandler('outer', function(ProcessedShortcode $s) {
            $name = $s->getParent() ? $s->getParent()->getName() : 'root';

            return $name.'['.$s->getContent().']';
            });
        $handlers->addAlias('inner', 'outer');
        $handlers->addAlias('level', 'outer');

        $processor = new Processor(new RegexExtractor(), new RegexParser(), $handlers);

        $text = 'x [outer]a [inner]c [level]x[/level] d[/inner] b[/outer] y';
        $result = 'x root[a outer[c inner[x] d] b] y';
        $this->assertSame($result, $processor->process($text));
        $this->assertSame($result.$result, $processor->process($text.$text));
        }

    public function testProcessorWithoutRecursion()
        {
        $processor = $this
            ->getProcessor()
            ->setRecursionDepth(0);

        $result = $processor->process('x [content]a-[name][/name]-b[/content] y');
        $this->assertSame('x a-[name][/name]-b y', $result);
        }

    public function testProcessorWithoutContentAutoProcessing()
        {
        $processor = $this
            ->getProcessor()
            ->setAutoProcessContent(false);

        $result = $processor->process('x [content]a-[name][/name]-b[/content] y');
        $this->assertSame('x a-[name][/name]-b y', $result);
        }

    public function testProcessorShortcodePositions()
        {
        $handlers = new HandlerContainer();
        $handlers->addHandler('p', function(ProcessedShortcode $s) { return $s->getPosition(); });
        $handlers->addHandler('n', function(ProcessedShortcode $s) { return $s->getNamePosition(); });
        $processor = new Processor(new RegexExtractor(), new RegexParser(), $handlers);

        $this->assertSame('123', $processor->process('[n][n][n]'), '3n');
        $this->assertSame('123', $processor->process('[p][p][p]'), '3p');
        $this->assertSame('113253', $processor->process('[p][n][p][n][p][n]'));
        $this->assertSame('1231567', $processor->process('[p][p][p][n][p][p][p]'));
        }

    public function testProcessorIterative()
        {
        $handlers = new HandlerContainer();
        $handlers
            ->addHandler('name', function(ShortcodeInterface $s) { return $s->getName(); })
            ->addHandler('content', function(ShortcodeInterface $s) { return $s->getContent(); })
            ->addAlias('c', 'content')
            ->addAlias('n', 'name')
            ->addAlias('d', 'c')
            ->addAlias('e', 'c');
        $processor = new Processor(new RegexExtractor(), new RegexParser(), $handlers);
        $processor->setRecursionDepth(0);

        $processor->setMaxIterations(2);
        $this->assertSame('x a y', $processor->process('x [c]a[/c] y'));
        $this->assertSame('x abc y', $processor->process('x [c]a[d]b[/d]c[/c] y'));
        $this->assertSame('x ab[e]c[/e]de y', $processor->process('x [c]a[d]b[e]c[/e]d[/d]e[/c] y'));

        $processor->setMaxIterations(null);
        $this->assertSame('x abcde y', $processor->process('x [c]a[d]b[e]c[/e]d[/d]e[/c] y'));
        }

    public function testExceptionOnInvalidRecursionDepth()
        {
        $processor = $this->getProcessor();
        $this->setExpectedException('InvalidArgumentException');
        $processor->setRecursionDepth(new \stdClass());
        }

    public function testExceptionOnInvalidMaxIterations()
        {
        $processor = $this->getProcessor();
        $this->setExpectedException('InvalidArgumentException');
        $processor->setMaxIterations(new \stdClass());
        }

    public function testDefaultHandler()
        {
        $handlers = new HandlerContainer();
        $handlers->setDefault(function(ShortcodeInterface $s) { return $s->getName(); });
        $processor = new Processor(new RegexExtractor(), new RegexParser(), $handlers);

        $this->assertSame('namerandom', $processor->process('[name][other][/name][random]'));
        }

    public function testPreventInfiniteLoop()
        {
        $handlers = new HandlerContainer();
        $handlers
            ->addHandler('name', function(ShortcodeInterface $s) { return $s->getName(); })
            ->addHandler('content', function(ShortcodeInterface $s) { return $s->getContent(); })
            ->addHandler('reverse', new ReverseShortcode())
            ->addAlias('c', 'content')
            ->addAlias('n', 'name')
            ->addHandler('self', function() { return '[self]'; })
            ->addHandler('other', function() { return '[self]'; })
            ->addHandler('random', function() { return '[various]'; });
        $processor = new Processor(new RegexExtractor(), new RegexParser(), $handlers);
        $processor->setMaxIterations(null);

        $processor->process('[self]');
        $processor->process('[other]');
        $processor->process('[random]');
        }
    }
