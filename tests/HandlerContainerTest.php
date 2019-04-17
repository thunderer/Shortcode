<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\HandlerContainer\ImmutableHandlerContainer;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class HandlerContainerTest extends AbstractTestCase
{
    public function testExceptionOnDuplicateHandler()
    {
        $handlers = new HandlerContainer();
        $handlers->add('name', function () {});
        $this->willThrowException('RuntimeException');
        $handlers->add('name', function () {});
    }

    public function testRemove()
    {
        $handlers = new HandlerContainer();
        static::assertFalse($handlers->has('code'));
        $handlers->add('code', function(ShortcodeInterface $s) {});
        static::assertTrue($handlers->has('code'));
        $handlers->remove('code');
        static::assertFalse($handlers->has('code'));
    }

    public function testRemoveException()
    {
        $handlers = new HandlerContainer();
        $this->willThrowException('RuntimeException');
        $handlers->remove('code');
    }

    public function testNames()
    {
        $handlers = new HandlerContainer();
        static::assertEmpty($handlers->getNames());
        $handlers->add('code', function(ShortcodeInterface $s) {});
        static::assertSame(array('code'), $handlers->getNames());
        $handlers->addAlias('c', 'code');
        static::assertSame(array('code', 'c'), $handlers->getNames());
    }

    public function testHandlerContainer()
    {
        $x = function () {};

        $handler = new HandlerContainer();
        $handler->add('x', $x);
        $handler->addAlias('y', 'x');

        static::assertSame($x, $handler->get('x'));
    }

    public function testInvalidHandler()
    {
        $handlers = new HandlerContainer();
        $this->willThrowException('RuntimeException');
        $handlers->add('invalid', new \stdClass());
    }

    public function testDefaultHandler()
    {
        $handlers = new HandlerContainer();
        static::assertNull($handlers->get('missing'));

        $handlers->setDefault(function () {});
        static::assertNotNull($handlers->get('missing'));
    }

    public function testExceptionIfAliasingNonExistentHandler()
    {
        $handlers = new HandlerContainer();
        $this->willThrowException('RuntimeException');
        $handlers->addAlias('m', 'missing');
    }

    public function testImmutableHandlerContainer()
    {
        $handlers = new HandlerContainer();
        $handlers->add('code', function () {});
        $handlers->addAlias('c', 'code');
        $imHandlers = new ImmutableHandlerContainer($handlers);
        $handlers->add('not', function() {});

        static::assertNull($imHandlers->get('missing'));
        static::assertNotNull($imHandlers->get('code'));
        static::assertNotNull($imHandlers->get('c'));
        static::assertNull($imHandlers->get('not'));

        $defaultHandlers = new HandlerContainer();
        $defaultHandlers->setDefault(function () {});
        $imDefaultHandlers = new ImmutableHandlerContainer($defaultHandlers);
        static::assertNotNull($imDefaultHandlers->get('missing'));
    }
}
