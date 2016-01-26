<?php
namespace Thunder\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Events
{
    const FILTER_SHORTCODES = 'event.filter.shortcodes';
    const APPLY_RESULTS = 'event.apply.results';

    public static function getEvents()
    {
        return array(static::FILTER_SHORTCODES, static::APPLY_RESULTS);
    }
}
