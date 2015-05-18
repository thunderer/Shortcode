<?php
namespace Thunder\Shortcode\Processor;

use Thunder\Shortcode\Match;
use Thunder\Shortcode\Shortcode\ContextAwareShortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;
use Thunder\Shortcode\Syntax;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 * @internal Utility for Processor to handle internal state
 */
final class ProcessorContext
    {
    private $parent;
    private $position;
    private $namePosition;
    private $recursionLevel;
    private $iterationNumber;
    private $text;

    public function __construct()
        {
        $this->reset();
        }

    public function reset()
        {
        $this->parent = null;
        $this->position = 0;
        $this->namePosition = array();
        $this->recursionLevel = 0;
        $this->iterationNumber = 0;
        }

    public function getShortcode(ShortcodeInterface $s, $text, Match $match)
        {
        $namePosition = $this->getNamePosition($s->getName());

        return new ContextAwareShortcode($s, $this->parent,
            $this->position, $namePosition,
            $text, $match->getPosition(), $match->getString(),
            $this->iterationNumber, $this->recursionLevel);
        }

    // GETTERS AND SETTERS

    public function setText($text)
        {
        $this->text = $text;
        }

    public function getText()
        {
        return $this->text;
        }

    public function incrementIterationNumber()
        {
        $this->iterationNumber++;
        }

    public function incrementRecursionLevel()
        {
        $this->recursionLevel++;
        }

    public function decrementRecursionLevel()
        {
        $this->recursionLevel--;
        }

    public function getRecursionLevel()
        {
        return $this->recursionLevel;
        }

    public function setParent(ShortcodeInterface $shortcode)
        {
        $this->parent = $shortcode;
        }

    public function clearParent()
        {
        $this->parent = null;
        }

    public function incrementPosition()
        {
        $this->position++;
        }

    public function incrementNamePosition($name)
        {
        $this->namePosition[$name] = array_key_exists($name, $this->namePosition)
            ? $this->namePosition[$name] + 1
            : 1;
        }

    public function getNamePosition($name)
        {
        return array_key_exists($name, $this->namePosition)
            ? $this->namePosition[$name]
            : 1;
        }
    }
