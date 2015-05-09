# Changelog

## Version 0.*

* v0.4.0 (dd.mm.2015)

  * `Thunder\Shortcode\Shortcode` class was deprecated, its content was moved to `Thunder\Shortcode\Shortcode\Shortcode`, please fix your `use`s. Old file was left with empty implementation to not break IDE autocomplete and call to `class_alias()` to not break BC, it will be removed in 1.0,
