<?php
namespace Thunder\Shortcode\Tests;

use PHPUnit\Framework\TestCase;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
abstract class AbstractTestCase extends TestCase
{
    public function willThrowException($exception)
    {
        version_compare(phpversion(), '7.0.0') > 0
            ? $this->expectException($exception)
            : $this->setExpectedException($exception);
    }
}
