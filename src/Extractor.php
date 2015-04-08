<?php
namespace Thunder\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Extractor implements ExtractorInterface
    {
    /** @var Syntax */
    private $syntax;

    public function __construct(Syntax $syntax = null)
        {
        $this->syntax = $syntax ?: new Syntax();
        }

    /**
     * @param string $text
     * @return Match[]
     */
    public function extract($text)
        {
        preg_match_all($this->syntax->getShortcodeRegex(), $text, $matches, PREG_OFFSET_CAPTURE);

        return array_map(function(array $matches) {
            return new Match($matches[1], $matches[0]);
            }, $matches[0]);
        }
    }
