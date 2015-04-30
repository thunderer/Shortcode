<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\Syntax;
use Thunder\Shortcode\SyntaxBuilder;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class SyntaxTest extends \PHPUnit_Framework_TestCase
    {
    public function testSyntax()
        {
        $syntax = new Syntax();

        $this->assertSame('[', $syntax->getOpeningTag());
        $this->assertSame(']', $syntax->getClosingTag());
        $this->assertSame('/', $syntax->getClosingTagMarker());
        $this->assertSame('=', $syntax->getParameterValueSeparator());
        $this->assertSame('"', $syntax->getParameterValueDelimiter());
        }

    public function testCustomSyntax()
        {
        $syntax = new Syntax('[[', ']]', '//', '==', '""');

        $this->assertSame('[[', $syntax->getOpeningTag());
        $this->assertSame(']]', $syntax->getClosingTag());
        $this->assertSame('//', $syntax->getClosingTagMarker());
        $this->assertSame('==', $syntax->getParameterValueSeparator());
        $this->assertSame('""', $syntax->getParameterValueDelimiter());
        }

    public function testBuilder()
        {
        $builder = new SyntaxBuilder();
        $syntax = $builder->getSyntax();

        $this->assertSame('[', $syntax->getOpeningTag());
        $this->assertSame(']', $syntax->getClosingTag());
        $this->assertSame('/', $syntax->getClosingTagMarker());
        $this->assertSame('=', $syntax->getParameterValueSeparator());
        $this->assertSame('"', $syntax->getParameterValueDelimiter());

        $builder = new SyntaxBuilder();
        $syntax = $builder
            ->setOpeningTag('[[')
            ->setClosingTag(']]')
            ->setClosingTagMarker('//')
            ->setParameterValueSeparator('==')
            ->setParameterValueDelimiter('""')
            ->getSyntax();

        $this->assertSame('[[', $syntax->getOpeningTag());
        $this->assertSame(']]', $syntax->getClosingTag());
        $this->assertSame('//', $syntax->getClosingTagMarker());
        $this->assertSame('==', $syntax->getParameterValueSeparator());
        $this->assertSame('""', $syntax->getParameterValueDelimiter());
        }

    /**
     * @dataProvider getDataForSpacesTrimmedCorrectly
     */
    public function testSpacesTrimmedCorrectly($incoming, $expected)
        {
            $propertiesToTest = array(

                'OpeningTag',
                'ClosingTag',
                'ClosingTagMarker',
                'ParameterValueSeparator',
                'ParameterValueDelimiter'
            );

            foreach ($propertiesToTest as $property) {

                $getter = "get$property";
                $setter = "set$property";

                $builder = new SyntaxBuilder();
                $syntax = $builder->$setter($incoming)->getSyntax();
                $this->assertEquals($expected, $syntax->$getter());
            }
        }

    public function getDataForSpacesTrimmedCorrectly()
        {
            return array(

                array('  x  ', 'x'),
                array('x  ', 'x'),
                array('  x', 'x'),
                array("\tx\t", 'x'),
                array("x\t", 'x'),
                array("\tx", 'x'),
            );
        }
    }
