# Changelog

## Version 0.*

* v0.3.0 (08.05.2015)

  * added support for `[self-closing /]`  shortcodes,
  * added library facade for easier usage,
  * `Syntax` regular expressions are now built once in constructor,
  * added support for whitespace between shortcode fragments, ie. `[  code   arg = val ] [  / code ]`,
  * `Syntax` and `SyntaxBuilder` support whitespaced and strict syntaxes.

* v0.2.1 (23.04.2015)

  * fixed matching simple parameter values enclosed by delimiters,
  * fixed missing support for escaping characters inside parameter values.

* v0.2.0 (17.04.2015)

  * added HandlerInterface to enable shortcode handlers with basic validation capabilities,
  * added default handler for processing shortcodes without registered name handlers,
  * added handler aliasing to reuse name handlers without manually registering them,
  * added recursive processing with ability to control recursion depth,
  * added iterative processing with ability to control maximum number of iterations,
  * added configurable syntax to enable different shortcode formats without modifying library code,
  * added syntax builder to ease building `Syntax` object,
  * added dash `-` to allowed characters in shortcode names,
  * deprecated `Processor::setRecursion()`, use `Processor::setRecursionDepth()` instead,
  * removed regular expressions constants from classes.

* v0.1.0 (06.04.2015)

  * first library version.
