<?php
namespace Thunder\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Events
{
    const FILTER_SHORTCODES = 'event.filter-shortcodes';
    const REPLACE_SHORTCODES = 'event.replace-shortcodes';
    const REWRITE_REPLACEMENTS = 'event.rewrite-replacements';

    /** @return string[] */
    public static function getEvents()
    {
        return array(static::FILTER_SHORTCODES, static::REPLACE_SHORTCODES, static::REWRITE_REPLACEMENTS);
    }
}
