<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\Extractor;
use Thunder\Shortcode\Parser;
use Thunder\Shortcode\Processor;
use Thunder\Shortcode\Shortcode;
use Thunder\Shortcode\Tests\Fake\HtmlShortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ProcessorTest extends \PHPUnit_Framework_TestCase
    {
    private function getProcessor()
        {
        $processor = new Processor(new Extractor(), new Parser());

        $processor
            ->addHandler('name', function(Shortcode $s) { return $s->getName(); })
            ->addHandler('content', function(Shortcode $s) { return $s->getContent(); })
            ->addHandler('html', new HtmlShortcode())
            ->addHandlerAlias('c', 'content')
            ->addHandlerAlias('n', 'name');

        return $processor;
        }

    /**
     * @dataProvider provideTexts
     */
    public function testProcessor($text, $result)
        {
        $processor = $this->getProcessor();

        $this->assertSame($result, $processor->process($text));
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
            array('x [html b]bold[/html] y [html code]code[/html] z', 'x <b>bold</b> y <code>code</code> z'),
            array('x [html]bold[/html] z', 'x [html]bold[/html] z'),
            );
        }

    public function testProcessorWithoutRecursion()
        {
        $processor = $this
            ->getProcessor()
            ->setRecursion(false);

        $result = $processor->process('x [content]a-[name][/name]-b[/content] y');
        $this->assertSame('x a-[name][/name]-b y', $result);
        }

    public function testExceptionOnInvalidHandler()
        {
        $processor = $this->getProcessor();
        $this->setExpectedException('RuntimeException');
        $processor->addHandler('invalid', new \stdClass());
        }

    public function testExceptionOnDuplicateHandler()
        {
        $processor = $this->getProcessor();
        $this->setExpectedException('RuntimeException');
        $processor->addHandler('name', function() {});
        }

    public function testDefaultHandler()
        {
        $processor = $this->getProcessor();
        $processor->setDefaultHandler(function(Shortcode $s) { return $s->getName(); });

        $this->assertSame('namerandom', $processor->process('[name][other][/name][random]'));
        }

    public function testExceptionOnInvalidRecursionDepth()
        {
        $processor = $this->getProcessor();
        $this->setExpectedException('InvalidArgumentException');
        $processor->setRecursionDepth(new \stdClass());
        }
    }
