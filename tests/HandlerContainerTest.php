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
        $handlers->add('name', function() {});
        $this->setExpectedException('RuntimeException');
        $handlers->add('name', function() {});
        }

    public function testHandlerContainer()
        {
        $x = function() {};

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

        $handlers->setDefault(function() {});
        $this->assertNotNull($handlers->get('missing'));
        }

    public function testImmutableHandlerContainer()
        {
        $handlers = new ImmutableHandlerContainer(array(
            'code' => function() {},
            ), array(
            'c' => 'code'
            ));

        $this->assertNull($handlers->get('missing'));
        $this->assertNotNull($handlers->get('code'));
        $this->assertNotNull($handlers->get('c'));

        $defaultHandlers = new ImmutableHandlerContainer(array(), array(), function() {});
        $this->assertNotNull($defaultHandlers->get('missing'));
        }
    }
