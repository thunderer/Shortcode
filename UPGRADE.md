# Changelog

## Version 0.*

* v0.4.0 (dd.mm.2015)

  * `Thunder\Shortcode\Shortcode` class was moved to `Thunder\Shortcode\Shortcode\Shortcode`, 
  * `Thunder\Shortcode\Extractor` class was moved to `Thunder\Shortcode\Parser\RegexExtractor`,
  * `Thunder\Shortcode\Parser` class was moved to `Thunder\Shortcode\Parser\RegexParser`,
  * please update your `use` clauses, old files were left with empty implementation to not break IDE autocomplete and call to `class_alias()` to not break BC, they will be removed in subsequent release.
