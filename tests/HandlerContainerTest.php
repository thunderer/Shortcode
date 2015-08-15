<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\HandlerContainer\HandlerContainer;

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

        $this->assertTrue($handler->has('x'));
        $this->assertTrue($handler->has('y'));
        $this->assertSame($x, $handler->get('x'));
        }

    public function testExceptionOnMissingHandler()
        {
        $handlers = new HandlerContainer();
        $this->setExpectedException('RuntimeException');
        $handlers->get('invalid');
        }

    public function testInvalidHandler()
        {
        $handlers = new HandlerContainer();
        $this->setExpectedException('RuntimeException');
        $handlers->add('invalid', new \stdClass());
        }
    }
