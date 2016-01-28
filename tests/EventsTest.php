<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\Event\ReplaceShortcodesEvent;
use Thunder\Shortcode\EventContainer\EventContainer;
use Thunder\Shortcode\Event\FilterShortcodesEvent;
use Thunder\Shortcode\Events;
use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Parser\RegularParser;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Shortcode\ReplacedShortcode;
use Thunder\Shortcode\Shortcode\ProcessedShortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class EventsTest extends \PHPUnit_Framework_TestCase
{
    public function testFilterShortcodes()
    {
        $handlers = new HandlerContainer();
        $handlers->add('root', function(ShortcodeInterface $s) { return 'root['.$s->getContent().']'; });
        $handlers->add('yes', function(ShortcodeInterface $s) { return 'yes['.$s->getContent().']'; });
        $handlers->add('no', function(ShortcodeInterface $s) { return 'nope'; });

        $events = new EventContainer();
        $events->addListener(Events::FILTER_SHORTCODES, function(FilterShortcodesEvent $event) {
            $event->setShortcodes(array_filter($event->getShortcodes(), function(ShortcodeInterface $s) {
                return $s->getName() !== 'no';
            }));
        });

        $processor = new Processor(new RegularParser(), $handlers);
        $processor = $processor->withEventContainer($events);

        $this->assertSame('x root[ yes[ yes[] ] yes[ [no /] ] ] y', $processor->process('x [root] [yes] [yes/] [/yes] [yes] [no /] [/yes] [/root] y'));
    }

    public function testRaw()
    {
        $times = 0;
        $handlers = new HandlerContainer();
        $handlers->add('raw', function(ShortcodeInterface $s) { return $s->getContent(); });
        $handlers->add('n', function(ShortcodeInterface $s) use(&$times) { ++$times; return $s->getName(); });
        $handlers->add('c', function(ShortcodeInterface $s) use(&$times) { ++$times; return $s->getContent(); });

        $events = new EventContainer();
        $events->addListener(Events::FILTER_SHORTCODES, function(FilterShortcodesEvent $event) {
            $parent = $event->getParent();
            if($parent && ($parent->getName() === 'raw' || $parent->hasAncestor('raw'))) {
                $event->setShortcodes(array());
            }
        });

        $processor = new Processor(new RegularParser(), $handlers);
        $processor = $processor->withEventContainer($events);

        $this->assertSame(' [n] [c]cnt[/c] [/n] ', $processor->process('[raw] [n] [c]cnt[/c] [/n] [/raw]'));
        $this->assertSame('x un [n] [c]cnt[/c] [/n]  y', $processor->process('x [c]u[n][/c][raw] [n] [c]cnt[/c] [/n] [/raw] y'));
        $this->assertEquals(2, $times);
    }

    public function testStripContentOutsideShortcodes()
    {
        $handlers = new HandlerContainer();
        $handlers->add('name', function(ShortcodeInterface $s) { return $s->getName(); });
        $handlers->add('content', function(ShortcodeInterface $s) { return $s->getContent(); });
        $handlers->add('root', function(ProcessedShortcode $s) { return 'root['.$s->getContent().']'; });

        $events = new EventContainer();
        $events->addListener(Events::REPLACE_SHORTCODES, function(ReplaceShortcodesEvent $event) {
            if($event->getShortcode() && 'root' === $event->getShortcode()->getName()) {
                $replaces = array();
                foreach($event->getReplacements() as $r) {
                    $replaces[] = $r->getReplacement();
                }
                $event->setResult(implode('', $replaces));
            }
        });

        $processor = new Processor(new RegularParser(), $handlers);
        $processor = $processor->withEventContainer($events);

        $this->assertSame('a root[name name ] b', $processor->process('a [root]x [name] c[content] [name /] [/content] y[/root] b'));
    }

    public function testDefaultApplier()
    {
        $handlers = new HandlerContainer();
        $handlers->add('name', function(ShortcodeInterface $s) { return $s->getName(); });
        $handlers->add('content', function(ShortcodeInterface $s) { return $s->getContent(); });
        $handlers->add('root', function(ProcessedShortcode $s) { return 'root['.$s->getContent().']'; });

        $events = new EventContainer();
        $events->addListener(Events::REPLACE_SHORTCODES, function(ReplaceShortcodesEvent $event) {
            $event->setResult(array_reduce(array_reverse($event->getReplacements()), function($state, ReplacedShortcode $r) {
                $offset = $r->getOffset();
                $length = mb_strlen($r->getText());

                return mb_substr($state, 0, $offset).$r->getReplacement().mb_substr($state, $offset + $length);
            }, $event->getText()));
        });

        $processor = new Processor(new RegularParser(), $handlers);
        $processor = $processor->withEventContainer($events);

        $this->assertSame('a root[x name c name  y] b', $processor->process('a [root]x [name] c[content] [name /] [/content] y[/root] b'));
    }

    public function testExceptionOnHandlerForUnknownEvent()
    {
        $events = new EventContainer();
        $this->setExpectedException('InvalidArgumentException');
        $events->addListener('invalid', function() {});
    }
}
