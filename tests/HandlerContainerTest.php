<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class HandlerContainerTest extends \PHPUnit_Framework_TestCase
    {
    public function testExceptionOnDuplicateHandler()
        {
        $handlers = new HandlerContainer();
        $handlers->addHandler('name', function() {});
        $this->setExpectedException('RuntimeException');
        $handlers->addHandler('name', function() {});
        }

    public function testHandlerContainer()
        {
        $x = function() {};
        $y = function() {};

        $handler = new HandlerContainer();
        $handler->addHandler('x', $x);
        $handler->addAlias('y', 'x');
        $handler->setDefault($y);

        $this->assertTrue($handler->hasHandler('x'));
        $this->assertTrue($handler->hasHandler('y'));
        $this->assertSame($x, $handler->getHandler('x'));
        $this->assertSame($y, $handler->getHandler('z'));
        }

    public function testInvalidHandler()
        {
        $handlers = new HandlerContainer();
        $this->setExpectedException('RuntimeException');
        $handlers->addHandler('invalid', new \stdClass());
        }
    }
