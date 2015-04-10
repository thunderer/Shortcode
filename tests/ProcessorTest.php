<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\Extractor;
use Thunder\Shortcode\Parser;
use Thunder\Shortcode\Processor;
use Thunder\Shortcode\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ProcessorTest extends \PHPUnit_Framework_TestCase
    {
    /**
     * @dataProvider provideTexts
     */
    public function testProcessor($text, $result)
        {
        $processor = new Processor(new Extractor(), new Parser());

        $processor
            ->addHandler('name', function(Shortcode $s) { return $s->getName(); })
            ->addHandler('content', function(Shortcode $s) { return $s->getContent(); })
            ->addHandlerAlias('cnt', 'content')
            ->addHandlerAlias('n', 'name');

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
            array('x [cnt]a-[n][/n]-b[/cnt] y', 'x a-n-b y'),
            );
        }

    public function testExceptionOnDuplicateHandler()
        {
        $processor = new Processor(new Extractor(), new Parser());
        $processor->addHandler('name', function() {});
        $this->setExpectedException('RuntimeException');
        $processor->addHandler('name', function() {});
        }

    public function testDefaultHandler()
        {
        $processor = new Processor(new Extractor(), new Parser());
        $processor->setDefaultHandler(function(Shortcode $s) { return $s->getName(); });

        $this->assertSame('namerandom', $processor->process('[name][other][/name][random]'));
        }
    }
