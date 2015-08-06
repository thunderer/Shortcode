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
    }
