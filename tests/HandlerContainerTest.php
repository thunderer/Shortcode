<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\HandlerContainer\ImmutableHandlerContainer;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class HandlerContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionOnDuplicateHandler()
    {
        $handlers = new HandlerContainer();
        $handlers->add('name', function () {});
        $this->setExpectedException('RuntimeException');
        $handlers->add('name', function () {});
    }

    public function testHandlerContainer()
    {
        $x = function () {};

        $handler = new HandlerContainer();
        $handler->add('x', $x);
        $handler->addAlias('y', 'x');

        $this->assertSame($x, $handler->get('x'));
    }

    public function testInvalidHandler()
    {
        $handlers = new HandlerContainer();
        $this->setExpectedException('RuntimeException');
        $handlers->add('invalid', new \stdClass());
    }

    public function testDefaultHandler()
    {
        $handlers = new HandlerContainer();
        $this->assertNull($handlers->get('missing'));

        $handlers->setDefault(function () {});
        $this->assertNotNull($handlers->get('missing'));
    }

    public function testExceptionIfAliasingNonExistentHandler()
    {
        $handlers = new HandlerContainer();
        $this->setExpectedException('RuntimeException');
        $handlers->addAlias('m', 'missing');
    }

    public function testImmutableHandlerContainer()
    {
        $handlers = new HandlerContainer();
        $handlers->add('code', function () {});
        $handlers->addAlias('c', 'code');
        $imHandlers = new ImmutableHandlerContainer($handlers);
        $handlers->add('not', function() {});

        $this->assertNull($imHandlers->get('missing'));
        $this->assertNotNull($imHandlers->get('code'));
        $this->assertNotNull($imHandlers->get('c'));
        $this->assertNull($imHandlers->get('not'));

        $defaultHandlers = new HandlerContainer();
        $defaultHandlers->setDefault(function () {});
        $imDefaultHandlers = new ImmutableHandlerContainer($defaultHandlers);
        $this->assertNotNull($imDefaultHandlers->get('missing'));
    }
}
